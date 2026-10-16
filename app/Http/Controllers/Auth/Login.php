<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class Login extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Объединяем гостевую корзину из localStorage с аккаунтом
            $cartProductsJson = $request->input('cart_products', '[]');
            $productIds = json_decode($cartProductsJson, true) ?? [];
            if (!empty($productIds)) {
                $this->mergeGuestCart($productIds);
            }

            $isAdmin = User::where('email', $request->email)->first()->role;
            if ($isAdmin) {
                return response()->json([
                    'success' => true,
                    'redirect' => '/admin/dashboard',
                ]);
            }
            return response()->json([
                'success' => true,
                'redirect' => '/products'
            ]);
        }
        return response()->json([
            'errors' => [
                'email' => ['Указана неверная почта или пароль']
            ]
        ], 422);
    }

    /**
     * Объединяет гостевую корзину с аккаунтом пользователя
     */
    protected function mergeGuestCart(array $productIds): void
    {
        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        foreach ($productIds as $productId) {
            $product = Product::find($productId);

            if (!$product) {
                continue;
            }

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
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->intended('/products');
    }

    public function GetCarts(Request $request){
        $userId = User::where('email', $request->email)->first()->id;
        $carts = Cart::where('user_id', $userId)->get();
        $cartsItems = CartItem::where('cart_id', $carts->id)->get();

    }
}
