@extends('layouts.app')
@section('title', 'Estado do Pedido - GoodTech')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-10">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
        @if($order->status === 'paid')
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-green-600 mb-2">Pagamento Confirmado!</h1>
        <p class="text-gray-500 mb-6">O seu pagamento foi processado com sucesso.</p>
        @elseif($order->status === 'cancelled')
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-red-600 mb-2">Pagamento Falhou</h1>
        <p class="text-gray-500 mb-6">O pagamento não foi processado. Tente novamente.</p>
        @else
        <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-yellow-600 mb-2">Pagamento Pendente</h1>
        <p class="text-gray-500 mb-6">Aguardamos a confirmação do pagamento.</p>
        @endif

        <div class="bg-gray-50 rounded-xl p-4 text-left mb-6">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Pedido</span>
                    <span class="font-medium">{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Estado</span>
                    <span class="font-medium capitalize">{{ $order->status }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Total</span>
                    <span class="font-bold text-brand-700">{{ number_format($order->total, 2, ',', '.') }} MT</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('home') }}" class="bg-brand-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-brand-700 transition">
                Voltar ao Início
            </a>
            @if($order->status !== 'paid')
            <a href="{{ route('payment.index', $order->order_number) }}" class="border border-gray-200 text-gray-700 px-6 py-2.5 rounded-lg font-medium hover:bg-gray-50 transition">
                Tentar Novamente
            </a>
            @endif
        </div>
    </div>
</div>
@endsection
