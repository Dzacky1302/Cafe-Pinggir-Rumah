<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckoutRequest;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\Request;

final class CheckoutController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function chooseType()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Keranjang kosong. Pilih menu dulu ya!');
        }

        return view('checkout.type', [
            'cart' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    public function showForm(Request $request)
    {
        $request->validate(['order_type' => 'required|in:dine_in,take_away']);

        if ($this->cart->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Keranjang kosong.');
        }

        session()->put('order_type', $request->input('order_type'));

        return view('checkout.form', [
            'cart' => $this->cart->items(),
            'total' => $this->cart->total(),
            'order_type' => $request->input('order_type'),
        ]);
    }

    public function store(StoreCheckoutRequest $request, CheckoutService $checkout)
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Keranjang kosong.');
        }

        $order = $checkout->placeOrder($request->input('order_type'), $request->validated());

        session()->put('current_order_id', $order->order_id);

        return redirect()->route('checkout.payment', $order->order_id);
    }
}
