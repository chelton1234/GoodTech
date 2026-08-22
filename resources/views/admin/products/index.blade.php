@extends('admin.layouts.app')
@section('title', 'Produtos - Admin GoodTech')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold">Produtos</h1>
        <p class="text-gray-500 text-sm">{{ $products->total() }} produto(s) no total</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-700 transition">
        + Novo Produto
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Produto</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Categoria</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Preço</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Stock</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Estado</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-100 rounded-lg overflow-hidden shrink-0">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-brand-50">
                                        <svg class="w-5 h-5 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <span class="font-medium">{{ $product->name }}</span>
                                @if($product->is_featured)
                                    <span class="ml-1 text-xs text-yellow-600">★ Destaque</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $product->category->name ?? '-' }}</td>
                    <td class="px-6 py-4 font-medium">{{ number_format($product->price, 2, ',', '.') }} MT</td>
                    <td class="px-6 py-4">
                        <span class="{{ $product->stock <= 5 ? 'text-red-600 font-medium' : '' }}">{{ $product->stock }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $product->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-brand-600 hover:underline text-xs font-medium">Editar</a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Tem certeza?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline text-xs font-medium">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">Nenhum produto encontrado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-100">
        {{ $products->links() }}
    </div>
</div>
@endsection
