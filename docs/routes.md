# Маршруты и эндпоинты

Все маршруты объявлены в `routes/web.php` и работают в рамках middleware-группы
`web` (сессии, CSRF). Отдельного API (`routes/api.php`) в проекте нет — «API»
это JSON-эндпоинты внутри `web.php`.

Всего 33 маршрута (`php artisan route:list`).

Общая часть маршрутов:

```bash
php artisan route:list                    # все
php artisan route:list --path=cart        # по префиксу
php artisan route:list --except-vendor    # без служебных
```

## Сводная таблица

| Метод | URI | Имя | Действие | Доступ | Тип ответа |
| --- | --- | --- | --- | --- | --- |
| GET | `/` | `home` | — | все | redirect |
| GET | `/products` | `products.index` | `ProductController@index` | все | HTML |
| GET | `/products/{product}` | `products.show` | `ProductController@show` | все | ⚠️ метод пуст |
| POST | `/products` | `products.store` | `ProductController@store` | админ | redirect |
| GET | `/products/create` | `products.create` | `ProductController@create` | админ | ⚠️ метод пуст |
| GET | `/products/{product}/edit` | `products.edit` | `ProductController@edit` | админ | ⚠️ метод пуст |
| PUT/PATCH | `/products/{product}` | `products.update` | `ProductController@update` | админ | redirect |
| DELETE | `/products/{product}` | `products.destroy` | `ProductController@destroy` | админ | redirect |
| POST | `/register` | `register` | `Auth\Register` (`__invoke`) | все | redirect |
| POST | `/login` | `login` | `Auth\Login@login` | все | JSON |
| GET | `/logout` | `logout` | `Auth\Login@logout` | все | redirect |
| GET | `/cart` | `cart.index` | `CartPageController@index` | все | HTML |
| GET | `/cart/getById` | `cart.getById` | `CartController@getById` | все | JSON |
| POST | `/cart/sync` | `cart.sync` | `CartController@sync` | внутри: `auth` | JSON |
| POST | `/cart/remove` | `cart.remove` | `CartController@remove` | внутри: `auth` | JSON |
| POST | `/cart/merge` | `cart.merge` | `CartController@merge` | внутри: `auth` | JSON |
| POST | `/cart/guest-details` | `cart.guest.details` | `CartController@guestDetails` | все | JSON |
| GET | `/orders` | `orders.my` | `OrderController@myOrders` | `auth` | HTML |
| POST | `/orders` | `orders.store` | `OrderController@store` | `auth` | JSON |
| GET | `/orders/{order}` | `orders.show` | `OrderController@show` | `auth` + владелец/админ | HTML |
| GET | `/admin/dashboard` | `admin.dashboard` | `Admin\DashboardController@index` | админ | HTML |
| GET | `/admin/orders` | `admin.orders.index` | `OrderController@index` | админ | HTML |
| GET | `/admin/orders/{order}` | `admin.orders.show` | `OrderController@adminShow` | админ | HTML |
| — | `/users` (7 маршрутов) | `users.*` | `Users\UserController` | админ | ⚠️ view отсутствует |
| GET | `/up` | — | health-check фреймворка | все | JSON |
| GET/PUT | `/storage/{path}` | `storage.local`, `storage.local.upload` | FilesystemServiceProvider | все | файл |

## Доступ и разграничение прав

Админский контур (`/admin/*`, `/users/*`, CRUD товаров) закрыт middleware
`App\Http\Middleware\IsAdmin`. Поведение зависит от типа запроса:

| Кто запрашивает | HTML-запрос | Запрос с `Accept: application/json` |
| --- | --- | --- |
| Гость | `302` → `/products` (редирект на витрину с окном входа) | `401` |
| Пользователь (`is_admin = 0`) | `403` | `403` |
| Администратор (`is_admin = 1`) | как обычно | как обычно |

Отдельной страницы входа в проекте нет — вход сделан модальным окном в шапке,
поэтому гостя с админского URL отправляем на витрину, а не на `/login`.

