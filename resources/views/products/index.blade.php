<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Магазин электроники</title>
    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
        }

        body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
            color: #2f3640;
            line-height: 1.6;
        }

        /* ===== Header ===== */
header {
    background-color: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-inner {
    max-width: 1280px;
            margin: 0 auto;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .logo {
    font-size: 22px;
            font-weight: 700;
            color: #0984e3;
            text-decoration: none;
            white-space: nowrap;
        }

        .logo span {
    color: #2f3640;
}

.header-left {
    display: flex;
    align-items: center;
            gap: 16px;
        }

        .auth-links {
    display: flex;
    align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .auth-links a {
    color: #636e72;
    text-decoration: none;
            transition: color 0.2s;
        }

        .auth-links a:hover {
    color: #0984e3;
}

.auth-divider {
    width: 1px;
            height: 18px;
            background-color: #dfe6e9;
        }

        .header-right {
    display: flex;
    align-items: center;
            gap: 16px;
        }

        .cart-btn {
    display: flex;
    align-items: center;
            gap: 8px;
            background-color: #0984e3;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .cart-btn:hover {
    background-color: #0770c4;
        }

        .cart-btn svg {
    width: 18px;
            height: 18px;
        }

        /* ===== Layout ===== */
.container {
    max-width: 1280px;
            margin: 0 auto;
            padding: 32px 24px 64px;
            display: grid;
            grid-template-columns: 1fr 260px;
            gap: 32px;
            align-items: start;
        }

        .page-title {
    font-size: 26px;
            font-weight: 700;
            margin-bottom: 24px;
            grid-column: 1 / -1;
        }

        /* ===== Products grid ===== */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }

        .product-card {
    background-color: #ffffff;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .product-card:hover {
    transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .product-image-wrapper {
    width: 100%;
            height: 180px;
            border-radius: 8px;
            /*overflow: hidden;*/
            margin-bottom: 12px;
            /*background-color: #f5f6fa;*/
            text-align: center;
        }

        .product-image-wrapper img {
            /*width: 100%;*/
            height: 100%;
            /*object-fit: cover;*/
            transition: transform 0.3s;
        }

        .product-card:hover .product-image-wrapper img {
    transform: scale(1.05);
        }

        .product-category {
    font-size: 12px;
            color: #0984e3;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .product-name {
    font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #2f3640;
        }

        .product-description {
    font-size: 13px;
            color: #636e72;
            margin-bottom: 16px;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-prices {
    display: flex;
    align-items: baseline;
            gap: 10px;
            margin-bottom: 16px;
        }

        .price-current {
    font-size: 20px;
            font-weight: 700;
            color: #2f3640;
        }

        .price-old {
    font-size: 14px;
            color: #b2bec3;
            text-decoration: line-through;
        }

        .add-to-cart-btn {
    width: 100%;
    background-color: #00b894;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .add-to-cart-btn:hover {
    background-color: #009e7f;
        }

        .out-of-stock {
    background-color: #dfe6e9;
            color: #b2bec3;
            cursor: not-allowed;
        }

        .out-of-stock:hover {
    background-color: #dfe6e9;
        }

        /* ===== Sidebar (categories) ===== */
.sidebar {
    background-color: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 88px;
        }

        .sidebar-title {
    font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #636e72;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f2f6;
        }

        .category-list {
    list-style: none;
        }

        .category-list li {
    margin-bottom: 4px;
        }

        .category-list a {
    display: block;
    padding: 8px 12px;
            border-radius: 8px;
            color: #2f3640;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.2s, color 0.2s;
        }

        .category-list a:hover {
    background-color: #f1f2f6;
            color: #0984e3;
        }

        .category-list a.active {
    background-color: #0984e3;
            color: #fff;
        }

        /* ===== Pagination ===== */
.pagination-wrapper {
    margin-top: 32px;
            display: flex;
            justify-content: center;
        }

        .pagination-wrapper nav > div:first-child {
    display: none;
}

.pagination-wrapper a,
.pagination-wrapper span[aria-current="page"] span,
.pagination-wrapper span[aria-disabled="true"] span {
    display: inline-flex;
    align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            padding: 0 12px;
            margin: 0 4px;
            border-radius: 8px;
            font-size: 14px;
            text-decoration: none;
            color: #2f3640;
            background-color: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        .pagination-wrapper span[aria-current="page"] span {
    background-color: #0984e3;
            color: #fff;
        }

        .pagination-wrapper span[aria-disabled="true"] span {
    color: #b2bec3;
}

/* ===== Empty state ===== */
.empty-state {
    grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #b2bec3;
            font-size: 16px;
        }

        /* ===== Responsive ===== */
@media (max-width: 900px) {
    .container {
        grid-template-columns: 1fr;
            }

            .sidebar {
        position: static;
        order: -1;
    }

            .auth-links {
        display: none;
    }
        }
    </style>
</head>
<body>

<header>
    <div class="header-inner">
        <div class="header-left">
            <a href="{{ route('products.index') }}" class="logo">Цифро<span>Store</span></a>
            <div class="auth-links">
                <a href="#">Войти</a>
                <div class="auth-divider"></div>
                <a href="#">Регистрация</a>
                <div class="auth-divider"></div>
                <a href="#">Личный кабинет</a>
            </div>
        </div>
        <div class="header-right">
            <button class="cart-btn" type="button" title="Корзина">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
Корзина
            </button>
        </div>
    </div>
</header>

<div class="container">
    <h1 class="page-title">Каталог товаров</h1>

{{-- Список товаров --}}
<section>
    @if($products->count())
        <div class="products-grid">
            @foreach($products as $product)
                <article class="product-card">
                    <div class="product-category">{{ $product->category->name ?? 'Без категории' }}</div>
                    <div class="product-image-wrapper">

                        <img src="{{ $images->where('product_id', $product->id)->first()?->path ?? asset('/storage/images/no-image.jpg') }}" alt="{{ $product->name }}">
                    </div>
                    <h2 class="product-name">{{ $product->name }}</h2>
                    <p class="product-description">{{ $product->description }}</p>

                    <div class="product-prices">
                        <span class="price-current">{{ number_format($product->price, 0, '.', ' ') }} ₽</span>
                        @if($product->old_price && $product->old_price > $product->price)
                            <span class="price-old">{{ number_format($product->old_price, 0, '.', ' ') }} ₽</span>
                        @endif
                    </div>

                    @if($product->stock)
                        <button class="add-to-cart-btn" type="button" title="Заглушка">
                            Добавить в корзину
                        </button>
                    @else
                        <button class="add-to-cart-btn out-of-stock" type="button" disabled>
                            Нет в наличии
                        </button>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="pagination-wrapper">
            {{ $products->links() }}
        </div>
    @else
        <div class="empty-state">
            Товары не найдены
        </div>
    @endif
</section>

{{-- Сайдбар с категориями --}}
<aside class="sidebar">
    <div class="sidebar-title">Категории</div>
    <ul class="category-list">
        <li>
            <a href="{{ route('products.index') }}"
               class="{{ !request('category_id') ? 'active' : '' }}">
                Все товары
            </a>
        </li>
        @foreach(\App\Models\Category::all() as $category)
            <li>
                <a href="{{ route('products.index', ['category_id' => $category->id]) }}"
                   class="{{ request('category_id') == $category->id ? 'active' : '' }}">
                    {{ $category->name }}
                </a>
            </li>
        @endforeach
    </ul>
</aside>
</div>

</body>
</html>
