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
    public function getById(): JsonResponse
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
     * Удаление товара из корзины
     */
    public function remove(Request $request): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Необходима авторизация'], 401);
        }

        $productId = $request->input('product_id');

        $cart = Cart::where('user_id', auth()->id())->first();

        if (!$cart) {
            return response()->json(['success' => true]);
        }

        $deleted = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
        ]);
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
    /**
     * Получить полные данные товаров гостевой корзины по ID
     * (эндпоинт не требует авторизации)
     */
    public function guestDetails(Request $request): JsonResponse
    {
        $ids = $request->input('products', []);

        if (empty($ids)) {
            return response()->json(['items' => []]);
        }

        $products = Product::with(['category', 'images'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $items = [];
        foreach ($ids as $id) {
            if (isset($products[$id])) {
                $items[] = [
                    'product'  => $products[$id],
                    'quantity' => 1,
                    'price'    => (float) $products[$id]->price,
                ];
            }
        }

        return response()->json(['items' => $items]);
    }
}
