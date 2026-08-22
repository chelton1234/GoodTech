@extends('layouts.app')
@section('title', $product->name . ' - GoodTech')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <nav class="text-sm text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-brand-600">Início</a>
        <span class="mx-2">/</span>
        <a href="{{ route('products.index') }}" class="hover:text-brand-600">Produtos</a>
        <span class="mx-2">/</span>
        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-brand-600">{{ $product->category->name }}</a>
        <span class="mx-2">/</span>
        <span class="text-gray-900">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="aspect-square bg-gray-100 relative">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-50 to-brand-100">
                        <svg class="w-32 h-32 text-brand-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                @endif
                @if($product->compare_price)
                    <span class="absolute top-4 left-4 bg-accent-500 text-white text-sm font-semibold px-3 py-1 rounded-full">
                        -{{ round((1 - $product->price / $product->compare_price) * 100) }}%
                    </span>
                @endif
            </div>
        </div>

        <div>
            <span class="text-sm text-brand-600 font-medium">{{ $product->category->name }}</span>
            <h1 class="text-3xl font-bold mt-2 mb-4">{{ $product->name }}</h1>

            <div class="flex items-center gap-3 mb-6">
                <span class="text-3xl font-bold text-brand-700">{{ number_format($product->price, 2, ',', '.') }} MT</span>
                @if($product->compare_price)
                    <span class="text-lg text-gray-400 line-through">{{ number_format($product->compare_price, 2, ',', '.') }} MT</span>
                @endif
            </div>

            @if($product->description)
            <div class="prose prose-sm text-gray-600 mb-6">
                <p>{{ $product->description }}</p>
            </div>
            @endif

            <div class="flex items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Disponibilidade:</span>
                    @if($product->stock > 0)
                        <span class="text-sm font-medium text-green-600">Em stock ({{ $product->stock }} unidades)</span>
                    @else
                        <span class="text-sm font-medium text-red-600">Esgotado</span>
                    @endif
                </div>
            </div>

            @if($product->stock > 0)
            <form action="{{ route('cart.add', $product) }}" method="POST" class="flex items-center gap-4 mb-8">
                @csrf
                <div class="flex items-center border border-gray-200 rounded-lg">
                    <button type="button" onclick="this.nextElementSibling.value = Math.max(1, parseInt(this.nextElementSibling.value) - 1)" class="px-3 py-2 text-gray-600 hover:text-brand-600">-</button>
                    <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-16 text-center border-0 focus:ring-0">
                    <button type="button" onclick="this.previousElementSibling.value = Math.min({{ $product->stock }}, parseInt(this.previousElementSibling.value) + 1)" class="px-3 py-2 text-gray-600 hover:text-brand-600">+</button>
                </div>
                <button type="submit" class="flex-1 bg-brand-600 text-white py-3 rounded-lg font-semibold hover:bg-brand-700 transition">
                    Adicionar ao Carrinho
                </button>
            </form>
            @endif

            <div class="border-t border-gray-100 pt-6 space-y-3 text-sm text-gray-500">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Envio para todo o Moçambique
                </div>
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Pagamento via M-Pesa, e-Mola, mKesh ou cartão
                </div>
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Garantia do fabricante incluída
                </div>
            </div>
        </div>
    </div>

    @if($related->count() > 0)
    <div class="mt-16">
        <h2 class="text-2xl font-bold mb-6">Produtos Relacionados</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($related as $rel)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                <a href="{{ route('products.show', $rel) }}">
                    <div class="aspect-square bg-gray-100">
                        @if($rel->image)
                            <img src="{{ asset('storage/' . $rel->image) }}" alt="{{ $rel->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-50 to-brand-100">
                                <svg class="w-12 h-12 text-brand-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                </a>
                <div class="p-4">
                    <h3 class="font-semibold text-sm mb-2 line-clamp-2">{{ $rel->name }}</h3>
                    <span class="text-lg font-bold text-brand-700">{{ number_format($rel->price, 2, ',', '.') }} MT</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
