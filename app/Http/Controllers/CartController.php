<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\Product;
use App\Services\CartService;

final class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index()
    {
        return view('cart', [
            'cart' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    public function store(AddToCartRequest $request)
    {
        $product = Product::query()->findOrFail($request->validated('product_id'));

        $this->cart->add($product);

        return redirect()->back()->with('success', 'Produk ditambahkan ke keranjang');
    }

    public function destroy(int $id)
    {
        $this->cart->remove($id);

        return redirect()->route('cart')->with('success', 'Produk dihapus');
    }
}
