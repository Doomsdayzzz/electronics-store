@extends('layouts.main')

@section('content')
    <div class="cart-wrap">
        <h1 class="page-title">Корзина</h1>

        {{-- Скелетон/пустое состояние --}}
        <div id="cart-empty" class="cart-empty" style="display:none">
            <div class="cart-empty-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h2 class="cart-empty-title">В корзине пока пусто</h2>
            <p class="cart-empty-text">Загляните в каталог — там много интересного</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary">Перейти в каталог</a>
        </div>

        {{-- Основной контент корзины --}}
        <div id="cart-content" class="cart-layout" style="display:none">
            <div class="cart-items" id="cart-items">
                {{-- Заполняется сервером и/или JS --}}
            </div>

            <aside class="cart-summary">
                <div class="summary-title">Итого</div>

                <div class="summary-row">
                    <span>Товаров</span>
                    <span id="summary-count">0</span>
                </div>
                <div class="summary-row">
                    <span>Сумма</span>
                    <span id="summary-total">0 ₽</span>
                </div>

                <button type="button" class="btn btn-primary summary-btn" id="checkout-btn">
                    Оформить заказ
                </button>

                <a href="{{ route('products.index') }}" class="summary-back">
                    ← Продолжить покупки
                </a>
            </aside>
        </div>
    </div>
    {{-- ===== Модалка оформления заказа ===== --}}
    <div class="modal-overlay" id="checkout-modal" aria-hidden="true">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="checkout-title">
            <button type="button" class="modal-close" aria-label="Закрыть">&times;</button>

            <div class="checkout-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>

            <h2 class="auth-title" id="checkout-title">Оформление заказа</h2>
            <p class="auth-subtitle">Укажите адрес доставки и подтвердите заказ</p>

            <form id="checkout-form" class="auth-form" novalidate>
                @csrf

                <div class="form-group">
                    <label class="form-label" for="checkout-address">Адрес доставки</label>
                    <input id="checkout-address" name="delivery_address" type="text"
                           class="form-input" placeholder="г. Москва, ул. Ленина, д. 1, кв. 5" required>
                    <span class="form-error" data-field="delivery_address"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="checkout-comment">Комментарий</label>
                    <textarea id="checkout-comment" name="comment" rows="3"
                              class="form-input" placeholder="Необязательно"></textarea>
                    <span class="form-error" data-field="comment"></span>
                </div>

                <div class="checkout-total">
                    <span>К оплате</span>
                    <span id="checkout-total-sum">0 ₽</span>
                </div>

                <button type="submit" class="auth-submit-btn" id="checkout-submit">
                    Подтвердить заказ
                </button>
            </form>
        </div>
    </div>
    {{-- ===== Стили ===== --}}
    <style>
        .cart-wrap {
            max-width: 1280px;
            margin: 0 auto;
            padding: 32px 24px 64px;
        }

        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 24px;
            align-items: start;
        }

        /* ===== Пустое состояние ===== */
        .cart-empty {
            background: #fff;
            border-radius: 12px;
            padding: 60px 24px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        .cart-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #f1f2f6;
            color: #b2bec3;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-empty-icon svg {
            width: 34px;
            height: 34px;
        }

        .cart-empty-title {
            font-size: 20px;
            font-weight: 700;
            color: #2f3640;
            margin-bottom: 6px;
        }

        .cart-empty-text {
            font-size: 14px;
            color: #636e72;
            margin-bottom: 20px;
        }

        /* ===== Список товаров ===== */
        .cart-items {
            background: #fff;
            border-radius: 12px;
            padding: 8px 24px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        .cart-item {
            display: grid;
            grid-template-columns: 96px 1fr auto auto;
            gap: 16px;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #f1f2f6;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 96px;
            height: 96px;
            object-fit: contain;
            border-radius: 8px;
            background: #f5f6fa;
            padding: 6px;
        }

        .cart-item-info {
            min-width: 0;
        }

        .cart-item-category {
            font-size: 11px;
            color: #0984e3;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
        }

        .cart-item-name {
            font-size: 16px;
            font-weight: 700;
            color: #2f3640;
            margin-bottom: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .cart-item-desc {
            font-size: 13px;
            color: #636e72;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .cart-item-price-unit {
            font-size: 12px;
            color: #b2bec3;
            margin-top: 4px;
        }

        /* Счётчик количества */
        .qty {
            display: inline-flex;
            align-items: center;
            border: 1px solid #dfe6e9;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }

        .qty-btn {
            width: 34px;
            height: 36px;
            border: none;
            background: #fff;
            color: #0984e3;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s;
            font-family: inherit;
        }

        .qty-btn:hover {
            background: #e6f1fc;
        }

        .qty-btn:disabled {
            color: #dfe6e9;
            cursor: not-allowed;
            background: #fff;
        }

        .qty-value {
            width: 40px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            border-left: 1px solid #dfe6e9;
            border-right: 1px solid #dfe6e9;
            background: #fafbfc;
        }

        .cart-item-total {
            font-size: 18px;
            font-weight: 700;
            color: #2f3640;
            min-width: 110px;
            text-align: right;
        }

        .cart-item-remove {
            width: 36px;
            height: 36px;
            border: none;
            background: transparent;
            color: #b2bec3;
            font-size: 22px;
            line-height: 1;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s, color 0.2s;
        }

        .cart-item-remove:hover {
            background: #ffe6e6;
            color: #d63031;
        }

        /* ===== Панель итогов ===== */
        .cart-summary {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 88px;
        }

        .summary-title {
            font-size: 16px;
            font-weight: 700;
            color: #2f3640;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f2f6;
            margin-bottom: 14px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            color: #636e72;
            margin-bottom: 10px;
        }

        .summary-row span:last-child {
            color: #2f3640;
            font-weight: 600;
        }

        .summary-row:last-of-type {
            font-size: 16px;
            padding-top: 12px;
            border-top: 1px solid #f1f2f6;
            margin-top: 12px;
        }

        .summary-row:last-of-type span:last-child {
            font-size: 22px;
            font-weight: 700;
        }

        .summary-btn {
            width: 100%;
            margin-top: 16px;
            padding: 12px;
            font-size: 15px;
            align-items: center;
            gap: 8px;
            background-color: #0984e3;
            color: #fff;
            border: none;

            border-radius: 8px;

            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;

        }

        .summary-btn:hover {
            background-color: #0770c4;

        }

        .summary-back {
            display: block;
            text-align: center;
            margin-top: 14px;
            font-size: 13px;
            color: #636e72;
            text-decoration: none;
            transition: color 0.2s;
        }

        .summary-back:hover {
            color: #0984e3;
        }

        /* ===== Toast ===== */
        .cart-toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #545c67;
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            box-shadow: 0 10px 30px rgba(90, 90, 90, 0.2);
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.25s, transform 0.25s;
            z-index: 2000;
            pointer-events: none;
        }

        .cart-toast.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 900px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }

            .cart-summary {
                position: static;
            }

            .cart-item {
                grid-template-columns: 80px 1fr auto;
                gap: 12px;
            }

            .cart-item-image {
                width: 80px;
                height: 80px;
            }

            .cart-item-total {
                grid-column: 2 / 3;
                text-align: left;
                font-size: 16px;
                min-width: 0;
            }

            .cart-item-remove {
                grid-column: 3 / 4;
                grid-row: 1 / 2;
            }
        }

        /* ===== Модалка оформления заказа ===== */
        .checkout-icon {
            width: 56px;
            height: 56px;
            margin: 4px auto 16px;
            border-radius: 50%;
            background: #e6f1fc;
            color: #0984e3;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .checkout-icon svg {
            width: 26px;
            height: 26px;
        }

        .checkout-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 16px;
            background: #f5f6fa;
            border-radius: 8px;
            font-size: 14px;
            color: #636e72;
        }

        .checkout-total span:last-child {
            font-size: 20px;
            font-weight: 700;
            color: #2f3640;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const isAuthed = @json(auth()->check());
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

            const itemsContainer = document.getElementById('cart-items');
            const emptyBlock = document.getElementById('cart-empty');
            const contentBlock = document.getElementById('cart-content');
            const summaryCount = document.getElementById('summary-count');
            const summaryTotal = document.getElementById('summary-total');

            // Серверные данные (для авторизованных)
            const serverItems = @json($items);

            // ===== Формат цены =====
            const formatPrice = (value) => {
                return new Intl.NumberFormat('ru-RU').format(Math.round(value)) + ' ₽';
            };

            // ===== Toast всплывающее уведомление =====
            const toast = document.createElement('div');
            toast.className = 'cart-toast';
            document.body.appendChild(toast);
            const showToast = (message) => {
                toast.textContent = message;
                toast.classList.add('is-visible');
                clearTimeout(toast._t);
                toast._t = setTimeout(() => toast.classList.remove('is-visible'), 2200);
            };

            // ===== Работа с localStorage (для гостя) =====
            const getGuestCart = () => {
                const ids = [];
                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i);
                    if (key.startsWith('id-product-')) {
                        ids.push(parseInt(localStorage.getItem(key), 10));
                    }
                }
                return ids;
            };

            // ===== Загрузка товаров с сервера по ID (для гостя) =====
            // Используем существующий эндпоинт каталога, чтобы не плодить роуты.
            // Если у вас есть API /api/products/{id} — замените на него.
            const fetchProductsByIds = async (ids) => {
                if (!ids.length) return [];

                // Параллельно тянем страницы каталога и фильтруем у себя.
                // Оптимальнее: сделайте отдельный endpoint GET /api/products?ids[]=...
                const res = await fetch('{{ route('products.index') }}', {
                    headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
                });
                if (!res.ok) return [];
                // Если каталог отдаёт HTML — этот путь не сработает. Ниже fallback.
                return [];
            };

            // ===== Рендер одной позиции =====
            const renderItem = (item) => {
                const p = item.product;
                const qty = item.quantity;
                const price = item.price;
                const img = p.image_url || (p.images?.[0]?.path) || '/storage/images/no-image.jpg';

                const el = document.createElement('div');
                el.className = 'cart-item';
                el.dataset.productId = p.id;
                el.dataset.price = price;

                el.innerHTML = `
            <img class="cart-item-image" src="${img}" alt="">
            <div class="cart-item-info">
                <div class="cart-item-category">${p.category?.name ?? 'Без категории'}</div>
                <div class="cart-item-name">${p.name}</div>
                <div class="cart-item-desc">${p.description ?? ''}</div>
                <div class="cart-item-price-unit">${formatPrice(price)} / шт.</div>
            </div>
            <div class="qty">
                <button type="button" class="qty-btn" data-action="dec" ${qty <= 1 ? 'disabled' : ''}>−</button>
                <div class="qty-value">${qty}</div>
                <button type="button" class="qty-btn" data-action="inc">+</button>
            </div>
            <div class="cart-item-total">${formatPrice(price * qty)}</div>
            <button type="button" class="cart-item-remove" title="Удалить" data-action="remove">&times;</button>
        `;
                return el;
            };

            // ===== Пересчёт итогов =====
            const recalc = () => {
                const rows = itemsContainer.querySelectorAll('.cart-item');
                let count = 0;
                let total = 0;
                rows.forEach(row => {
                    const qty = parseInt(row.querySelector('.qty-value').textContent, 10);
                    const price = parseFloat(row.dataset.price);
                    count += qty;
                    total += price * qty;
                });

                summaryCount.textContent = count;
                summaryTotal.textContent = formatPrice(total);

                if (!rows.length) {
                    contentBlock.style.display = 'none';
                    emptyBlock.style.display = 'block';
                } else {
                    contentBlock.style.display = 'grid';
                    emptyBlock.style.display = 'none';
                }
            };

            // ===== Отрисовка списка =====
            const renderAll = (items) => {
                itemsContainer.innerHTML = '';
                items.forEach(item => itemsContainer.appendChild(renderItem(item)));
                recalc();
            };

            // ===== Обработчики кнопок =====
            itemsContainer.addEventListener('click', async (e) => {
                const btn = e.target.closest('[data-action]');
                if (!btn) return;

                const row = btn.closest('.cart-item');
                const productId = parseInt(row.dataset.productId, 10);
                const action = btn.dataset.action;
                const qtyEl = row.querySelector('.qty-value');
                const qty = parseInt(qtyEl.textContent, 10);

                if (action === 'inc' || action === 'dec') {
                    const newQty = action === 'inc' ? qty + 1 : qty - 1;
                    if (newQty < 1) return;

                    qtyEl.textContent = newQty;
                    row.querySelector('.qty-btn[data-action="dec"]').disabled = newQty <= 1;

                    // Для авторизованного — сохраняем на сервере (через sync или отдельный endpoint).
                    if (isAuthed) {
                        // Простейший вариант: обновляем всю корзину через sync.
                        await syncServerCart();
                    } else {
                        // Гость: для изменения количества храним JSON в localStorage
                        // (в вашем текущем формате localStorage — просто список id).
                        // Если нужно именно количество — переделайте формат хранения.
                    }

                    const price = parseFloat(row.dataset.price);
                    row.querySelector('.cart-item-total').textContent = formatPrice(price * newQty);
                    recalc();
                    await window.refreshCartBadge();
                    return;
                }

                if (action === 'remove') {
                    row.style.opacity = '0.4';
                    row.style.pointerEvents = 'none';

                    if (isAuthed) {
                        try {
                            await fetch('{{ route('cart.remove') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({product_id: productId}),
                            });
                        } catch (err) {
                            console.error(err);
                        }
                    } else {
                        localStorage.removeItem('id-product-' + productId);
                    }

                    row.remove();
                    recalc();
                    showToast('Товар удалён из корзины');
                    window.refreshCartBadge();

                }
            });

            // ===== Сохранение корзины на сервере (для авторизованного) =====
            const syncServerCart = async () => {
                const ids = [];
                itemsContainer.querySelectorAll('.cart-item').forEach(row => {
                    const id = parseInt(row.dataset.productId, 10);
                    const qty = parseInt(row.querySelector('.qty-value').textContent, 10);
                    for (let i = 0; i < qty; i++) ids.push(id);
                });

                try {
                    await fetch('{{ route('cart.sync') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({products: ids}),
                    });
                } catch (err) {
                    console.error(err);
                }
            };

            // ===== Инициализация =====
            const init = async () => {
                if (isAuthed && serverItems.length) {
                    renderAll(serverItems);
                    return;
                }

                // Гость: подтягиваем данные с сервера по ID из localStorage
                const ids = getGuestCart();
                if (!ids.length) {
                    recalc(); // покажет пустое состояние
                    return;
                }

                // Тянем данные через существующий index-эндпоинт корзины —
                // он возвращает только ID. Нам нужны полные данные товаров.
                // Самый простой способ — попросить сервер отдать полные данные:
                try {
                    const res = await fetch('{{ route('cart.guest.details') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({products: ids}),
                    });
                    if (!res.ok) throw new Error('bad status');
                    const data = await res.json();
                    renderAll(data.items || []);
                } catch (err) {
                    console.error(err);
                    recalc();
                }
            };

