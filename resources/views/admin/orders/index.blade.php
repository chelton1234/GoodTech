@extends('admin.layouts.app')
@section('title', 'Pedidos - Admin GoodTech')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold">Pedidos</h1>
    <p class="text-gray-500 text-sm">{{ $orders->total() }} pedido(s) no total</p>
</div>

<div class="flex gap-2 mb-6 flex-wrap">
    <a href="{{ route('admin.orders.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ !request('status') ? 'bg-brand-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">Todos</a>
    @foreach(['pending', 'processing', 'paid', 'shipped', 'delivered', 'cancelled'] as $s)
        <a href="{{ route('admin.orders.index', ['status' => $s]) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('status') === $s ? 'bg-brand-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">{{ ucfirst($s) }}</a>
    @endforeach
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Pedido</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Cliente</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Itens</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Total</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Estado</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Data</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">{{ $order->order_number }}</td>
                    <td class="px-6 py-4">
                        <div>{{ $order->customer_name }}</div>
                        <div class="text-xs text-gray-400">{{ $order->customer_email }}</div>
                    </td>
                    <td class="px-6 py-4">{{ $order->items->count() }}</td>
                    <td class="px-6 py-4 font-medium">{{ number_format($order->total, 2, ',', '.') }} MT</td>
                    <td class="px-6 py-4">
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'processing' => 'bg-blue-100 text-blue-700',
                                'paid' => 'bg-green-100 text-green-700',
                                'shipped' => 'bg-purple-100 text-purple-700',
                                'delivered' => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$order->status] ?? '' }}">
                            {{ $order->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-brand-600 hover:underline text-xs font-medium">Ver Detalhes</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-400">Nenhum pedido encontrado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-100">
        {{ $orders->withQueryString()->links() }}
    </div>
</div>
@endsection
