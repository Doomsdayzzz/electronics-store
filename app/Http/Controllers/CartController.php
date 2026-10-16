<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    /**
     * Получить текущую корзину (для отображения на странице)
     */
    public function index(): JsonResponse
    {
        $cartIds = [];

        if (auth()->check()) {
            $cart = Cart::where('user_id', auth()->id())->first();
            if ($cart) {
                $cartIds = CartItem::where('cart_id', $cart->id)
                    ->pluck('product_id')
                    ->toArray();
            }
        }

        return response()->json(['product_ids' => $cartIds]);
    }

    /**
     * Синхронизация корзины (только для авторизованных)
     */
    public function sync(Request $request): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Необходима авторизация'], 401);
        }

        $productIds = $request->input('products', []);

        if (empty($productIds)) {
            return response()->json(['success' => true, 'message' => 'Корзина пуста']);
        }

        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);

        // Удаляем старые позиции
        CartItem::where('cart_id', $cart->id)->delete();

        // Добавляем новые позиции
        foreach ($productIds as $productId) {
            $product = Product::find($productId);

            if (!$product) {
                continue;
            }

            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Корзина сохранена',
            'items_count' => count($productIds),
        ]);
    }

    /**
     * Объединение гостевой корзины (localStorage) с аккаунтом при логине
     */
    public function merge(Request $request): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Необходима авторизация'], 401);
        }

        $productIds = $request->input('products', []);

        if (empty($productIds)) {
            return response()->json(['success' => true]);
        }

        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);

        // Добавляем товары из localStorage (без удаления существующих)
        foreach ($productIds as $productId) {
            $product = Product::find($productId);

            if (!$product) {
                continue;
            }

            // Проверяем, нет ли уже такого товара в корзине
            $existing = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existing) {
                $existing->quantity += 1;
                $existing->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => $product->price,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Корзина объединена',
            'items_count' => count($productIds),
        ]);
    }
}
