# Архитектура

## Общая картина

Проект — монолит на стандартном каркасе Laravel 13. Никаких дополнительных слоёв
(сервисы, репозитории, DTO,use-case'ы) нет: HTTP-логика целиком в контроллерах,
работа с данными — через Eloquent, проверка входных данных — в `FormRequest`.

```
HTTP-запрос
    ↓
routes/web.php            маршрут + middleware
    ↓
App\Http\Requests         валидация и authorize()           (опционально)
    ↓
App\Http\Controllers       бизнес-логика
    ↓
App\Models (Eloquent)      запросы к БД
    ↓
SQLite (database/database.sqlite)

Ответ: Blade-шаблон (resources/views) либо JSON для fetch-запросов
```

Middleware в проекте один — `App\Http\Middleware\IsAdmin`. Он подключается
к маршруту как класс (`->middleware(IsAdmin::class)`), а не через алиас,
поэтому в `bootstrap/app.php` менять ничего не нужно.

## Слои и где что лежит

| Слой | Каталог | Ответственность |
| --- | --- | --- |
| Маршруты | `routes/web.php` | URL → контроллер, назначение групп доступа |
| Контроллеры | `app/Http/Controllers` | Сценарии use-case, работа с моделями, выбор view/JSON |
| Валидация | `app/Http/Requests` | Правила полей, сообщения, проверка прав |
| Права | `app/Http/Middleware` | Проверка `is_admin` |
| Домен | `app/Models` | Атрибуты, связи, scope'ы |
| Представление | `resources/views` | HTML + инлайновые CSS и JS |
| Данные | `database/migrations`, `database/seeders` | Схема и демо-наполнение |

Схема БД подробно — в [database.md](database.md), маршруты — в [routes.md](routes.md).

## Доменная модель

```
Category ──1:N──> Product ──1:N──> ProductImage
                      ▲    ▲
        CartItem ─────┘    └────── OrderItem
           │                      │
          Cart ──1:N──> CartItem  Order ──1:N──> OrderItem
           │                      │
           └──────> User <────────┘
```

Отношения, объявленные в моделях:

| Модель | Связи |
| --- | --- |
| `Category` | `products()` — `hasMany(Product)` |
| `Product` | `category()` — `belongsTo(Category)`, `images()` — `hasMany(ProductImage)`, scope `Filters()` |
| `ProductImage` | `user()` — `belongsTo(Product)`, ⚠️ метод назван `user`, но связь ведёт на товар |
| `User` | наследует `Authenticatable`, `casts(): password => hashed` |
| `Cart` | `user()` — `belongsTo(User)`, `items()` — `hasMany(CartItem)` |
| `CartItem` | `cart()`, `product()` |
| `Order` | `user()`, `items()` — `hasMany(OrderItem)` |
| `OrderItem` | `order()`, `product()` |

Модели используют атрибуты вместо свойств — Laravel 13:

```php
#[Fillable(['id', 'category_id', 'name', 'description', 'price', 'stock', 'old_price'])]
class Product extends Model
```

У пользователя дополнительно скрыты `password` и `remember_token`:

```php
#[Fillable(['name', 'email', 'password', 'phone', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
```

### Фильтрация товаров

Единственный scope — `Product::scopeFilters()`, используется только в админ-дашборде:

```php
Product::Filters($request)->with(['category', 'images'])->latest()->paginate(20, ['*'], 'page');
```

Поддерживает `search` (по `name` и `description`), `category_id`, `stock`.
Пустое значение любого фильтра означает «без фильтра», а `stock=1` ищет товары
с ненулевым остатком («в наличии»), `stock=0` — с нулевым. Условия поиска
обёрнуты в группу, поэтому поиск и категория объединяются через `AND`.

## Права доступа

Роль хранится в `users.is_admin` (boolean). Проверок роли ровно три:

1. `IsAdmin` middleware — для всего админского контура: `/admin/*`, CRUD товаров,
   `/users/*`.
2. `authorize()` в `ProductStoreRequest` / `ProductUpdateRequest` — `auth()->check() && auth()->user()->is_admin`.
3. `abort_unless()` в `OrderController@show` — заказ доступен владельцу или администратору.

Публичная часть (каталог, корзина, регистрация, вход) доступна всем.
Эндпоинты `orders.*` требуют сессии — middleware `auth` в группе маршрутов.

Поведение `IsAdmin` зависит от типа запроса: гостю HTML-запрос отвечает
редиректом на витрину (отдельной страницы входа нет, вход сделан модальным
окном), ожидающему JSON — `401`; пользователю без прав — `403` в обоих случаях.
Подробнее — [routes.md](routes.md#доступ-и-разграничение-прав).

## Устройство корзины

Корзина работает в двух режимах, и это самое неочевидное место проекта.

**Гость (без авторизации).** Корзина целиком в `localStorage`. Ключи вида
`id-product-{id}`, значение — id товара. Количество всегда 1: повторное нажатие
кнопки удаляет товар из корзины, увеличить количество нельзя.
Данные о товарах для гостя подгружаются отдельным эндпоинтом
`POST /cart/guest-details`, который сознательно не требует авторизации.

**Пользователь.** Корзина в БД: одна строка `carts` на пользователя
(`Cart::firstOrCreate(['user_id' => ...])`), позиции — в `cart_items`.
Цена фиксируется в момент добавления (`cart_items.price`), количество — тоже 1
при синхронизации.

**Переход между режимами.** При входе форма отправляет скрытое поле
`cart_products` с JSON-массивом id из `localStorage`. `Auth\Login@login` вызывает
`mergeGuestCart()`, которая добавляет эти позиции к серверной корзине
с накоплением количества. JS после успешного входа чистит `localStorage`
и перезагружает страницу.

Дублирующая логика слияния есть в трёх местах — `Login@mergeGuestCart()`,
`CartController@merge()`, а на клиенте — в `products/index.blade.php`. См.
[known-issues.md](known-issues.md#6-слияние-корзины-реализовано-в-трёх-местах).

**Индикатор в шапке.** Функция `window.refreshCartBadge()` в
`layouts/main.blade.php` считает позиции: у авторизованных — запросом
`GET /cart/getById`, у гостей — перебором ключей `localStorage`.
Она вызывается на каждой странице, поэтому количество на бейдже равно
числу позиций, а не суммарному количеству товаров.

## Оформление заказа

`POST /orders` (`OrderController@store`) — единственное место, где корзина
превращается в заказ:

1. Загружается корзина пользователя с товарами; если пуста — `422`.
2. В `DB::transaction` создаётся `orders` со статусом `new` и суммой
   позиций (`price * quantity`), затем создаются `order_items`.
3. Позиции корзины удаляются.

Суммы берутся из `cart_items.price`, то есть из цены на момент добавления, а не
из актуальной `products.price`, и `stock` не уменьшается. Статусы заказа —
`new`, `processing`, `done`, `canceled`; они только отображаются, смены статуса
в проекте нет.

## Работа с изображениями товаров

Файлы кладутся на диск `public` (`storage/app/public`), то есть физически в
`storage/app/public/images/{id_товара}/{оригинальное_имя}`, а наружу отдаются
через симлинк `public/storage`. В БД в `product_images.path` сохраняется путь
**с префиксом `/storage/`** — то есть готовая к отдаче ссылка.

При обновлении товара с новой картинкой старая папка и все записи
`product_images` удаляются целиком. При удалении товара записи изображений
удаляются по `product_id`, после чего стирается папка на диске.

Заглушка для товара без фото — `storage/app/public/images/no-image.jpg`.

## Фронтенд

Сборщик есть (Vite 8 + Tailwind 4), но не подключён. Разметка использует
semantic HTML и инлайновые стили, написанные прямо в Blade-шаблонах:

| Файл | Строк | Что внутри |
| --- | --- | --- |
| `layouts/main.blade.php` | 853 | стили интерфейса, модалки входа/регистрации, AJAX-формы, счётчик корзины |
| `admin/dashboard.blade.php` | 956 | стили админки, фильтры, AJAX-форма товара, удаление через `fetch` |
| `cart/index.blade.php` | 849 | страница корзины, гостевая и авторизованная ветки, оформление заказа |
| `products/index.blade.php` | 200 | каталог и переключатель «в корзине / добавить» |

`resources/js/app.js` содержит только `//`, `resources/css/app.css` — только
`@import 'tailwindcss';`. Директива `@vite` не встречается ни в одном шаблоне
(проверено поиском по `resources/`).

Взаимодействие с сервером идёт через `fetch` с заголовками
`X-CSRF-TOKEN` (берётся из `<meta name="csrf-token">`), `Accept: application/json`
и `X-Requested-With: XMLHttpRequest`. Ошибки валидации `422` разбираются
в JS и подставляются в `.form-error` рядом с полем.

## Обработка ошибок

`bootstrap/app.php` переключает JSON-ответы для `api/*` и любых запросов с
`Accept: application/json`:

```php
$exceptions->shouldRenderJsonWhen(
    fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
);
```

Это позволяет контроллерам отдавать `response()->json(...)` с кодами 401/422,
которые ожидает фронтенд. Свои обработчики исключений не настроены.

## Что осознанно не сделано

Список неполон по дизайну, а не по недосмотру. Полный перечень с приоритетами —
в [known-issues.md](known-issues.md).