Маршруты заказов (`/orders`) закрыты стандартным middleware `auth`, а доступ к
конкретному заказу дополнительно проверяется на принадлежность владельцу
(см. [known-issues.md](known-issues.md#3-middleware-isadmin-отдаёт-json-и-неверные-коды)
о самом middleware).

## Общественное

### `GET /` — `home`

Редирект на каталог: `302` → `/products`. Отдельной главной страницы в проекте
нет, витрина — `/products`.

### `GET /products` — `products.index`

Каталог. Параметры запроса:

| Параметр | Тип | Действие |
| --- | --- | --- |
| `category_id` | int | фильтр по категории |
| `page` | int | страница пагинации, 12 товаров на страницу |

Во view передаются `products`, `images` (все `ProductImage` — см.
[known-issues.md](known-issues.md#8-загрузка-всех-изображений-в-память)) и
`cartProductIds` — id товаров, уже в корзине текущего пользователя.
Категории в сайдбаре подтягиваются прямо во Blade через `Category::all()`.

### `POST /register` — `register`

Инвокабельный контроллер `Auth\Register`. Поля: `name`, `email`, `phone`,
`password`, `password_confirmation`. Валидация — `UserStoreRequest`
(сообщения об ошибках на русском). При успехе — `Auth::login()` и
`redirect('/products')`. Ответ обычный 302, JS в layout обрабатывает его
как успех и перезагружает страницу.

### `POST /login` — `login`

Поля: `email`, `password`, необязательное `cart_products` — JSON-строка
с массивом id товаров из гостевой корзины.

```json
{ "success": true, "redirect": "/products" }
```

Поле `redirect` равно `/admin/dashboard` для администратора и `/products` для
обычного пользователя. ⚠️ Сейчас это не работает: контроллер читает
несуществующий атрибут `role` вместо `is_admin`, поэтому всегда приходит
`/products`. См. [known-issues.md](known-issues.md#5-редирект-админа-после-входа-не-работает).

При неверных Credentials:

```json
{ "errors": { "email": ["Указана неверная почта или пароль"] } }
```

HTTP-код `422`. JS подставляет текст в `.form-error[data-field="email"]`.

### `GET /logout` — `logout`

`Auth::logout()` и `redirect()->intended('/products')`. Метод GET — состояние
меняется без CSRF-защиты; см.
[known-issues.md](known-issues.md#7-выход-через-get).

## Корзина

### `GET /cart` — `cart.index`

Страница корзины. Для гостя отдаёт пустую коллекцию: данные гостя JS
подтягивает отдельно через `cart.guest.details`.

### `GET /cart/getById` — `cart.getById`

Список id товаров в корзине. Для гостя — пустой массив (200, не 401):

```json
{ "product_ids": [3, 8, 12] }
```

Используется глобальной функцией `window.refreshCartBadge()` для бейджа в шапке.

### `POST /cart/sync` — `cart.sync` · авторизация обязательна

Полная перезапись корзины. Тело: `{"products": [1, 2, 5]}`.
Несуществующие id молча пропускаются. Перед записью все позиции корзины
удаляются, затем создаются заново с `quantity = 1` и текущей `products.price`.

```json
{ "success": true, "message": "Корзина сохранена", "items_count": 3 }
```

Для неавторизованного — `401 {"error": "Необходима авторизация"}`.

⚠️ Клиент вызывает `sync` и для гостя (после добавления в `localStorage`),
поэтому гость всегда получает `401`; см.
[known-issues.md](known-issues.md#9-гость-шлёт-sync-и-получает-401).

### `POST /cart/remove` — `cart.remove` · авторизация обязательна

Тело: `{"product_id": 5}`.

```json
{ "success": true, "deleted": 1 }
```

Количество всегда сбрасывается до 1 строки: повторное добавление того же товара
увеличивает количество только через `merge`, не через `sync`.

### `POST /cart/merge` — `cart.merge` · авторизация обязательна

Тело: `{"products": [...]}` — id из гостевой корзины. Для каждого id: если позиция
уже есть, `quantity += 1`, иначе создаётся новая. Существующие позиции
не удаляются.

```json
{ "success": true, "message": "Корзина объединена", "items_count": 2 }
```

Дублирует `Login@mergeGuestCart()` — см.
[known-issues.md](known-issues.md#6-слияние-корзины-реализовано-в-трёх-местах).

### `POST /cart/guest-details` — `cart.guest.details` · **без авторизации**

Единственный эндпоинт, специально открытый гостю. Тело: `{"products": [3, 8]}`.
Возвращает товары с `category` и `images` в порядке запрошенных id,
количество всегда `1`:

```json
{
  "items": [
    { "product": { "id": 3, "name": "...", "price": "24990.00", "...": "..." },
      "quantity": 1,
      "price": 24990.0 }
  ]
}
```

## Заказы

Все три маршрута — внутри группы `middleware('auth')`.

### `GET /orders` — `orders.my`

Список заказов текущего пользователя, 10 на страницу, с позициями и товарами.

### `GET /orders/{order}` — `orders.show`

Карточка заказа. Доступна владельцу или администратору, иначе — 403
через `abort_unless`. Обратите внимание: админ может открыть пользовательский
маршрут `/orders/{order}`, а пользователь — любой свой заказ.

### `POST /orders` — `orders.store`

Тело: `delivery_address` (обязательно), `comment` (необязательно, ≤255).
Валидация — `OrderStoreRequest`, где `authorize()` возвращает `auth()->check()`.

Успех:

```json
{ "success": true, "message": "Заказ принят", "order_id": 17 }
```

Пустая корзина — `422 {"success": false, "message": "Корзина пуста"}`.
Ошибки валидации — стандартный формат `{"errors": {...}}` с кодом `422`.

Заказ создаётся в транзакции, позиции корзины после этого удаляются.
Актуальные цены товаров не перечитываются.

## Админка

### `GET /admin/dashboard` — `admin.dashboard`

Middleware `IsAdmin`. Содержит:

- таблицу товаров с поиском и фильтрами; параметры: `search`, `category_id`,
  `stock` (`1` — в наличии, то есть `stock > 0`; `0` — нет в наличии,
  то есть `stock = 0`; пустое значение — без фильтра), пагинация 20 на страницу;
- таблицу пользователей, 15 на страницу, отдельный ключ пагинации `users_page`;
- список категорий для выпадающего списка;
- AJAX-форму создания/редактирования товара и удаление через `fetch`.

Поля формы товара: `name`, `description`, `category_id`, `stock`, `price`,
`old_price`, `image`. Отправляется на `products.store` или `products.update`;
`_method` в скрытом поле переключает метод.

Фильтры применяются scope `Product::Filters()`. Поиск и категория объединяются
через `AND`, то есть при поиске «iPhone» по категории «Ноутбуки» товар с таким
же словом в названии из другой категории не попадёт в выдачу.

### `GET /admin/orders` — `admin.orders.index`

Middleware `IsAdmin`. Все заказы, 20 на страницу, с пользователем и позициями.

### `GET /admin/orders/{order}` — `admin.orders.show`

Middleware `IsAdmin`. Карточка заказа в админском оформлении.

Статусы заказа (`new`, `processing`, `done`, `canceled`) отображаются
цветными плашками, но сменить статус нельзя — эндпоинта и формы нет.

## Пользователи

`Route::resource('users', UserController::class)` под `IsAdmin` — 7 маршрутов.
Реализован только `store`; `index` возвращает `view('users.index')`, которого
в проекте нет, поэтому `GET /users` падает. Остальные методы — пустые.
Описание — в [known-issues.md](known-issues.md#10-view-users-index-не-существует).

## Требования к JSON-запросам

Фронтенд отправляет:

```js
fetch(url, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify({ /* ... */ }),
});
```

`Accept: application/json` включает JSON-ответы для исключений
(настроено в `bootstrap/app.php` через `shouldRenderJsonWhen`), а
`X-CSRF-TOKEN` обязателен для всех POST.