// ===== Кнопка «Оформить заказ» =====
            document.getElementById('checkout-btn')?.addEventListener('click', () => {
                if (!isAuthed) {
                    showToast('Войдите, чтобы оформить заказ');
                    document.querySelector('[data-modal="login-modal"]')?.click();
                    return;
                }

                if (!itemsContainer.querySelector('.cart-item')) {
                    showToast('Корзина пуста');
                    return;
                }

                openCheckoutModal();
            });

// ===== Модалка оформления заказа =====
            const checkoutModal = document.getElementById('checkout-modal');
            const checkoutForm = document.getElementById('checkout-form');
            const checkoutSubmit = document.getElementById('checkout-submit');
            const checkoutTotal = document.getElementById('checkout-total-sum');

            const openCheckoutModal = () => {
                if (!checkoutModal) return;

                // Подставляем актуальную сумму из корзины
                const rows = itemsContainer.querySelectorAll('.cart-item');
                let total = 0;
                rows.forEach(row => {
                    const qty = parseInt(row.querySelector('.qty-value').textContent, 10);
                    const price = parseFloat(row.dataset.price);
                    total += price * qty;
                });
                checkoutTotal.textContent = formatPrice(total);

                // Сброс ошибок
                checkoutForm.querySelectorAll('.form-error').forEach(el => el.textContent = '');
                checkoutForm.querySelectorAll('.form-input').forEach(el => el.classList.remove('is-invalid'));

                checkoutModal.classList.add('is-open');
                checkoutModal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');

                setTimeout(() => document.getElementById('checkout-address')?.focus(), 100);
            };

            const closeCheckoutModal = () => {
                if (!checkoutModal) return;
                checkoutModal.classList.remove('is-open');
                checkoutModal.setAttribute('aria-hidden', 'true');
                // Закрываем блокировку скролла только если не открыта другая модалка
                if (!document.querySelector('.modal-overlay.is-open')) {
                    document.body.classList.remove('modal-open');
                }
            };

