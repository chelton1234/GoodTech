<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/produtos', [ProductController::class, 'index'])->name('products.index');
Route::get('/produtos/{product}', [ProductController::class, 'show'])->name('products.show');

Route::get('/carrinho', [CartController::class, 'index'])->name('cart.index');
Route::post('/carrinho/adicionar/{product}', [CartController::class, 'add'])->name('cart.add');
Route::put('/carrinho/atualizar/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/carrinho/remover/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/carrinho/contar', [CartController::class, 'count'])->name('cart.count');

Route::get('/finalizar', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/finalizar', [CheckoutController::class, 'store'])->name('checkout.store');

Route::post('/pagamento/callback', [PaymentController::class, 'callback'])->name('payment.callback');
Route::get('/pagamento/{orderNumber}', [PaymentController::class, 'index'])->name('payment.index');
Route::post('/pagamento/{orderNumber}', [PaymentController::class, 'process'])->name('payment.process');
Route::get('/pagamento/{orderNumber}/status', [PaymentController::class, 'status'])->name('payment.status');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', AdminProductController::class);
    Route::get('pedidos', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('pedidos/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::put('pedidos/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
});
