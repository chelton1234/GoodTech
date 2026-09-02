@extends('layouts.app')
@section('title', 'Pagamento - GoodTech')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-10">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-brand-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold">Pagamento Seguro</h1>
            <p class="text-gray-500 text-sm mt-1">Pedido #{{ $order->order_number }}</p>
        </div>

        @if(session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19H19a2 2 0 001.74-3L13.74 4a2 2 0 00-3.48 0L3.26 16A2 2 0 005 19z"/>
            </svg>
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
        @endif

        @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-green-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
        @endif

        @php
            $zarRate = \App\Services\DbitoPayService::zarRate();
            $zarNet = round($order->total / max($zarRate, 0.01), 2);
            $payfastBr = \App\Services\DbitoPayService::payfastBreakdown($zarNet);
            $zarAmount = $payfastBr['gross'];
            $payfastMinZar = \App\Services\DbitoPayService::MIN_AMOUNTS['payfast']['ZAR'];
            $payfastBelowMin = $zarAmount < $payfastMinZar;

            $methods = [
                'mpesa' => [
                    'label' => 'M-Pesa',
                    'desc' => 'Pagamento pelo telemóvel — confirmação imediata (mín. 10 MT)',
                    'icon' => '📱',
                    'min' => 10,
                    'available' => (string) config('services.debitopay.wallet_mpesa') !== '',
                ],
                'emola' => [
                    'label' => 'e-Mola',
                    'desc' => 'Pagamento pelo telemóvel (mín. 50 MT)',
                    'icon' => '💧',
                    'min' => 50,
                    'available' => (string) config('services.debitopay.wallet_emola') !== '',
                ],
                'visa_mastercard' => [
                    'label' => 'Cartão Visa / Mastercard (MT)',
                    'desc' => 'Debitado em meticais com 3D Secure automático (mín. 50 MT)',
                    'icon' => '💳',
                    'min' => 50,
                    'available' => (string) config('services.debitopay.wallet_card') !== '',
                ],
                'payfast' => [
                    'label' => 'PayFast (Rand)',
                    'desc' => 'Será cobrado R&thinsp;' . number_format($zarAmount, 2, ',', '.') . ' em Rand — taxas incluídas, cai na conta sul-africana',
                    'icon' => '🛡️',
                    'available' => (string) config('services.debitopay.wallet_payfast') !== '',
                ],
            ];
            $paymentMethods = array_filter($methods, fn ($m) => $m['available']);

            $minWarnings = [];
            foreach ($paymentMethods as $value => $m) {
                if (($m['min'] ?? 0) > 0 && $order->total < $m['min']) {
                    $minWarnings[$value] = $m['min'];
                }
            }
        @endphp

        <div class="bg-gray-50 rounded-xl p-4 mb-6">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Itens</span>
                    <span>{{ $order->items->count() }} produto(s)</span>
                </div>
                @foreach($order->items as $item)
                <div class="flex justify-between text-xs text-gray-400">
                    <span>{{ $item->product_name }} x{{ $item->quantity }}</span>
                    <span>{{ number_format($item->total, 2, ',', '.') }} MT</span>
                </div>
                @endforeach
                <div class="border-t pt-2 flex justify-between font-bold text-lg">
                    <span>Total</span>
                    <span class="text-brand-700">{{ number_format($order->total, 2, ',', '.') }} MT</span>
                </div>
            </div>
        </div>

        @if($payfastBelowMin)
        <div class="mb-6 rounded-xl border border-orange-200 bg-orange-50 p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-orange-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19H19a2 2 0 001.74-3L13.74 4a2 2 0 00-3.48 0L3.26 16A2 2 0 005 19z"/>
            </svg>
            <p class="text-sm text-orange-700">
                Este pedido fica abaixo do mínimo de R&thinsp;{{ number_format($payfastMinZar, 2) }} do PayFast.
                Escolha um método em meticais (M-Pesa, e-Mola ou Cartão) para pagar normalmente.
            </p>
        </div>
        @endif

        <form action="{{ route('payment.process', $order->order_number) }}" method="POST" x-data="{ processing: false }">
            @csrf
            <h3 class="font-semibold mb-3">Método de pagamento:</h3>
            <div class="mb-6">
                <fieldset>
                    <legend class="sr-only">Método de pagamento</legend>
                    @include('partials.payment-methods', [
                        'paymentMethods' => $paymentMethods,
                        'minWarnings' => $minWarnings,
                    ])
                </fieldset>
            </div>

            <button type="submit"
                class="w-full bg-brand-600 text-white py-3 rounded-lg font-semibold hover:bg-brand-700 transition"
                @click="processing = true">
                <span x-show="!processing">Pagar agora</span>
                <span x-show="processing" x-cloak>A processar...</span>
            </button>

            <p x-show="processing" x-cloak
                class="text-xs text-gray-500 text-center mt-3">
                🔒 A redirecionar para a página segura da Débito Pay quando necessário.
            </p>
        </form>

        @unless($payfastBelowMin)
        <div class="mt-4 text-center">
            <a href="{{ route('payment.link', $order->order_number) }}"
               class="text-sm text-brand-700 hover:text-brand-800 underline">
                Ou gere um link de pagamento para usar noutro dispositivo 📱
            </a>
        </div>
        @endunless

        <div class="mt-6 text-center">
            <div class="flex items-center justify-center gap-2 text-xs text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Pagamento processado com segurança via Débito Pay
            </div>
        </div>
    </div>
</div>
@endsection