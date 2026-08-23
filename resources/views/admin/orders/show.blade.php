@extends('admin.layouts.app')
@section('title', 'Pedido #' . $order->order_number . ' - Admin GoodTech')

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.orders.index') }}" class="text-sm text-brand-600 hover:underline">&larr; Voltar</a>
    <div class="flex items-center justify-between mt-2">
        <h1 class="text-2xl font-bold">Pedido #{{ $order->order_number }}</h1>
        <span class="px-3 py-1 rounded-full text-sm font-medium
            @if($order->status === 'paid') bg-green-100 text-green-700
            @elseif($order->status === 'pending') bg-yellow-100 text-yellow-700
            @elseif($order->status === 'cancelled') bg-red-100 text-red-700
            @else bg-blue-100 text-blue-700 @endif">
            {{ ucfirst($order->status) }}
        </span>
    </div>
    @if(in_array($order->status, ['pending', 'processing']))
    <a href="{{ route('payment.link', $order->order_number) }}" target="_blank"
       class="inline-flex items-center gap-2 mt-3 px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
        🔗 Gerar link de pagamento para o cliente
    </a>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold mb-4">Itens do Pedido</h2>
            <div class="space-y-3">
                @foreach($order->items as $item)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <div>
                        <span class="font-medium text-sm">{{ $item->product_name }}</span>
                        <span class="text-gray-400 text-sm ml-2">x{{ $item->quantity }}</span>
                    </div>
                    <span class="font-medium text-sm">{{ number_format($item->total, 2, ',', '.') }} MT</span>
                </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100 space-y-1 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>{{ number_format($order->subtotal, 2, ',', '.') }} MT</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Envio</span><span>{{ number_format($order->shipping_cost, 2, ',', '.') }} MT</span></div>
                <div class="flex justify-between font-bold text-lg"><span>Total</span><span class="text-brand-700">{{ number_format($order->total, 2, ',', '.') }} MT</span></div>
            </div>
        </div>

        @if($order->payment)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold mb-4">Pagamento</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Método</span><span class="font-medium uppercase">{{ $order->payment->payment_method }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Estado</span><span class="font-medium">{{ $order->payment->status }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Montante</span><span class="font-medium">{{ number_format($order->payment->amount, 2, ',', '.') }} {{ $order->payment->currency }}</span></div>
                @if($order->payment->transaction_id)
                <div class="flex justify-between"><span class="text-gray-500">Transação</span><span class="font-medium">{{ $order->payment->transaction_id }}</span></div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold mb-4">Cliente</h2>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-500">Nome:</span> <span class="font-medium">{{ $order->customer_name }}</span></div>
                <div><span class="text-gray-500">Email:</span> <span class="font-medium">{{ $order->customer_email }}</span></div>
                @if($order->customer_phone)
                <div><span class="text-gray-500">Telefone:</span> <span class="font-medium">{{ $order->customer_phone }}</span></div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold mb-4">Envio</h2>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-500">Cidade:</span> <span class="font-medium">{{ $order->shipping_city }}</span></div>
                <div><span class="text-gray-500">Endereço:</span> <span class="font-medium">{{ $order->shipping_address }}</span></div>
                @if($order->notes)
                <div><span class="text-gray-500">Notas:</span> <span class="font-medium">{{ $order->notes }}</span></div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold mb-4">Atualizar Estado</h2>
            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                @csrf @method('PUT')
                <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @foreach(['pending', 'processing', 'paid', 'shipped', 'delivered', 'cancelled'] as $s)
                        <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full bg-brand-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-brand-700 transition">
                    Atualizar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
