<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items.product', 'payment');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,paid,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        // Mantém a tabela de pagamentos coerente com ações manuais do admin.
        if ($validated['status'] === 'paid') {
            $this->markManuallyPaid($order);
        }

        if ($validated['status'] === 'cancelled') {
            $order->payment()->latest()->first()?->update(['status' => 'failed']);
        }

        return back()->with('success', 'Estado do pedido atualizado!');
    }

    protected function markManuallyPaid(Order $order): void
    {
        $payment = $order->payment()->latest()->first();

        if ($payment) {
            $payment->update([
                'status' => 'completed',
                'paid_at' => $payment->paid_at ?? now(),
            ]);

            return;
        }

        $order->payment()->create([
            'transaction_id' => null,
            'payment_method' => 'manual',
            'amount' => $order->total,
            'currency' => 'MZN',
            'status' => 'completed',
            'gateway_response' => ['source' => 'admin_manual_confirmation'],
            'paid_at' => now(),
        ]);
    }
}
