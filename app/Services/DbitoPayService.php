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
    protected float $zarRate;

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
        $this->zarRate = (float) config('services.debitopay.zar_rate', 3.60);
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

    public function createPayment(Order $order, string $paymentMethod): array
    {
        $method = $paymentMethod === 'card' ? 'visa_mastercard' : $paymentMethod;

        if (!in_array($method, self::METHODS, true)) {
            return ['success' => false, 'message' => 'Método de pagamento não suportado.'];
        }

        if ($method === 'payfast') {
            $currency = 'ZAR';
            $amount = round($order->total / max($this->zarRate, 0.01), 2);
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
                        ? sprintf('A redirecionar para o PayFast (R %s).', number_format($amount, 2))
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
            Log::warning('Invalid Débito Pay webhook signature');

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

        $expected = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        return hash_equals($expected, $signature);
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
