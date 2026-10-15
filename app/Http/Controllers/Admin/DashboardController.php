<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ===== Товары: поиск + фильтры =====
        $productsQuery = Product::Filters($request)->with(['category', 'images']);

        $products = $productsQuery
            ->latest()
            ->paginate(20, ['*'], 'page')
            ->withQueryString();

        // ===== Пользователи: пагинация со своим ключом =====
        $users = User::orderByDesc('created_at')
            ->paginate(15, ['*'], 'users_page')
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('admin.dashboard', compact('products', 'users', 'categories'));
    }
}
