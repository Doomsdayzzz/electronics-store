<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Все заказы — только для админа.
     */
    public function index()
    {
        $orders = Order::with(['user', 'items.product'])
            ->latest()
            ->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Один заказ — админская карточка.
     */
    public function adminShow(Order $order)
    {
        $order->load(['user', 'items.product']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Заказы текущего пользователя.
     */
    public function myOrders()
    {
        $orders = Order::with(['items.product'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Один заказ — просмотр только владельцем или админом.
     */
    public function show(Order $order)
    {
        abort_unless(
            auth()->check() && (
                auth()->id() === $order->user_id || auth()->user()->is_admin
            ),
            403
        );

        $order->load(['items.product']);

        return view('orders.show', compact('order'));
    }

    /**
     * Оформить заказ из текущей корзины.
     */
    public function store(OrderStoreRequest $request): JsonResponse
    {
        $cart = Cart::with('items.product')
            ->where('user_id', auth()->id())
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Корзина пуста',
            ], 422);
        }

        DB::transaction(function () use ($request, $cart, &$order) {
            $total = $cart->items->sum(fn ($item) => $item->price * $item->quantity);

            $order = Order::create([
                'user_id'          => auth()->id(),
                'delivery_address' => $request->input('delivery_address'),
                'comment'          => $request->input('comment'),
                'total_price'      => $total,
                'status'           => 'new',
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $item->product_id,
                    'quantity'    => $item->quantity,
                    'total_price' => $item->price * $item->quantity,
                ]);
            }

            // Очищаем корзину после оформления
            $cart->items()->delete();
        });

        return response()->json([
            'success'  => true,
            'message'  => 'Заказ принят',
            'order_id' => $order->id,
        ]);
    }
}
