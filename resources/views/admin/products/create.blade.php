@extends('admin.layouts.app')
@section('title', 'Novo Produto - Admin GoodTech')

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.products.index') }}" class="text-sm text-brand-600 hover:underline">&larr; Voltar</a>
    <h1 class="text-2xl font-bold mt-2">Novo Produto</h1>
</div>

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="max-w-2xl">
    @csrf
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
            @error('name')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Categoria *</label>
            <select name="category_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="">Selecionar...</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            @error('category_id')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea name="description" rows="3" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">{{ old('description') }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Preço (MT) *</label>
                <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Preço Comparativo (MT)</label>
                <input type="number" name="compare_price" value="{{ old('compare_price') }}" step="0.01" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stock *</label>
                <input type="number" name="stock" value="{{ old('stock', 0) }}" min="0" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Imagem</label>
                <input type="file" name="image" accept="image/*" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
        </div>
        <div class="flex items-center gap-6">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-600">
                Ativo
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="rounded border-gray-300 text-brand-600">
                Destaque
            </label>
        </div>
    </div>
    <button type="submit" class="mt-4 bg-brand-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-brand-700 transition">
        Criar Produto
    </button>
</form>
@endsection
