<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DbitoPayService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $merchantId;
    protected string $walletCode;
    protected string $webhookSecret;
    protected float $payfastFeeRate;
    protected float $payfastFeeFixed;
    protected float $payfastVat;

    /**
     * Cada método usa a sua própria carteira (códigos públicos de 5 dígitos).
     */
    protected array $walletCodes;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.debitopay.base_url'), '/');
        $this->apiKey = config('services.debitopay.api_key');
        $this->merchantId = config('services.debitopay.merchant_id');
        $this->walletCode = config('services.debitopay.wallet_code');
        $this->webhookSecret = config('services.debitopay.webhook_secret');
        $this->payfastFeeRate = (float) config('services.debitopay.payfast_fee_rate', 0.07);
        $this->payfastFeeFixed = (float) config('services.debitopay.payfast_fee_fixed', 5.00);
        $this->payfastVat = (float) config('services.debitopay.payfast_vat', 0.15);
        $this->walletCodes = [
            'mpesa' => (string) config('services.debitopay.wallet_mpesa', ''),
            'emola' => (string) config('services.debitopay.wallet_emola', ''),
            'mkesh' => (string) config('services.debitopay.wallet_mkesh', ''),
            'visa_mastercard' => (string) config('services.debitopay.wallet_card', ''),
            'payfast' => (string) config('services.debitopay.wallet_payfast', ''),
        ];
    }

    protected function walletCodeFor(string $method): ?string
    {
        return ($this->walletCodes[$method] ?? '') !== '' ? $this->walletCodes[$method] : null;
    }

    /**
     * Taxa de câmbio MZN→ZAR em tempo real (open.er-api.com), devolvida como
     * "MZN por 1 ZAR" (coerente com o fallback 3.60), com margem de segurança
     * e cache. Se a API falhar, usa a taxa fixa de fallback.
     */
    public static function zarRate(): float
    {
        $fallback = (float) config('services.debitopay.zar_rate', 3.60);
        $margin = (float) config('services.debitopay.zar_margin', 0.03);
        $ttl = (int) config('services.debitopay.zar_rate_ttl', 60);

        $live = cache()->remember('debitopay.live_zar_rate', now()->addMinutes($ttl), function () {
            try {
                $response = Http::timeout(5)->get('https://open.er-api.com/v6/latest/MZN');
                $zarPerMzn = (float) (($response->json('rates') ?? [])['ZAR'] ?? 0);

                return $zarPerMzn > 0 ? (1 / $zarPerMzn) : false;
            } catch (\Throwable $e) {
                Log::warning('Débito Pay FX rate fetch failed', ['error' => $e->getMessage()]);

                return false;
            }
        });

        $rate = $live ?: $fallback;

        return max(round(($rate / (1 + $margin)), 4), 0.01);
    }

    /**
     * Métodos suportados pela API Débito Pay.
     * "card" local é mapeado para "visa_mastercard"; "payfast" cobra em ZAR.
     */
    public const METHODS = ['mpesa', 'emola', 'mkesh', 'visa_mastercard', 'payfast'];

    /**
     * Montantes mínimos por transação.
     * Nota: a documentação indicava 5 ZAR para PayFast, mas o gateway
     * em produção rejeita abaixo de 10 ZAR ("Valor mínimo PayFast: ZAR 10.00").
     */
    public const MIN_AMOUNTS = [
        'mpesa' => ['MZN' => 10],
        'emola' => ['MZN' => 50],
        'mkesh' => ['MZN' => 10],
        'visa_mastercard' => ['MZN' => 50],
        'payfast' => ['ZAR' => 10],
    ];

    /**
     * Gross-up PayFast: calcula o valor bruto a cobrar ao cliente para que
     * o líquido (após 7% + R5 + VAT 15% sobre as taxas) seja igual ao pedido.
     */
    public static function payfastBreakdown(float $netZar): array
    {
        $feeRate = (float) config('services.debitopay.payfast_fee_rate', 0.07);
        $fixed = (float) config('services.debitopay.payfast_fee_fixed', 5.00);
        $vat = (float) config('services.debitopay.payfast_vat', 0.15);

        $denominator = 1 - ($feeRate * (1 + $vat));
        $grossCents = ceil((($netZar * 100) + ($fixed * (1 + $vat) * 100)) / $denominator);
        $gross = $grossCents / 100;

        $variableFee = round($gross * $feeRate, 2);
        $totalFeeExVat = round($variableFee + $fixed, 2);
        $vatAmount = round($totalFeeExVat * $vat, 2);
        $totalFees = round($totalFeeExVat + $vatAmount, 2);

        return [
            'net' => round($netZar, 2),
            'gross' => $gross,
            'variable_fee' => $variableFee,
            'fixed_fee' => $fixed,
            'vat' => $vatAmount,
            'total_fees' => $totalFees,
        ];
    }

    public function createPayment(Order $order, string $paymentMethod): array
    {
        $method = $paymentMethod === 'card' ? 'visa_mastercard' : $paymentMethod;

        if (!in_array($method, self::METHODS, true)) {
            return ['success' => false, 'message' => 'Método de pagamento não suportado.'];
        }

        if ($method === 'payfast') {
            $currency = 'ZAR';
            $netZar = round($order->total / max(self::zarRate(), 0.01), 2);
            $amount = self::payfastBreakdown($netZar)['gross'];
        } else {
            $currency = 'MZN';
            $amount = (float) $order->total;
        }

        $minimum = self::MIN_AMOUNTS[$method][$currency] ?? 0;

        if ($amount < $minimum) {
            return [
                'success' => false,
                'message' => $currency === 'ZAR'
                    ? sprintf('O valor mínimo para PayFast é R %s.', number_format($minimum, 2, ',', '.'))
                    : sprintf('O valor mínimo para este método é %s MT.', number_format($minimum, 2, ',', '.')),
            ];
        }

        $phone = $this->normalizePhone($order->customer_phone);

        $walletCode = $this->walletCodeFor($method);

        if (!$walletCode) {
            return [
                'success' => false,
                'message' => 'Este método de pagamento não está disponível de momento. Escolha outra opção.',
            ];
        }

        // O PayFast bloqueia return_url com localhost (WAF CloudFront).
        // Usamos sempre o APP_URL publico (tunel) para o retorno do pagamento.
        $returnUrl = rtrim(config('app.url'), '/').'/pagamento/'.$order->order_number.'/status';

        $payload = [
            'action' => 'process',
            'payment_method' => $method,
            'merchant_id' => $this->merchantId,
            'wallet_code' => $walletCode,
            'amount' => $amount,
            'currency' => $currency,
            'source' => 'gateway',
            'source_id' => $order->order_number,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $phone,
            'return_url' => $returnUrl,
        ];

        // Mobile money exige o campo "phone" dedicado (documentação Débito Pay).
        if (in_array($method, ['mpesa', 'emola', 'mkesh'], true)) {
            $payload['phone'] = $phone;
        }

        try {
            $response = Http::withHeaders($this->buildHeaders())
                ->timeout(90)
                ->post("{$this->baseUrl}/payment-orchestrator", $payload);

            $data = $response->json() ?? [];

            if (!$response->successful()) {
                Log::error('Débito Pay API error', [
                    'status' => $response->status(),
                    'response' => $data,
                    'order' => $order->order_number,
                ]);

                return [
                    'success' => false,
                    'message' => $data['message'] ?? $data['error'] ?? 'Erro ao processar pagamento. Tente novamente.',
                ];
            }

            $gatewayStatus = $data['status'] ?? 'pending';
            $isPaid = in_array($gatewayStatus, ['success', 'completed', 'paid'], true);

            $payment = Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $data['payment_id'] ?? null,
                'payment_method' => $paymentMethod,
                'amount' => $order->total,
                'currency' => $currency,
                'status' => $isPaid ? 'completed' : 'pending',
                'gateway_response' => $data,
                'paid_at' => $isPaid ? now() : null,
            ]);

            if ($isPaid) {
                $order->update(['status' => 'paid']);
            }

            return [
                'success' => true,
                'redirect_url' => $data['checkout_url'] ?? null,
                'transaction_id' => $data['payment_id'] ?? null,
                'message' => $isPaid
                    ? 'Pagamento confirmado com sucesso.'
: ($currency === 'ZAR'
                    ? sprintf('A redirecionar para o PayFast (R %s, taxas incluídas).', number_format($amount, 2))
                    : 'Pagamento iniciado.'),
                'payment_id' => $data['payment_id'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('Débito Pay connection error', [
                'error' => $e->getMessage(),
                'order' => $order->order_number,
            ]);

            return [
                'success' => false,
                'message' => 'Erro de conexão. Tente novamente mais tarde.',
            ];
        }
    }

    public function verifyPayment(string $orderNumber): array
    {
        $order = Order::where('order_number', $orderNumber)->first();
        $payment = $order?->payment()->latest()->first();

        if (!$order || !$payment || !$payment->transaction_id) {
            return ['status' => 'unknown'];
        }

        try {
            $response = Http::withHeaders($this->buildHeaders())
                ->timeout(90)
                ->post("{$this->baseUrl}/payment-orchestrator", [
                    'action' => 'check-status',
                    'payment_id' => $payment->transaction_id,
                ]);

            if (!$response->successful()) {
                return ['status' => 'unknown'];
            }

            $remote = $response->json('payment') ?? [];

            if (!empty($remote['status'])) {
                $this->applyStatus($payment, $order, $remote['status'], $remote);
            }

            return ['status' => $remote['status'] ?? 'unknown'];
        } catch (\Throwable $e) {
            Log::error('Débito Pay verification error', ['error' => $e->getMessage()]);

            return ['status' => 'error'];
        }
    }

    public function handleWebhook(string $rawBody, ?string $signature): bool
    {
        if (!$this->verifySignature($rawBody, $signature)) {
            Log::warning('Invalid Débito Pay webhook signature', [
                'signature' => $signature,
                'headers' => Request::capture()->headers->all(),
            ]);

            return false;
        }

        $payload = json_decode($rawBody, true);

        if (!is_array($payload)) {
            return false;
        }

        $event = $payload['event'] ?? $payload['type'] ?? null;
        $data = $payload['data'] ?? $payload;

        // Payload oficial: data.payment_id identifica o pagamento; source_id é fallback.
        $order = null;
        $payment = null;

        if (!empty($data['payment_id'])) {
            $payment = Payment::where('transaction_id', $data['payment_id'])->first();
            $order = $payment?->order;
        }

        $orderNumber = $data['source_id']
            ?? $data['order_id']
            ?? $payload['source_id']
            ?? $payload['order_id']
            ?? null;

        if (!$order && $orderNumber) {
            $order = Order::where('order_number', $orderNumber)->first();
        }

        if (!$order) {
            Log::warning('Order not found for webhook', [
                'payment_id' => $data['payment_id'] ?? null,
                'source_id' => $orderNumber,
            ]);

            return false;
        }

        $payment ??= $order->payment()->latest()->first();

        if (!$payment) {
            return false;
        }

        // Eventos documentados: payment.completed / failed / expired / refunded / chargeback
        $status = match ($event) {
            'payment.completed', 'payment.paid' => 'success',
            'payment.failed', 'payment.cancelled' => str_replace('payment.', '', $event),
            'payment.expired' => 'expired',
            'payment.refunded' => 'refunded',
            'payment.chargeback' => 'failed',
            default => $data['status'] ?? $payload['status'] ?? null,
        };

        if (!$status) {
            return false;
        }

        $this->applyStatus($payment, $order, $status, $payload);

        return true;
    }

    protected function verifySignature(string $rawBody, ?string $signature): bool
    {
        if (!$this->webhookSecret || !$signature) {
            return false;
        }

        $request = Request::capture();
        $secret = $this->webhookSecret;

        $messages = [$rawBody];

        $id = $request->header('svix-id') ?: $request->header('webhook-id') ?: $request->header('X-Webhook-Id');
        $ts = $request->header('svix-timestamp') ?: $request->header('webhook-timestamp') ?: $request->header('X-Webhook-Timestamp');

        if ($id && $ts) {
            $messages[] = "{$id}.{$ts}.{$rawBody}";
        }

        if ($ts) {
            $messages[] = "{$ts}.{$rawBody}";
        }

        $candidates = [$signature];

        if (str_contains($signature, ',')) {
            $candidates = array_map('trim', explode(',', $signature));
        }

        foreach ($candidates as $candidate) {
            $candidate = preg_replace('/^v[0-9]+,?/i', '', trim($candidate));

            foreach ($messages as $message) {
                $hex = hash_hmac('sha256', $message, $secret);

                if (hash_equals(strtolower($hex), strtolower($candidate))) {
                    return true;
                }

                $base64 = base64_encode(hash_hmac('sha256', $message, $secret, true));

                if (hash_equals($base64, $candidate)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Cabeçalhos padrão + contexto do cliente final (anti-fraude/KYC)
     * e chave de idempotência para evitar duplicados em retries.
     */
    protected function buildHeaders(): array
    {
        $headers = [
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Idempotency-Key' => (string) Str::uuid(),
        ];

        $request = Request::capture();

        if ($ip = $request->ip()) {
            $headers['X-Customer-IP'] = $ip;
        }

        if ($userAgent = $request->userAgent()) {
            $headers['X-Customer-User-Agent'] = $userAgent;
        }

        if ($origin = $request->headers->get('origin') ?: $request->headers->get('referer')) {
            $headers['X-Customer-Origin'] = $origin;
        }

        return $headers;
    }

    /**
     * Consulta o saldo das carteiras do merchant (endpoint wallet-balance).
     */
    public function getBalance(?string $walletCode = null): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(90)
                ->get("{$this->baseUrl}/wallet-balance", $walletCode ? ['wallet_code' => $walletCode] : []);

            return $response->json() ?? ['success' => false];
        } catch (\Throwable $e) {
            Log::error('Débito Pay balance error', ['error' => $e->getMessage()]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function applyStatus(Payment $payment, Order $order, string $remoteStatus, array $response): void
    {
        $paymentStatus = match ($remoteStatus) {
            'success', 'completed', 'paid' => 'completed',
            'failed' => 'failed',
            'cancelled' => 'failed',
            'expired' => 'failed',
            'refunded' => 'refunded',
            'processing' => 'processing',
            default => 'pending',
        };

        // Preserva a data original de pagamento (ex.: refund chega depois do completed).
        $paidAt = $paymentStatus === 'completed'
            ? ($payment->paid_at ?? now())
            : $payment->paid_at;

        $payment->update([
            'status' => $paymentStatus,
            'gateway_response' => $response,
            'paid_at' => $paidAt,
        ]);

        $orderStatus = match ($paymentStatus) {
            'completed' => 'paid',
            'failed', 'refunded' => 'cancelled',
            'processing' => 'processing',
            default => $order->status,
        };

        if ($orderStatus !== $order->status) {
            $order->update(['status' => $orderStatus]);
        }
    }

    protected function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/[^\d+]/', '', trim($phone));

        if (str_starts_with($digits, '+')) {
            return $digits;
        }

        if (str_starts_with($digits, '258')) {
            return '+' . $digits;
        }

        if (strlen($digits) === 9 && str_starts_with($digits, '8')) {
            return '+258' . $digits;
        }

        return '+' . $digits;
    }
}
