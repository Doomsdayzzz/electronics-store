<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $products = $query->paginate(12);
        $images = ProductImage::all();

        // Получаем ID товаров в корзине
        $cartProductIds = [];
        if (auth()->check()) {
            $cart = Cart::where('user_id', auth()->id())->first();
            if ($cart) {
                $cartProductIds = CartItem::where('cart_id', $cart->id)
                    ->pluck('product_id')
                    ->toArray();
            }
        }

        return view('products.index', compact(['products', 'images', 'cartProductIds']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStoreRequest $request)
    {
        $validated = $request->validated();
        $product = Product::query()->create($validated);
        if ($request->hasFile('image')) {
            $path = $request->file('image')->storeAs(
                'images/' . $product->id,
                $request->file('image')->getClientOriginalName(),
                'public'
            );
            ProductImage::query()->create(
                [
                    'product_id' => $product->id,
                    'path' => '/storage/' . $path,
                    'is_main' => true
                ]
            );
            return redirect()->route('dashboard')->with('success', 'Товар добавлен');
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $validated = $request->validated();
        $product->update($validated);
        if ($request->hasFile('image')) {
            // Удаляем старые изображения и папку
            Storage::disk('public')->deleteDirectory('images/' . $product->id);
            ProductImage::where('product_id', $product->id)->delete();

            $path = $request->file('image')->storeAs(
                'images/' . $product->id,
                $request->file('image')->getClientOriginalName(),
                'public'
            );

            ProductImage::create([
                'product_id' => $product->id,
                'path'       => '/storage/' . $path,
                'is_main'    => true,
            ]);
        }

        return redirect()->route('admin.dashboard')->with('success', 'Товар обновлён');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        Product::destroy($product->id);
        ProductImage::destroy($product->id);
        Storage::deleteDirectory('/public/storage/images/' . $product->id);

        return redirect()->route('admin.dashboard')->with('success', 'Товар удалён');
    }
}
