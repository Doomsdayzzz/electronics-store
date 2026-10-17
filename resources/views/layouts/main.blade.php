<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ЦифроStore</title>
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

        .cart-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            background: #fff;
            color: #0984e3;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            margin-left: 4px;
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

        .product-id {
            font-size: 11px;
            color: #636e72;
            margin-bottom: 6px;
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

        .in-cart {
            width: 100%;
            background-color: rgba(255, 255, 255, 0);
            color: #fff;
            border: 1px solid #00b894;
            padding: 10px;
            border-radius: 8px;
            color: #007a5e;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
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

        /* ===== Modal ===== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(47, 54, 64, 0.55);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            z-index: 1000;
        }

        .modal-overlay.is-open {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            position: relative;
            width: 100%;
            max-width: 440px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            background-color: #ffffff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            transform: translateY(16px) scale(0.98);
            transition: transform 0.25s ease;
        }

        .modal-overlay.is-open .modal {
            transform: translateY(0) scale(1);
        }

        .modal-close {
            position: absolute;
            top: 12px;
            right: 14px;
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;
            font-size: 26px;
            line-height: 1;
            color: #b2bec3;
            cursor: pointer;
            border-radius: 8px;
            transition: background-color 0.2s, color 0.2s;
        }

        .modal-close:hover {
            background-color: #f1f2f6;
            color: #2f3640;
        }

        /* Общие стили для форм — как в register.blade */
        .auth-title {
            font-size: 22px;
            font-weight: 700;
            color: #2f3640;
            text-align: center;
            margin-bottom: 6px;
        }

        .auth-subtitle {
            font-size: 14px;
            color: #636e72;
            text-align: center;
            margin-bottom: 24px;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #636e72;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .form-input {
            width: 100%;
            padding: 11px 14px;
            font-size: 14px;
            font-family: inherit;
            color: #2f3640;
            background-color: #f5f6fa;
            border: 1px solid #dfe6e9;
            border-radius: 8px;
            outline: none;
            transition: border-color 0.2s, background-color 0.2s, box-shadow 0.2s;
        }

        .form-input::placeholder {
            color: #b2bec3;
        }

        .form-input:focus {
            background-color: #ffffff;
            border-color: #0984e3;
            box-shadow: 0 0 0 3px rgba(9, 132, 227, 0.12);
        }

        .form-input.is-invalid {
            border-color: #d63031;
            background-color: #fff5f5;
        }

        .form-error {
            font-size: 12px;
            color: #d63031;
            min-height: 0;
        }

        .auth-submit-btn {
            width: 100%;
            background-color: #00b894;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .auth-submit-btn:hover {
            background-color: #009e7f;
        }

        .auth-submit-btn:disabled {
            background-color: #b2bec3;
            cursor: not-allowed;
        }

        .auth-footer {
            text-align: center;
            font-size: 14px;
            color: #636e72;
        }

        .auth-footer a {
            color: #0984e3;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        body.modal-open {
            overflow: hidden;
        }
    </style>
</head>
<body>

<header>
    <div class="header-inner">
        <div class="header-left">

            <a href="{{ route('products.index') }}" class="logo">Цифро<span>Store</span></a>
            <div class="auth-links">
                @if(!auth()->check())

                    <a href="#" class="auth-trigger" data-modal="login-modal">Войти</a>
                    <div class="auth-divider"></div>
                    <a href="#" class="auth-trigger" data-modal="register-modal">Регистрация</a>
                @else
                    <a href="#"><span>{{auth()->user()->name}}</span></a>
                    @if(!(auth()->user()->is_admin))
                        <div class="auth-divider"></div>
                        <a href="{{ route('orders.my') }}">Мои заказы</a>
                    @endif
                    @if(auth()->user()->is_admin)
                        <div class="auth-divider"></div>
                        <a href="{{route('admin.dashboard')}}">Админка</a>
                        <a href="{{ route('admin.orders.index') }}">Все заказы</a>
                    @endif
                    <a href="{{route('logout')}}">Выйти</a>

                @endif
            </div>
        </div>
        <div class="header-right">
            <a href="{{route('cart.index')}}" style="text-decoration: none">
                <button class="cart-btn" type="button" title="Корзина">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Корзина
                    <span class="cart-badge" id="cart-badge" style="display:none">0</span>
                </button>
            </a>
        </div>
    </div>
</header>
@yield('content')
{{-- ===== Login Modal ===== --}}
<div class="modal-overlay" id="login-modal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="login-title">
        <button type="button" class="modal-close" aria-label="Закрыть">&times;</button>
        <h2 class="auth-title" id="login-title">Вход</h2>
        <p class="auth-subtitle">Войдите, чтобы продолжить покупки</p>

        <form method="POST" action="{{route('login')}}" class="auth-form ajax-form" novalidate>
            @csrf
            <input type="hidden" name="cart_products" id="login-cart-products" value="">
            <div class="form-group">
                <label class="form-label" for="login-email">Email</label>
                <input id="login-email" type="email" name="email" class="form-input" required>
                <span class="form-error" data-field="email"></span>
            </div>
            <div class="form-group">
                <label class="form-label" for="login-password">Пароль</label>
                <input id="login-password" type="password" name="password" class="form-input" required>
                <span class="form-error" data-field="password"></span>
            </div>
            <button type="submit" class="auth-submit-btn">Войти</button>
            <div class="auth-footer">
                Нет аккаунта?
                <a href="#" class="auth-switch" data-modal="register-modal">Зарегистрироваться</a>
            </div>
        </form>
    </div>
</div>

{{-- ===== Register Modal ===== --}}
<div class="modal-overlay" id="register-modal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="register-title">
        <button type="button" class="modal-close" aria-label="Закрыть">&times;</button>
        <h2 class="auth-title" id="register-title">Регистрация</h2>
        <p class="auth-subtitle">Создайте аккаунт, чтобы оформлять заказы</p>

        <form method="POST" action="{{ route('register') }}" class="auth-form ajax-form" novalidate>
            @csrf
            <div class="form-group">
                <label class="form-label" for="reg-name">Имя</label>
                <input id="reg-name" type="text" name="name" class="form-input" required autofocus>
                <span class="form-error" data-field="name"></span>
            </div>
            <div class="form-group">
                <label class="form-label" for="reg-email">Email</label>
                <input id="reg-email" type="email" name="email" class="form-input" required>
                <span class="form-error" data-field="email"></span>
            </div>
            <div class="form-group">
                <label class="form-label" for="reg-phone">Телефон</label>
                <input id="reg-phone" type="tel" name="phone" class="form-input" required>
                <span class="form-error" data-field="phone"></span>
            </div>
            <div class="form-group">
                <label class="form-label" for="reg-password">Пароль</label>
                <input id="reg-password" type="password" name="password" class="form-input" required>
                <span class="form-error" data-field="password"></span>
            </div>
            <div class="form-group">
                <label class="form-label" for="reg-password-confirm">Подтверждение пароля</label>
                <input id="reg-password-confirm" type="password" name="password_confirmation" class="form-input"
                       required>
            </div>
            <button type="submit" class="auth-submit-btn">Зарегистрироваться</button>
            <div class="auth-footer">
                Уже есть аккаунт?
                <a href="#" class="auth-switch" data-modal="login-modal">Войти</a>
            </div>
        </form>
    </div>
</div>


<script>
    //загрузка значка кол-ва товаров корзины ГЛОБАЛЬНО
    window.refreshCartBadge = async function () {
        const badge = document.getElementById('cart-badge');
        if (!badge) return;

        const isAuthed = {{ auth()->check() ? 'true' : 'false' }};
        let count = 0;

        if (isAuthed) {
            try {
                const res = await fetch('{{ route('cart.getById') }}', {
                    headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
                });
                const data = await res.json();
                count = (data.product_ids || []).length;
            } catch (e) {
                console.error('Не удалось получить корзину', e);
            }
        } else {
            for (let i = 0; i < localStorage.length; i++) {
                if (localStorage.key(i).startsWith('id-product-')) count++;
            }
        }

        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-flex';
        } else {
            badge.textContent = '0';
            badge.style.display = 'none';
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        const overlays = document.querySelectorAll('.modal-overlay');

        const openModal = (id) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.add('is-open');
            el.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            const firstInput = el.querySelector('input');
            if (firstInput) setTimeout(() => firstInput.focus(), 100);
        };

        const closeModal = (el) => {
            el.classList.remove('is-open');
            el.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        };

        // Открытие
        document.querySelectorAll('[data-modal]').forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                // Закрыть все открытые, чтобы не было наложения
                overlays.forEach(o => closeModal(o));
                openModal(trigger.dataset.modal);
            });
        });

        // Закрытие по крестику и клику по фону
        overlays.forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) closeModal(overlay);
            });
            overlay.querySelector('.modal-close')?.addEventListener('click', () => closeModal(overlay));
        });

        // Закрытие по Esc
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                overlays.forEach(o => {
                    if (o.classList.contains('is-open')) closeModal(o);
                });
            }
        });

        // ===== AJAX-отправка форм =====
        document.querySelectorAll('.ajax-form').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitBtn = form.querySelector('.auth-submit-btn');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Подождите...';

                // Сброс предыдущих ошибок
                form.querySelectorAll('.form-error').forEach(el => el.textContent = '');
                form.querySelectorAll('.form-input').forEach(el => el.classList.remove('is-invalid'));

                // Для формы логина — добавляем корзину из localStorage
                const cartProductsInput = form.querySelector('#login-cart-products');
                if (cartProductsInput) {
                    const cartIds = [];
                    for (let i = 0; i < localStorage.length; i++) {
                        const key = localStorage.key(i);
                        if (key.startsWith('id-product-')) {
                            cartIds.push(localStorage.getItem(key));
                        }
                    }
                    cartProductsInput.value = JSON.stringify(cartIds);
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                                || form.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(form),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (response.ok) {
                        // Успех — очищаем localStorage и перезагружаем страницу
                        if (form.id === 'login-form' || cartProductsInput) {
                            // Очищаем localStorage после успешного входа
                            Object.keys(localStorage).forEach(key => {
                                if (key.startsWith('id-product-')) {
                                    localStorage.removeItem(key);
                                }
                            });
                        }
                        window.location.reload();
                        return;
                    }

                    // Ошибки валидации (422)
                    if (response.status === 422 && data.errors) {
                        Object.entries(data.errors).forEach(([field, messages]) => {
                            const input = form.querySelector(`[name="${field}"]`);
                            const errorEl = form.querySelector(`.form-error[data-field="${field}"]`);
                            if (input) input.classList.add('is-invalid');
                            if (errorEl) errorEl.textContent = messages[0];
                        });
                    } else {
                        console.log(data);
                        alert(data.message || 'Произошла ошибка. Попробуйте ещё раз.');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Ошибка сети. Проверьте подключение.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        });
        window.refreshCartBadge();


    });


</script>

</body>
</html>
