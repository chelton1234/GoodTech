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
            $zarRate = (float) config('services.debitopay.zar_rate', 3.60);
            $zarAmount = round($order->total / max($zarRate, 0.01), 2);
            $payfastMinZar = \App\Services\DbitoPayService::MIN_AMOUNTS['payfast']['ZAR'];
            $abaixoDoMinimo = $zarAmount < $payfastMinZar;
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
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Equivalente em Rand</span>
                    <span>R&thinsp;{{ number_format($zarAmount, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        @if($abaixoDoMinimo)
        <div class="mb-6 rounded-xl border border-orange-200 bg-orange-50 p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-orange-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19H19a2 2 0 001.74-3L13.74 4a2 2 0 00-3.48 0L3.26 16A2 2 0 005 19z"/>
            </svg>
            <p class="text-sm text-orange-700">
                Este pedido equivale a R&thinsp;{{ number_format($zarAmount, 2, ',', '.') }}, abaixo do mínimo de R&thinsp;{{ number_format($payfastMinZar, 2) }} do PayFast.
                Adicione mais produtos para atingir pelo menos {{ number_format($payfastMinZar * $zarRate, 2, ',', '.') }} MT.
            </p>
        </div>
        @endif

        <form action="{{ route('payment.process', $order->order_number) }}" method="POST" x-data="{ processing: false, method: '' }">
            @csrf
            <h3 class="font-semibold mb-3">Método de pagamento:</h3>
            <div class="mb-6">
                <label class="block">
                    <input type="radio" name="payment_method" value="payfast" checked class="hidden peer">
                    <div class="border-2 border-brand-600 bg-brand-50 rounded-xl p-4 flex items-center gap-4">
                        <span class="text-2xl">🛡️</span>
                        <div class="flex-1">
                            <span class="font-medium text-sm">PayFast</span>
                            <p class="text-xs text-gray-500">Será cobrado R&thinsp;{{ number_format($order->total / 3.60, 2, ',', '.') }} (aprox.) via cartão ou EFT na página segura da Débito Pay</p>
                        </div>
                        <svg class="w-5 h-5 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </label>
            </div>

            <button type="submit"
                class="w-full bg-brand-600 text-white py-3 rounded-lg font-semibold hover:bg-brand-700 transition {{ $abaixoDoMinimo ? 'opacity-50 cursor-not-allowed' : '' }}"
                @click="{{ $abaixoDoMinimo ? 'return false' : 'processing = true' }}"
                @if($abaixoDoMinimo) disabled @endif>
                <span x-show="!processing">Pagar agora — R&thinsp;{{ number_format($zarAmount, 2, ',', '.') }}</span>
                <span x-show="processing" x-cloak>Redirecionando para a página segura...</span>
            </button>

            <p x-show="processing" x-cloak
                class="text-xs text-gray-500 text-center mt-3">
                🔒 Vai ser redirecionado para o checkout seguro da Débito Pay para inserir os dados do cartão.
            </p>
        </form>

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
