<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\ProductImage;

class CartPageController extends Controller
{
    public function index()
    {
        $items = collect();

        if (auth()->check()) {
            $cart = Cart::with(['items.product.category', 'items.product.images'])
                ->where('user_id', auth()->id())
                ->first();

            if ($cart) {
                $items = $cart->items->map(function ($item) {
                    return [
                        'product'  => $item->product,
                        'quantity' => $item->quantity,
                        'price'    => (float) $item->price,
                    ];
                });
            }
        }

        return view('cart.index', compact('items'));
    }
}
