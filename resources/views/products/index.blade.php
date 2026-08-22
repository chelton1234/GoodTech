@extends('layouts.app')
@section('title', 'Produtos - GoodTech')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-col md:flex-row gap-8">

        <aside class="md:w-64 shrink-0">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                <h3 class="font-semibold mb-4">Categorias</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('products.index') }}" class="text-sm flex items-center justify-between py-1.5 {{ !request('category') ? 'text-brand-600 font-medium' : 'text-gray-600 hover:text-brand-600' }}">
                            Todos
                            <span class="text-xs text-gray-400">{{ $products->total() }}</span>
                        </a>
                    </li>
                    @foreach($categories as $cat)
                    <li>
                        <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="text-sm flex items-center justify-between py-1.5 {{ request('category') === $cat->slug ? 'text-brand-600 font-medium' : 'text-gray-600 hover:text-brand-600' }}">
                            {{ $cat->name }}
                            <span class="text-xs text-gray-400">{{ $cat->products_count }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>

                <form method="GET" action="{{ route('products.index') }}" class="mt-6">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Pesquisar..."
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </form>
            </div>
        </aside>

        <div class="flex-1">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">
                    @if(request('category'))
                        {{ $categories->where('slug', request('category'))->first()->name ?? 'Produtos' }}
                    @elseif(request('search'))
                        Resultados para "{{ request('search') }}"
                    @else
                        Todos os Produtos
                    @endif
                </h1>
            </div>

            @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($products as $product)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition group">
                    <a href="{{ route('products.show', $product) }}">
                        <div class="aspect-square bg-gray-100 relative overflow-hidden">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-50 to-brand-100">
                                    <svg class="w-16 h-16 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                            @endif
                            @if($product->compare_price)
                                <span class="absolute top-3 left-3 bg-accent-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                                    -{{ round((1 - $product->price / $product->compare_price) * 100) }}%
                                </span>
                            @endif
                            @if($product->stock <= 0)
                                <span class="absolute top-3 right-3 bg-gray-800 text-white text-xs font-semibold px-2 py-1 rounded-full">Esgotado</span>
                            @endif
                        </div>
                    </a>
                    <div class="p-4">
                        <p class="text-xs text-brand-600 font-medium mb-1">{{ $product->category->name }}</p>
                        <a href="{{ route('products.show', $product) }}">
                            <h3 class="font-semibold text-sm mb-2 hover:text-brand-600 transition line-clamp-2">{{ $product->name }}</h3>
                        </a>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-lg font-bold text-brand-700">{{ number_format($product->price, 2, ',', '.') }} MT</span>
                            @if($product->compare_price)
                                <span class="text-sm text-gray-400 line-through">{{ number_format($product->compare_price, 2, ',', '.') }} MT</span>
                            @endif
                        </div>
                        <form action="{{ route('cart.add', $product) }}" method="POST">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" {{ $product->stock <= 0 ? 'disabled' : '' }}
                                class="w-full bg-brand-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-brand-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ $product->stock > 0 ? 'Adicionar ao Carrinho' : 'Esgotado' }}
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $products->withQueryString()->links() }}
            </div>
            @else
            <div class="text-center py-20">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Nenhum produto encontrado</h3>
                <p class="text-gray-400 text-sm">Tente outro termo de pesquisa ou categoria.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
