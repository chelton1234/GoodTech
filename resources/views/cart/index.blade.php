@extends('layouts.app')
@section('title', 'Carrinho - GoodTech')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold mb-8">Carrinho de Compras</h1>

    @if(count($items) > 0)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-4">
            @foreach($items as $item)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex gap-4">
                <div class="w-20 h-20 bg-gray-100 rounded-xl overflow-hidden shrink-0">
                    @if($item['image'])
                        <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-50 to-brand-100">
                            <svg class="w-8 h-8 text-brand-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-sm">{{ $item['name'] }}</h3>
                    <p class="text-brand-600 font-bold text-sm mt-1">{{ number_format($item['price'], 2, ',', '.') }} MT</p>
                    <div class="flex items-center justify-between mt-3">
                        <form action="{{ route('cart.update', $item['product']) }}" method="POST" class="flex items-center">
                            @csrf
                            @method('PUT')
                            <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1"
                                class="w-16 px-2 py-1 border border-gray-200 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-brand-500"
                                onchange="this.form.submit()">
                        </form>
                        <div class="flex items-center gap-4">
                            <span class="font-semibold text-sm">{{ number_format($item['total'], 2, ',', '.') }} MT</span>
                            <form action="{{ route('cart.remove', $item['product']) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                <h2 class="font-semibold mb-4">Resumo do Pedido</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span>{{ number_format($total, 2, ',', '.') }} MT</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Envio</span>
                        <span>{{ $total >= 5000 ? 'Grátis' : number_format(350, 2, ',', '.') . ' MT' }}</span>
                    </div>
                    <div class="border-t pt-3 flex justify-between font-bold text-lg">
                        <span>Total</span>
                        <span class="text-brand-700">{{ number_format($total >= 5000 ? $total : $total + 350, 2, ',', '.') }} MT</span>
                    </div>
                </div>
                <a href="{{ route('checkout.index') }}" class="block w-full bg-brand-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-brand-700 transition mt-6">
                    Finalizar Compra
                </a>
                <a href="{{ route('products.index') }}" class="block text-center text-sm text-brand-600 mt-3 hover:underline">
                    Continuar Comprando
                </a>
            </div>
        </div>
    </div>
    @else
    <div class="text-center py-20">
        <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-600 mb-2">O seu carrinho está vazio</h3>
        <p class="text-gray-400 text-sm mb-6">Adicione produtos ao carrinho para continuar.</p>
        <a href="{{ route('products.index') }}" class="inline-block bg-brand-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-brand-700 transition">
            Ver Produtos
        </a>
    </div>
    @endif
</div>
@endsection
