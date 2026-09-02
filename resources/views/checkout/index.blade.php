@extends('layouts.app')
@section('title', 'Finalizar Compra - GoodTech')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold mb-8">Finalizar Compra</h1>

    <form action="{{ route('checkout.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="{ paymentMethod: '' }">
        @csrf
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold mb-4">Dados Pessoais</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @error('customer_name')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" name="customer_email" value="{{ old('customer_email') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @error('customer_email')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="+258 84 000 0000"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cidade *</label>
                        <input type="text" name="shipping_city" value="{{ old('shipping_city', 'Maputo') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @error('shipping_city')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Endereço de Entrega *</label>
                        <textarea name="shipping_address" rows="2" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">{{ old('shipping_address') }}</textarea>
                        @error('shipping_address')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas (opcional)</label>
                        <textarea name="notes" rows="2"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold mb-4">Método de Pagamento</h2>
                <fieldset>
                    <legend class="sr-only">Método de pagamento</legend>
                    @include('partials.payment-methods', ['paymentMethods' => $paymentMethods])
                </fieldset>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                <h2 class="font-semibold mb-4">Resumo do Pedido</h2>
                <div class="space-y-3 mb-4">
                    @foreach($items as $item)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 truncate pr-2">{{ $item['name'] }} x{{ $item['quantity'] }}</span>
                        <span class="font-medium shrink-0">{{ number_format($item['total'], 2, ',', '.') }} MT</span>
                    </div>
                    @endforeach
                </div>
                <div class="border-t pt-3 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span>{{ number_format($subtotal, 2, ',', '.') }} MT</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Envio</span>
                        <span>{{ $shipping == 0 ? 'Grátis' : number_format($shipping, 2, ',', '.') . ' MT' }}</span>
                    </div>
                    <div class="border-t pt-3 flex justify-between font-bold text-lg">
                        <span>Total</span>
                        <span class="text-brand-700">{{ number_format($total, 2, ',', '.') }} MT</span>
                    </div>
                </div>
                <button type="submit"
                    class="w-full bg-brand-600 text-white py-3 rounded-lg font-semibold hover:bg-brand-700 transition mt-6">
                    Confirmar Pedido
                </button>
                <p class="text-xs text-gray-400 text-center mt-3">Será redirecionado para o pagamento seguro</p>
            </div>
        </div>
    </form>
</div>
@endsection
