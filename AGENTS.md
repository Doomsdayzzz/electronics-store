# AGENTS.md

Правила работы ИИ-агентов с проектом **electronics-store** («ЦифроStore» —
интернет-магазин электроники на Laravel 13).

Полная документация проекта — в [`docs/`](docs/): [архитектура](docs/architecture.md),
[схема БД](docs/database.md), [маршруты](docs/routes.md),
[разработка](docs/development.md), [известные проблемы](docs/known-issues.md).

## Проект в двух абзацах

Монолит Laravel без сервисного слоя: вся бизнес-логика в `app/Http/Controllers`,
данные через Eloquent, валидация в `app/Http/Requests`, представления — Blade
с инлайновыми CSS и JS. Есть каталог товаров с категориями, корзина (гостевая
в `localStorage` и пользовательская в БД), заказы и админ-панель. Роль хранится
в `users.is_admin`, проверяется middleware `App\Http\Middleware\IsAdmin`.
БД — SQLite, шаблонов и тестов домена нет.

## Окружение

| Компонент | Версия |
| --- | --- |
| PHP | `^8.3` (проверено 8.3.33, Windows) |
| Composer | 2.x (проверено 2.10.3) |
| Laravel | `^13.17` |
| БД | SQLite, файл `database/database.sqlite` |
| Тесты | PHPUnit `^12.5` |

## Подготовка окружения

```powershell
composer install
copy .env.example .env
php artisan key:generate
New-Item -ItemType File database\database.sqlite   # файл не в Git
php artisan migrate --seed
php artisan storage:link                           # симлинк не в Git
```

## Команды

```bash
php artisan serve            # :8000
php artisan migrate:fresh --seed
php artisan route:list --except-vendor
php artisan test
vendor/bin/pint              # форматирование; --test — только проверка
vendor/bin/pail              # логи
```

Демо-доступы: `user1@ya.ru` / `1234`, `admin1@ya.ru` / `132456`.

## Перед любыми правками

1. **Прочитай [docs/known-issues.md](docs/known-issues.md).** Проблемы №1–4 и №11
   уже исправлены и закрыты тестами, остальные открыты. Не «доделывай»
   исправленное и не считай открытые дефекты случайной поломкой.
2. `php artisan test` — 11 тестов, все зелёные. Любой тест, который ходит в базу,
   обязан использовать `RefreshDatabase`, иначе в `:memory:` не будет таблиц.
3. `User::factory()->create()` **не работает**: фабрика не совпадает со схемой
   `users` (выдаёт `email_verified_at` и `remember_token`, которых нет, и не
   задаёт обязательную `phone`). Создавай пользователей через
   `User::create([...])` — так сделано в `AdminAccessTest` и `ProductAdminTest`.
4. Не коммитай `.env`, `database/database.sqlite`, `public/storage` и
   `storage/app/public/images/*` — они в `.gitignore`.

## Структура

```
app/Http/Controllers/   ProductController, CartController, CartPageController,
                        OrderController, Auth\{Login,Register},
                        Admin\DashboardController, Users\UserController
app/Http/Requests/      ProductStoreRequest, ProductUpdateRequest,
                        UserStoreRequest, OrderStoreRequest
app/Http/Middleware/    IsAdmin
app/Models/             Category, Product, ProductImage, User, Cart, CartItem,
                        Order, OrderItem
database/               8 миграций, 3 сидера
resources/views/        layouts/main (шапка, модалки, счётчик корзины),
                        products/index, cart/index, orders/*, admin/*
routes/web.php          все 33 маршрута
```

## Соглашения — следуй тому, что уже есть

**Модели — атрибуты Laravel 13, а не свойства:**

```php
#[Fillable(['id', 'category_id', 'name', 'description', 'price', 'stock', 'old_price'])]
class Product extends Model
{
    use HasFactory;

    public function category(): BelongsTo { /* ... */ }
    public function images(): HasMany { /* ... */ }
    public function scopeFilters(Builder $builder, Request $request): Builder { /* ... */ }
}
```

`$fillable`, `$hidden`, `$casts` как свойства здесь не используются. `casts()`
в `User` — метод.

**Контроллеры:** страница → `view(...)`; AJAX → `response()->json([...])` с
явным кодом (`401`, `403`, `422`); редирект →
`redirect()->route('...')->with('success', 'текст')`.

**Валидация:** `FormRequest` в `app/Http/Requests`, сообщения на русском.
`authorize()` возвращает `auth()->check() && auth()->user()->is_admin` для
админских операций — не дублируй эту проверку в контроллере.

**Права:** админские маршруты оборачиваются в `->middleware(IsAdmin::class)`;
доступ к отдельной записи проверяется через `abort_unless(...)`.

**Именование:** контроллеры в единственном числе, но `IsAdmin` — прилагательное,
`Login` и `Register` — без суффикса; JSON-эндпоинты корзины в единственном
(`/cart/sync`, `/cart/remove`), товарные — во множественном (`/products`).
Комментарии и тексты интерфейса — на русском.

## Фронтенд — не трогай сборку зря

Vite 8 и Tailwind 4 установлены и настроены, но **ни один шаблон не содержит
директиву `@vite`**. Вся вёрстка, стили и скрипты — инлайном в `.blade.php`
(`layouts/main.blade.php` — 853 строки, `admin/dashboard.blade.php` — 956,
`cart/index.blade.php` — 849). `resources/js/app.js` пуст.

`npm run dev` / `npm run build` на результат не влияют. Если задача не про
переезд на Vite — не начинай переезд и не добавляй `@vite` в шаблоны: это
переписывание всей вёрстки.

Обмен с сервером идёт через `fetch` с `X-CSRF-TOKEN` (из `<meta
name="csrf-token">`), `Accept: application/json` и
`X-Requested-With: XMLHttpRequest`; ошибки `422` разбираются в JS.

## Правила поведения агента

- **Не добавляй зависимости без явной просьбы пользователя.** В частности,
  Laravel Boost (`laravel/boost`) не установлен намеренно: `AGENTS.md` и
  `CLAUDE.md` содержали bootstrap-инструкцию по его установке, она заменена
  этим файлом. Не запускай `composer require` и `php artisan boost:install`
  без отдельной команды.
- **Не перезаписывай `CLAUDE.md`** — он оставлен как есть, историческая копия
  bootstrap-инструкций.
- **Не правь `.idea/`, `.gigacode/`, `vendor/`, `node_modules/`.**
- **Не меняй `composer.json`, `package.json` и `.env` без просьбы.**
- Не выноси CSS/JS в новые файлы без согласования — это переход на Vite в
  частичной форме.
- Перед сдачей: `php artisan test` (должен быть зелёным) и
  `vendor/bin/pint --test`.
- `vendor/bin/pint --test` по всему проекту падает на 16 старых файлах — это
  существующий долг по стилю. **Не запускай `vendor/bin/pint` без нужды:** он
  переформатирует их разом и замусорит диф. Форматируй только свои файлы:
  `vendor/bin/pint app/... routes/web.php tests/...`
- Последний коммит — `7bd21ed добавлено оформление и просмотр заказов
  пользователя. Админ видит все заказы`. Модуль заказов только появился и ещё
  почти не покрыт тестами; при правках в нём проверяй сценарии вручную.

## Проверка перед сдачей

```bash
php artisan test                 # ожидаемо: 11 тестов, все зелёные
vendor/bin/pint --test          # падает на 16 старых файлах — не твоя регрессия
php artisan route:list --except-vendor    # 33 маршрута
```