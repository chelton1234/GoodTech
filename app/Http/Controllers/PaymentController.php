<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\DbitoPayService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected DbitoPayService $dbitoPay) {}

    public function index(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with('items.product')
            ->firstOrFail();

        return view('payment.index', compact('order'));
    }

    public function process(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        if ($order->status === 'paid') {
            return redirect()->route('payment.status', $order->order_number);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:payfast',
        ]);

        $result = $this->dbitoPay->createPayment($order, $validated['payment_method']);

        if ($result['success']) {
            if (!empty($result['redirect_url'])) {
                return redirect($result['redirect_url']);
            }

            return redirect()->route('payment.status', $order->order_number)
                ->with('success', $result['message']);
        }

        return back()->withInput()->with('error', $result['message']);
    }

    public function status(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with('items.product', 'payment')
            ->firstOrFail();

        $payment = $order->payment;

        if ($payment && $payment->status === 'pending') {
            $this->dbitoPay->verifyPayment($order->order_number);
            $order->refresh();
            $order->load('payment');
        }

        return view('payment.status', compact('order'));
    }

    public function callback(Request $request)
    {
        $signature = $request->header('X-Signature')
            ?? $request->header('X-Webhook-Signature')
            ?? $request->header('Signature');

        $this->dbitoPay->handleWebhook($request->getContent(), $signature);

        return response()->json(['status' => 'ok']);
    }
}
