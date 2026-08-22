<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        $cart = Session::get('cart', []);
        $items = $this->getCartItems($cart);
        $total = collect($items)->sum(fn ($item) => $item['price'] * $item['quantity']);

        return view('cart.index', compact('items', 'total'));
    }

    public function add(Request $request, Product $product)
    {
        $cart = Session::get('cart', []);

        if (!isset($cart[$product->id])) {
            $cart[$product->id] = [
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'image' => $product->image,
                'quantity' => 0,
            ];
        }

        $cart[$product->id]['quantity'] += $request->input('quantity', 1);

        if ($cart[$product->id]['quantity'] > $product->stock) {
            return back()->with('error', 'Estoque insuficiente para este produto.');
        }

        Session::put('cart', $cart);

        return back()->with('success', 'Produto adicionado ao carrinho!');
    }

    public function update(Request $request, Product $product)
    {
        $cart = Session::get('cart', []);
        $quantity = (int) $request->input('quantity');

        if ($quantity <= 0) {
            unset($cart[$product->id]);
        } elseif ($quantity <= $product->stock) {
            $cart[$product->id]['quantity'] = $quantity;
        }

        Session::put('cart', $cart);

        return redirect()->route('cart.index');
    }

    public function remove(Product $product)
    {
        $cart = Session::get('cart', []);
        unset($cart[$product->id]);
        Session::put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Produto removido do carrinho.');
    }

    public function count()
    {
        $cart = Session::get('cart', []);
        return collect($cart)->sum('quantity');
    }

    protected function getCartItems(array $cart): array
    {
        $items = [];
        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if ($product) {
                // Preço sempre da base de dados para refletir alterações recentes.
                $items[] = [
                    'product' => $product,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'image' => $product->image,
                    'quantity' => $item['quantity'],
                    'total' => $product->price * $item['quantity'],
                ];
            }
        }
        return $items;
    }
}
