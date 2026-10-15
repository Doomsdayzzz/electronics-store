@extends('layouts.main')

@section('content')
    <div class="dashboard-wrap">
        <h1 class="page-title">Панель администратора</h1>

        @if(session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif

        {{-- ===== Статистика ===== --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $products->total() }}</div>
                <div class="stat-label">Товаров (по фильтру)</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $users->total() }}</div>
                <div class="stat-label">Пользователей</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $categories->count() }}</div>
                <div class="stat-label">Категорий</div>
            </div>
        </div>

        {{-- ===== Товары ===== --}}
        <section class="panel">
            <div class="panel-header">
                <h2 class="panel-title">Товары</h2>
                <button type="button" class="btn btn-primary" id="btn-add-product">
                    + Добавить товар
                </button>
            </div>

            {{-- Панель поиска/фильтров --}}
            <form method="GET" action="{{ route('admin.dashboard') }}" class="filters-bar" id="filters-form">
                {{-- Сброс users_page, чтобы не «прыгать» на ту же страницу пользователей --}}
                @if(request('users_page'))
                    <input type="hidden" name="users_page" value="{{ request('users_page') }}">
                @endif

                <div class="filter-search">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" class="filter-search-icon">
                        <circle cx="11" cy="11" r="7"/>
                        <path stroke-linecap="round" d="M21 21l-4.3-4.3"/>
                    </svg>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Поиск по названию или описанию..."
                        class="form-input filter-search-input"
                    >
                </div>

                <select name="category_id" class="form-input filter-select">
                    <option value="">Все категории</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            @selected(request('category_id') == $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>

                <select name="stock" class="form-input filter-select">
                    <option value="">Наличие</option>
                    <option value="1" @selected(request('stock') === '1')>В наличии</option>
                    <option value="0" @selected(request('stock') === '0')>Нет в наличии</option>
                </select>

                <button type="submit" class="btn btn-primary">Найти</button>

                @if(request()->hasAny(['search', 'category_id', 'stock']))
                    <a href="{{ route('admin.dashboard', request('users_page') ? ['users_page' => request('users_page')] : []) }}"
                       class="btn btn-ghost">Сбросить</a>
                @endif
            </form>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th style="width:60px">ID</th>
                        <th style="width:70px">Фото</th>
                        <th>Название</th>
                        <th>Категория</th>
                        <th>Цена</th>
                        <th>Скидка</th>
                        <th>Склад</th>
                        <th style="width:180px">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>#{{ $product->id }}</td>
                            <td>
                                @php $img = $product->images->first(); @endphp
                                <img class="thumb"
                                     src="{{ $img?->path ?? asset('/storage/images/no-image.jpg') }}"
                                     alt="">
                            </td>
                            <td>
                                <div class="cell-title">{{ $product->name }}</div>
                                <div
                                    class="cell-sub">{{ \Illuminate\Support\Str::limit($product->description, 60) }}</div>
                            </td>
                            <td>{{ $product->category->name ?? '—' }}</td>
                            <td><b>{{ number_format($product->price, 0, '.', ' ') }} ₽</b></td>
                            <td>
                                @if($product->old_price && $product->old_price > $product->price)
                                    <span class="badge badge-warn">{{ number_format($product->old_price, 0, '.', ' ') }} ₽</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($product->stock)
                                    <span class="badge badge-ok">{{$product->stock}} шт</span>
                                @else
                                    <span class="badge badge-off">Нет</span>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions">
                                    <button type="button"
                                            class="btn btn-sm btn-outline btn-edit-product"
                                            data-product="{{ json_encode($product->only(['id','name','description','category_id','price','old_price','stock'])) }}">
                                        Изменить
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-danger btn-delete-product"
                                            data-id="{{ $product->id }}"
                                            data-name="{{ $product->name }}"
                                            data-action="{{ route('products.destroy', $product) }}">
                                        Удалить
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-row">
                                @if(request()->hasAny(['search', 'category_id', 'stock']))
                                    По заданным условиям ничего не найдено
                                @else
                                    Товаров пока нет
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">{{ $products->links() }}</div>
        </section>

        {{-- ===== Пользователи ===== --}}
        <section class="panel">
            <div class="panel-header">
                <h2 class="panel-title">Пользователи</h2>
                <span class="panel-subtitle">Всего: {{ $users->total() }}</span>
            </div>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th style="width:60px">ID</th>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th>Роль</th>
                        <th>Регистрация</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>#{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone }}</td>
                            <td>
                                @if($user->is_admin)
                                    <span class="badge badge-info">Админ</span>
                                @else
                                    <span class="badge">Пользователь</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at?->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">{{ $users->links() }}</div>
        </section>
    </div>

    {{-- ===== Модалка товара ===== --}}
    <div class="modal-overlay" id="product-modal" aria-hidden="true">
        <div class="modal" role="dialog" aria-modal="true">
            <button type="button" class="modal-close" aria-label="Закрыть">&times;</button>
            <h2 class="auth-title" id="product-modal-title">Новый товар</h2>
            <p class="auth-subtitle">Заполните карточку товара</p>

            <form id="product-form" class="auth-form" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="_method" value="POST" id="product-method">

                <div class="form-group">
                    <label class="form-label" for="p-name">Название</label>
                    <input id="p-name" name="name" type="text" class="form-input" required>
                    <span class="form-error" data-field="name"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="p-description">Описание</label>
                    <textarea id="p-description" name="description" rows="3" class="form-input"></textarea>
                    <span class="form-error" data-field="description"></span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="p-category">Категория</label>
                        <select id="p-category" name="category_id" class="form-input" required>
                            <option value="">— Выберите —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <span class="form-error" data-field="category_id"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="p-stock">На складе</label>
                        <input type="text" id="p-stock" name="stock" class="form-input" min="0" required>
                        <span class="form-error" data-field="stock"></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="p-price">Цена, ₽</label>
                        <input id="p-price" name="price" type="number" step="0.01" min="0" class="form-input" required>
                        <span class="form-error" data-field="price"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="p-old-price">Старая цена, ₽</label>
                        <input id="p-old-price" name="old_price" type="number" step="0.01" min="0" class="form-input">
                        <span class="form-error" data-field="old_price"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="p-image">Изображение</label>
                    <input id="p-image" name="image" type="file" accept="image/*" class="form-input">
                    <span class="form-error" data-field="image"></span>
                </div>

                <button type="submit" class="auth-submit-btn" id="product-submit">Сохранить</button>
            </form>
        </div>
    </div>

    {{-- ===== Модалка подтверждения удаления ===== --}}
    <div class="modal-overlay" id="delete-modal" aria-hidden="true">
        <div class="modal modal-sm" role="dialog" aria-modal="true">
            <button type="button" class="modal-close" aria-label="Закрыть">&times;</button>

            <div class="confirm-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18"/>
                    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6"/>
                </svg>
            </div>

            <h2 class="auth-title">Удалить товар?</h2>
            <p class="auth-subtitle">
                Вы собираетесь удалить <b id="delete-product-name">товар</b>.<br>
                Это действие нельзя отменить.
            </p>

            <form id="delete-form" method="POST" class="confirm-actions">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-ghost" id="delete-cancel">Отмена</button>
                <button type="submit" class="btn btn-danger-solid" id="delete-confirm">Удалить</button>
            </form>
        </div>
    </div>

    {{-- ===== Стили ===== --}}
    <style>
        .dashboard-wrap {
            max-width: 1280px;
            margin: 0 auto;
            padding: 32px 24px 64px;
        }

        .flash-success {
            background: #e8f8f3;
            color: #007a5e;
            border: 1px solid #b8ecdb;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        /* ===== Stats ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #0984e3;
            line-height: 1;
        }

        .stat-label {
            font-size: 13px;
            color: #636e72;
            margin-top: 6px;
        }

        /* ===== Panels ===== */
        .panel {
            background: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f1f2f6;
            margin-bottom: 16px;
        }

        .panel-title {
            font-size: 18px;
            font-weight: 700;
            color: #2f3640;
        }

        .panel-subtitle {
            font-size: 13px;
            color: #636e72;
        }

        /* ===== Filters bar ===== */
        .filters-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-bottom: 16px;
        }

        .filter-search {
            position: relative;
            flex: 1 1 260px;
            min-width: 200px;
        }

        .filter-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #b2bec3;
            pointer-events: none;
        }

        .filter-search-input {
            padding-left: 38px !important;
        }

        .filter-select {
            flex: 0 1 190px;
            min-width: 150px;
            cursor: pointer;
        }

        .filters-bar .btn {
            flex: 0 0 auto;
        }

        /* ===== Table ===== */
        .table-wrap {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .data-table th {
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #636e72;
            padding: 10px 12px;
            border-bottom: 1px solid #f1f2f6;
            white-space: nowrap;
        }

        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #f1f2f6;
            vertical-align: middle;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover td {
            background: #fafbfc;
        }

        .thumb {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 6px;
            background: #f5f6fa;
        }

        .cell-title {
            font-weight: 600;
            color: #2f3640;
        }

        .cell-sub {
            font-size: 12px;
            color: #b2bec3;
            margin-top: 2px;
        }

        .muted {
            color: #b2bec3;
        }

        .empty-row {
            text-align: center;
            padding: 40px 0;
            color: #b2bec3;
        }

        /* ===== Badges ===== */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            background: #f1f2f6;
            color: #636e72;
        }

        .badge-ok {
            background: #e8f8f3;
            color: #009e7f;
        }

        .badge-off {
            background: #fdecec;
            color: #d63031;
        }

        .badge-warn {
            background: #fff4e0;
            color: #b06d00;
        }

        .badge-info {
            background: #e6f1fc;
            color: #0984e3;
        }

        /* ===== Buttons ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 16px;
            cursor: pointer;
            transition: background-color .2s, color .2s, border-color .2s;
            font-family: inherit;
            text-decoration: none;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 13px;
        }

        .btn-primary {
            background: #00b894;
            color: #fff;
        }

        .btn-primary:hover {
            background: #009e7f;
        }

        .btn-outline {
            background: #fff;
            color: #0984e3;
            border: 1px solid #0984e3;
        }

        .btn-outline:hover {
            background: #e6f1fc;
        }

        .btn-danger {
            background: #ffe6e6;
            color: #d63031;
        }

        .btn-danger:hover {
            background: #d63031;
            color: #fff;
        }

        .btn-danger-solid {
            background: #d63031;
            color: #fff;
        }

        .btn-danger-solid:hover {
            background: #b02525;
        }

        .btn-ghost {
            background: #f5f6fa;
            color: #636e72;
        }

        .btn-ghost:hover {
            background: #e6e9ef;
            color: #2f3640;
        }

        .row-actions {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        /* ===== Modal ===== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(47, 54, 64, 0.55);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            transition: opacity .2s ease, visibility .2s ease;
            z-index: 1000;
        }

        .modal-overlay.is-open {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            position: relative;
            width: 100%;
            max-width: 520px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            background: #fff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            transform: translateY(16px) scale(.98);
            transition: transform .25s ease;
        }

        .modal-sm {
            max-width: 420px;
            padding: 28px 28px 24px;
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
            transition: background-color .2s, color .2s;
        }

        .modal-close:hover {
            background: #f1f2f6;
            color: #2f3640;
        }

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
            line-height: 1.5;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
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
            letter-spacing: .3px;
        }

        .form-input {
            width: 100%;
            padding: 11px 14px;
            font-size: 14px;
            font-family: inherit;
            color: #2f3640;
            background: #f5f6fa;
            border: 1px solid #dfe6e9;
            border-radius: 8px;
            outline: none;
            transition: border-color .2s, background-color .2s, box-shadow .2s;
            resize: vertical;
        }

        .form-input:focus {
            background: #fff;
            border-color: #0984e3;
            box-shadow: 0 0 0 3px rgba(9, 132, 227, .12);
        }

        .form-input.is-invalid {
            border-color: #d63031;
            background: #fff5f5;
        }

        .form-error {
            font-size: 12px;
            color: #d63031;
            min-height: 0;
        }

        .auth-submit-btn {
            width: 100%;
            background: #00b894;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color .2s;
        }

        .auth-submit-btn:hover {
            background: #009e7f;
        }

        .auth-submit-btn:disabled {
            background: #b2bec3;
            cursor: not-allowed;
        }

        /* ===== Confirm ===== */
        .confirm-icon {
            width: 56px;
            height: 56px;
            margin: 4px auto 16px;
            border-radius: 50%;
            background: #fdecec;
            color: #d63031;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .confirm-icon svg {
            width: 26px;
            height: 26px;
        }

        .confirm-actions {
            display: flex;
            gap: 10px;
            margin-top: 8px;
        }

        .confirm-actions .btn {
            flex: 1;
        }

        body.modal-open {
            overflow: hidden;
        }

        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .panel {
                padding: 16px;
            }

            .filter-search {
                flex-basis: 100%;
            }

            .filter-select {
                flex: 1 1 100%;
            }

            .filters-bar .btn {
                flex: 1 1 auto;
            }
        }
    </style>

    {{-- ===== JS ===== --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ===== Утилиты для модалок =====
            const openModal = (el) => {
                el.classList.add('is-open');
                el.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
            };
            const closeModal = (el) => {
                el.classList.remove('is-open');
                el.setAttribute('aria-hidden', 'true');
                if (!document.querySelector('.modal-overlay.is-open')) {
                    document.body.classList.remove('modal-open');
                }
            };

            // Закрытие по клику на фон/крестик/Esc для всех модалок
            document.querySelectorAll('.modal-overlay').forEach(overlay => {
                overlay.addEventListener('click', e => {
                    if (e.target === overlay) closeModal(overlay);
                });
                overlay.querySelector('.modal-close')?.addEventListener('click', () => closeModal(overlay));
            });
            document.addEventListener('keydown', e => {
                if (e.key !== 'Escape') return;
                document.querySelectorAll('.modal-overlay.is-open').forEach(closeModal);
            });

            // ===== Модалка товара (создание / редактирование) =====
            const productModal = document.getElementById('product-modal');
            const productForm = document.getElementById('product-form');
            const productTitle = document.getElementById('product-modal-title');
            const productMeth = document.getElementById('product-method');
            const productSubmit = document.getElementById('product-submit');

            const resetProductForm = () => {
                productForm.reset();
                productForm.querySelectorAll('.form-error').forEach(el => el.textContent = '');
                productForm.querySelectorAll('.form-input').forEach(el => el.classList.remove('is-invalid'));
            };

            document.getElementById('btn-add-product').addEventListener('click', () => {
                resetProductForm();
                productTitle.textContent = 'Новый товар';
                productMeth.value = 'POST';
                productForm.action = '{{ route('products.store') }}';
                productSubmit.textContent = 'Создать';
                openModal(productModal);
            });

            document.querySelectorAll('.btn-edit-product').forEach(btn => {
                btn.addEventListener('click', () => {
                    resetProductForm();
                    const p = JSON.parse(btn.dataset.product);

                    productTitle.textContent = 'Редактирование товара';
                    productMeth.value = 'PUT';
                    productForm.action = `/products/${p.id}`;
                    productSubmit.textContent = 'Сохранить';

                    productForm.name.value = p.name ?? '';
                    productForm.description.value = p.description ?? '';
                    productForm.category_id.value = p.category_id ?? '';
                    productForm.price.value = p.price ?? '';
                    productForm.old_price.value = p.old_price ?? '';
                    productForm.stock.value = p.stock;

                    openModal(productModal);
                });
            });

            // AJAX-отправка формы товара
            productForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const original = productSubmit.textContent;
                productSubmit.disabled = true;
                productSubmit.textContent = 'Сохранение...';

                productForm.querySelectorAll('.form-error').forEach(el => el.textContent = '');
                productForm.querySelectorAll('.form-input').forEach(el => el.classList.remove('is-invalid'));

                try {
                    const res = await fetch(productForm.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                                || productForm.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(productForm),
                    });
                    console.log(productForm.stock.value);
                    const data = await res.json().catch(() => ({}));

                    if (res.ok) {
                        window.location.reload();
                        return;
                    }

                    if (res.status === 422 && data.errors) {
                        Object.entries(data.errors).forEach(([field, messages]) => {
                            const input = productForm.querySelector(`[name="${field}"]`);
                            const errorEl = productForm.querySelector(`.form-error[data-field="${field}"]`);
                            if (input) input.classList.add('is-invalid');
                            if (errorEl) errorEl.textContent = messages[0];
                        });
                    } else {
                        alert(data.message || 'Ошибка сохранения');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Ошибка сети');
                } finally {
                    productSubmit.disabled = false;
                    productSubmit.textContent = original;
                }
            });

            // ===== Модалка подтверждения удаления =====
            const deleteModal = document.getElementById('delete-modal');
            const deleteForm = document.getElementById('delete-form');
            const deleteName = document.getElementById('delete-product-name');
            const deleteCancel = document.getElementById('delete-cancel');

            document.querySelectorAll('.btn-delete-product').forEach(btn => {
                btn.addEventListener('click', () => {
                    deleteForm.action = btn.dataset.action;
                    deleteName.textContent = '«' + btn.dataset.name + '»';
                    openModal(deleteModal);
                });
            });

            deleteCancel.addEventListener('click', () => closeModal(deleteModal));

            // ===== Автосубмит фильтров при смене select =====
            // (пользователь может как нажать "Найти", так и просто выбрать категорию/наличие)
            const filtersForm = document.getElementById('filters-form');
            filtersForm.querySelectorAll('select').forEach(sel => {
                sel.addEventListener('change', () => {
                    // Сбрасываем на первую страницу при смене фильтра
                    filtersForm.querySelectorAll('input[name="page"], input[name="users_page"]')
                        .forEach(inp => inp.remove());
                    filtersForm.submit();
                });
            });


        });
    </script>
@endsection
