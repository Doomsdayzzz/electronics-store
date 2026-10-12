@extends('layouts.main')
@section('content')
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


@endsection
