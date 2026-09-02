@extends('layouts.app')

@section('title', 'Link de Pagamento — ' . $order->order_number)

@section('content')
<div class="max-w-lg mx-auto px-4 py-10">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-brand-50 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 01-5.656 0l-3-3a4 4 0 015.656-5.656l1.5 1.5m6.364-6.364l3 3a4 4 0 01-5.656 5.656"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold">Link de Pagamento</h1>
            <p class="text-sm text-gray-500 mt-1">Pedido {{ $order->order_number }} — {{ number_format($order->total, 2, ',', '.') }} MT</p>
        </div>

        @php
            $zarRate = \App\Services\DbitoPayService::zarRate();
            $zarNet = round($order->total / max($zarRate, 0.01), 2);
            $payfastBr = \App\Services\DbitoPayService::payfastBreakdown($zarNet);
        @endphp

        <div class="bg-gray-50 rounded-xl p-4 mb-4 text-center">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($payLink) }}"
                 alt="QR Code de pagamento"
                 class="mx-auto rounded-lg border border-gray-200 bg-white">
            <p class="text-xs text-gray-500 mt-2">Aponte a câmera do telemóvel para pagar</p>
        </div>

        <div class="mb-4">
            <label class="block text-xs font-medium text-gray-500 mb-1">Ou copie o link para enviar por WhatsApp:</label>
            <div class="flex gap-2">
                <input type="text" id="paylink" value="{{ $payLink }}" readonly
                       class="flex-1 text-xs bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-600 truncate focus:outline-none">
                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('paylink').value); this.textContent='Copiado!'"
                        class="shrink-0 px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                    Copiar
                </button>
            </div>
        </div>

        <a href="{{ $payLink }}" target="_blank" rel="noopener"
           class="block w-full text-center bg-gray-900 text-white py-3 rounded-lg font-semibold hover:bg-black transition mb-3">
            Abrir página segura agora
        </a>

        <p class="text-center text-xs text-gray-400">
            Valor a pagar (taxas incluídas): R&thinsp;{{ number_format($payfastBr['gross'], 2, ',', '.') }}
            · Líquido para a conta: R&thinsp;{{ number_format($payfastBr['net'], 2, ',', '.') }}
        </p>
    </div>
</div>
@endsection
