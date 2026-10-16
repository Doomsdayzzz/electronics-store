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
                    <p class="product-id">#{{ $product->id }}</p>
                    <h2 class="product-name">{{ $product->name }}</h2>
                    <p class="product-description">{{ $product->description }}</p>

                    <div class="product-prices">
                        <span class="price-current">{{ number_format($product->price, 0, '.', ' ') }} ₽</span>
                        @if($product->old_price && $product->old_price > $product->price)
                            <span class="price-old">{{ number_format($product->old_price, 0, '.', ' ') }} ₽</span>
                        @endif
                    </div>

                    @if($product->stock)
                        <button class="add-to-cart-btn" type="button" id="add-to-cart-{{$product->id}}">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btns = document.querySelectorAll('[id^="add-to-cart-"]');

        // ID товаров в корзине из БД (для авторизованных) или из localStorage (для гостей)
        let cartFromServer = @json($cartProductIds ?? []).map(Number);
        const isAuthorized = {{ auth()->check() ? 'true' : 'false' }};

        function getCartFromStorage() {
            const ids = [];
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key.startsWith('id-product-')) {
                    ids.push(Number(localStorage.getItem(key)));
                }
            }
            return ids;
        }

        function isInCart(productId) {
            if (isAuthorized) {
                return cartFromServer.includes(Number(productId));
            }
            return localStorage.getItem('id-product-' + productId) !== null;
        }

        function syncCartToServer(productIds) {
            if (productIds.length === 0) return;
            fetch('/cart/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ products: productIds })
            })
            .then(response => {
                if (response.status === 401) {
                    return;
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    console.log('Корзина синхронизирована:', data.items_count, 'товаров');
                }
            })
            .catch(err => {
                console.error('Ошибка синхронизации:', err);
            });
        }

        function removeFromCart(productId) {
            fetch('/cart/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Товар удалён из корзины');
                }
            })
            .catch(err => {
                console.error('Ошибка удаления:', err);
            });
        }

        btns.forEach(btn => {
            const id = btn.id.slice(12);
            const productId = Number(id);

            // Проверяем, есть ли товар в корзине (из БД или localStorage)
            if (isInCart(id)) {

                btn.textContent = 'В корзине';
                btn.classList.add('in-cart');
            }

            btn.addEventListener('click', function() {
                if (isAuthorized) {
                    const index = cartFromServer.indexOf(productId);

                    if (index === -1) {
                        // Добавляем в корзину
                        cartFromServer.push(productId);
                        btn.textContent = 'В корзине';
                        btn.classList.add('in-cart');
                        syncCartToServer(cartFromServer);
                    } else {
                        // Удаляем из корзины
                        removeFromCart(productId);
                        cartFromServer.splice(index, 1);
                        btn.textContent = 'Добавить в корзину';
                        btn.classList.remove('in-cart');
                    }
                } else {
                    // Для гостей — работаем с localStorage
                    if (!localStorage.getItem('id-product-' + id)) {
                        localStorage.setItem('id-product-' + id, id);
                        btn.textContent = 'В корзине';
                        btn.classList.add('in-cart');
                    } else {
                        localStorage.removeItem('id-product-' + id);
                        btn.textContent = 'Добавить в корзину';
                        btn.classList.remove('in-cart');
                    }

                    const currentCart = getCartFromStorage();
                    syncCartToServer(currentCart);
                }
                window.refreshCartBadge();
            });
        });
        window.refreshCartBadge();
    });
</script>