// Закрытие по фону, крестику, Esc
            checkoutModal?.addEventListener('click', (e) => {
                if (e.target === checkoutModal) closeCheckoutModal();
            });
            checkoutModal?.querySelector('.modal-close')?.addEventListener('click', closeCheckoutModal);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && checkoutModal?.classList.contains('is-open')) {
                    closeCheckoutModal();
                }
            });

// Кнопка «Оформить заказ» — открывает модалку
            document.getElementById('checkout-btn')?.addEventListener('click', () => {
                if (!isAuthed) {
                    showToast('Войдите, чтобы оформить заказ');
                    document.querySelector('[data-modal="login-modal"]')?.click();
                    return;
                }

                if (!itemsContainer.querySelector('.cart-item')) {
                    showToast('Корзина пуста');
                    return;
                }

                openCheckoutModal();
            });

// Отправка формы заказа
            checkoutForm?.addEventListener('submit', async (e) => {
                e.preventDefault();

                const original = checkoutSubmit.textContent;
                checkoutSubmit.disabled = true;
                checkoutSubmit.textContent = 'Оформляем...';

                checkoutForm.querySelectorAll('.form-error').forEach(el => el.textContent = '');
                checkoutForm.querySelectorAll('.form-input').forEach(el => el.classList.remove('is-invalid'));

                try {
                    const res = await fetch('{{ route('orders.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(checkoutForm),
                    });

                    const data = await res.json().catch(() => ({}));

                    if (res.ok && data.success) {
                        // Очищаем localStorage на всякий случай (если гость успел побывать)
                        Object.keys(localStorage).forEach(key => {
                            if (key.startsWith('id-product-')) localStorage.removeItem(key);
                        });

                        // Уходим на страницу заказа
                        window.location.href = `/orders/${data.order_id}`;
                        return;
                    }

                    if (res.status === 422 && data.errors) {
                        Object.entries(data.errors).forEach(([field, messages]) => {
                            const input = checkoutForm.querySelector(`[name="${field}"]`);
                            const errorEl = checkoutForm.querySelector(`.form-error[data-field="${field}"]`);
                            if (input) input.classList.add('is-invalid');
                            if (errorEl) errorEl.textContent = messages[0];
                        });
                    } else {
                        showToast(data.message || 'Не удалось оформить заказ');
                    }
                } catch (err) {
                    console.error(err);
                    showToast('Ошибка сети. Проверьте подключение');
                } finally {
                    checkoutSubmit.disabled = false;
                    checkoutSubmit.textContent = original;
                }
            });
            init();
            window.refreshCartBadge();
            init();
            window.refreshCartBadge();

        });
    </script>
@endsection
