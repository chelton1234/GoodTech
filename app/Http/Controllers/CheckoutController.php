<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'O seu carrinho está vazio.');
        }

        $items = collect($cart)->map(function ($item, $productId) {
            $product = Product::find($productId);

            if (!$product) {
                return null;
            }

            // Preço sempre da base de dados para refletir alterações recentes.
            return [
                'product' => $product,
                'name' => $product->name,
                'price' => (float) $product->price,
                'quantity' => $item['quantity'],
                'total' => $product->price * $item['quantity'],
            ];
        })->filter()->values();

        $subtotal = $items->sum('total');
        $shipping = 0; // Frete desativado durante fase de testes.
        $total = $subtotal + $shipping;

        $paymentMethods = $this->paymentMethods();

        return view('checkout.index', compact('items', 'subtotal', 'shipping', 'total', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:payfast,mpesa,emola,visa_mastercard',
        ]);

        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'O seu carrinho está vazio.');
        }

        $subtotal = 0;
        foreach ($cart as $productId => $item) {
            $product = Product::findOrFail($productId);
            // Preço atual da base de dados, nunca o valor congelado na sessão.
            $subtotal += $product->price * $item['quantity'];
        }

        $shipping = 0; // Frete desativado durante fase de testes.
        $total = $subtotal + $shipping;

        $order = Order::create([
            'order_number' => 'GDT-' . strtoupper(Str::random(8)),
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'shipping_address' => $validated['shipping_address'],
            'shipping_city' => $validated['shipping_city'],
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping,
            'total' => $total,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($cart as $productId => $item) {
            $product = Product::findOrFail($productId);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'total' => $product->price * $item['quantity'],
            ]);

            $product->decrement('stock', $item['quantity']);
        }

        Session::forget('cart');

        return redirect()->route('payment.index', $order->order_number);
    }

    protected function paymentMethods()
    {
        $methods = [
            'mpesa' => [
                'label' => 'M-Pesa',
                'desc' => 'Pagamento pelo telemóvel — confirmação imediata (mín. 10 MT)',
                'icon' => '📱',
                'min' => 10,
                'available' => (string) config('services.debitopay.wallet_mpesa') !== '',
            ],
            'emola' => [
                'label' => 'e-Mola',
                'desc' => 'Pagamento pelo telemóvel (mín. 50 MT)',
                'icon' => '💧',
                'min' => 50,
                'available' => (string) config('services.debitopay.wallet_emola') !== '',
            ],
            'visa_mastercard' => [
                'label' => 'Cartão Visa / Mastercard',
                'desc' => 'Debitado em meticais com 3D Secure automático (mín. 50 MT)',
                'icon' => '💳',
                'min' => 50,
                'available' => (string) config('services.debitopay.wallet_card') !== '',
            ],
            'payfast' => [
                'label' => 'PayFast (Rand)',
                'desc' => 'Cartão internacional ou EFT, cobrado em Rand para a conta sul-africana',
                'icon' => '🛡️',
                'available' => (string) config('services.debitopay.wallet_payfast') !== '',
            ],
        ];

        return array_filter($methods, fn ($m) => $m['available']);
    }
}
