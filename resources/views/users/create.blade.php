@extends('layouts.main')

@section('content')
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Регистрация</h1>
            <p class="auth-subtitle">Создайте аккаунт, чтобы оформлять заказы</p>

            <form method="POST" action="{{ route('products.index') }}" class="auth-form" novalidate>
                @csrf

                {{-- Имя --}}
                <div class="form-group">
                    <label for="name" class="form-label">Имя</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="form-input @error('name') is-invalid @enderror"
                        placeholder="Введите ваше имя"
                        required
                        autofocus
                    >
                    @error('name')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-input @error('email') is-invalid @enderror"
                        placeholder="example@mail.ru"
                        required
                    >
                    @error('email')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Телефон --}}
                <div class="form-group">
                    <label for="phone" class="form-label">Телефон</label>
                    <input
                        id="phone"
                        type="tel"
                        name="phone"
                        value="{{ old('phone') }}"
                        class="form-input @error('phone') is-invalid @enderror"
                        placeholder="+7 (___) ___-__-__"
                        required
                    >
                    @error('phone')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Пароль --}}
                <div class="form-group">
                    <label for="password" class="form-label">Пароль</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-input @error('password') is-invalid @enderror"
                        placeholder="Минимум 8 символов"
                        required
                    >
                    @error('password')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Подтверждение пароля --}}
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="form-input"
                        placeholder="Повторите пароль"
                        required
                    >
                </div>

                <button type="submit" class="auth-submit-btn">
                    Зарегистрироваться
                </button>

                <div class="auth-footer">
                    Уже есть аккаунт?
                    <a href="#">Войти</a>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* ===== Auth page ===== */
        .auth-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 48px 24px 64px;
            display: flex;
            justify-content: center;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            background-color: #ffffff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        .auth-title {
            font-size: 24px;
            font-weight: 700;
            color: #2f3640;
            margin-bottom: 6px;
            text-align: center;
        }

        .auth-subtitle {
            font-size: 14px;
            color: #636e72;
            text-align: center;
            margin-bottom: 28px;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 18px;
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

        .form-input.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(214, 48, 49, 0.12);
        }

        .form-error {
            font-size: 12px;
            color: #d63031;
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
            margin-top: 4px;
        }

        .auth-submit-btn:hover {
            background-color: #009e7f;
        }

        .auth-submit-btn:active {
            transform: translateY(1px);
        }

        .auth-footer {
            text-align: center;
            font-size: 14px;
            color: #636e72;
            margin-top: 4px;
        }

        .auth-footer a {
            color: #0984e3;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .auth-footer a:hover {
            color: #0770c4;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 24px 20px;
            }

            .auth-title {
                font-size: 20px;
            }
        }
    </style>
@endsection
