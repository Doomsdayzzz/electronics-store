# Известные проблемы и недоработки

Документ описывает состояние проекта на момент коммита
`7bd21ed добавлено оформление и просмотр заказов пользователя. Админ видит все заказы`.

Проверялось чтением кода и запуском (`php artisan test`, `php artisan route:list`,
`tinker` с выводом `toSql()`, запросы к поднятому `php artisan serve`).
Там, где поведение проверялось запуском, это указано явно.

> Проблемы №1–4 исправлены: правки внесены, тесты добавлены, поведение проверено
> запуском. Остальные — описание, а не список задач.

## Сводка

| # | Проблема | Влияние | Статус | Проверено |
| --- | --- | --- | --- | --- |
| 1 | Нет маршрута `/` | высокая | ✅ исправлено | да |
| 2 | Удаление картинок при удалении товара | высокая | ✅ исправлено | да |
| 3 | `IsAdmin` отдаёт JSON и неверные коды | высокая | ✅ исправлено | да |
| 4 | Фильтр по наличию не срабатывает | средняя | ✅ исправлено | да |
| 5 | Редирект админа после входа не работает | средняя | открыта | нет |
| 6 | Слияние корзины в трёх местах | средняя | открыта | нет |
| 7 | Выход через GET | средняя | открыта | нет |
| 8 | Загрузка всех изображений в память | средняя | открыта | нет |
| 9 | Гость шлёт `sync` и получает 401 | средняя | открыта | нет |
| 10 | `view users.index` не существует | средняя | открыта | нет |
| 11 | Поиск ломает фильтр по категории | средняя | ✅ исправлено | да |
| 12 | `/products/create` и `edit` недоступны | средняя | открыта | да |
| 13 | Остатки не уменьшаются при заказе | высокая | открыта | нет |
| 14 | Цена в корзине не актуализируется | высокая | открыта | нет |
| 15 | Количество товара всегда 1 | средняя | открыта | нет |
| 16 | Нет валидации загружаемого изображения | средняя | открыта | нет |
| 17 | Синхронизация корзины не атомарна | средняя | открыта | нет |
| 18 | `userSeeder` с маленькой буквы — сломается на Linux | средняя | открыта | нет |
| 19 | Нет смены статуса заказа | средняя | открыта | нет |
| 20 | Тестовое покрытие минимальное | средняя | частично | да |
| 21 | Нет миграций `sessions`/`cache`/`jobs` | низкая | открыта | нет |
| 22 | Опечатки и мелкие расхождения | низкая | открыта | нет |
| 23 | Не реализовано: страница товара, смена статуса, управление пользователями | — | открыта | нет |
| 24 | `UserFactory` несовместима со схемой `users` | средняя | открыта | да |

## Подробно

### 1. Нет маршрута home

**Где:** `routes/web.php`, `tests/Feature/ExampleTest.php`

Маршрута `/` в проекте нет, поэтому корневой URL отдаёт **404**. Проверено
запросом к поднятому серверу. Входная точка для посетителя — `/products`.

Из-за этого падает тест:

```
Tests\Feature\ExampleTest::test_the_application_returns_a_successful_response
Expected response status code [200] but received 404.
```

Проверено: `php artisan test` — 1 пройден, 1 провален.

**Статус: исправлено.** В `routes/web.php` добавлен маршрут `home`:
`GET /` → `302` на `/products`. Тест-заглушка переписан: `test_the_home_page_redirects_to_the_catalog`
проверяет редирект, `test_the_catalog_page_is_successful` — что каталог открывается
(с `RefreshDatabase`). Регрессия закрыта тестом.

### 2. Удаление картинок при удалении товара

**Где:** `app/Http/Controllers/ProductController.php`, метод `destroy()`

```php
public function destroy(Product $product)
{
    Product::destroy($product->id);
    ProductImage::destroy($product->id);   // ← это id товара, а не записи изображения
    Storage::disk('public')->deleteDirectory('images/' . $product->id);
    ...
}
```

`ProductImage::destroy()` принимает **id записи в `product_images`**, а сюда
передаётся `id` товара. Последствия:

- записи `product_images` этого товара остаются в БД (удаляется случайная запись
  с таким же номером, если она есть);
- в каталоге у оставшихся товаров могут появиться «чужие» картинки, потому что
  выборка идёт как `$images->where('product_id', $product->id)->first()` —
  по `product_id` осиротевшие записи не мешают, но папка на диске удаляется,
  и в БД остаются битые ссылки;
- позиции корзины (`cart_items`) и заказов (`order_items`) для этого товара
  не удаляются, хотя `cart_items.product_id` и `order_items.product_id`
  объявлены с `cascadeOnDelete()` — но Eloquent не каскадирует, каскадирует БД,
  а `Product::destroy()` удаляет строку, так что с БД здесь как раз всё чисто,
  и проблема только в `product_images` (у неё `cascadeOnDelete` не задан).

**Статус: исправлено.** Метод переписан:

```php
public function destroy(Product $product)
{
    ProductImage::where('product_id', $product->id)->delete();
    Storage::disk('public')->deleteDirectory('images/' . $product->id);

    $product->delete();

    return redirect()->route('admin.dashboard')->with('success', 'Товар удалён');
}
```

Регрессия закрыта тестом `test_product_images_are_deleted_with_the_product`
(в `tests/Feature/ProductAdminTest.php`): удаляет товар с двумя записями
`product_images` и проверяет, что в БД не осталось ни товара, ни его изображений.

### 3. Middleware IsAdmin отдаёт JSON и неверные коды

**Где:** `app/Http/Middleware/IsAdmin.php`

```php
$user = Auth::user();
if ($user == null) {
    return Response()->json(['success' => false, 'message' => 'You are not authorized...']);
} elseif ($user->is_admin === 1) {
    return $next($request);
} else {
    return Response()->json(['success' => false, 'message' => 'Permission denied']);
}
```

Проблемы:

- гостю возвращается **200** с JSON, а не редирект на `/login` — пользователь
  видит в браузере сырой JSON вместо страницы входа;
- обычному пользователю тоже **200**, а не 403;
- сравнение `$user->is_admin === 1` строгое: SQLite отдаёт `1`, MySQL может
  отдать `true`, а приведение типа зависит от драйвера. Надёжнее
  `$user->is_admin` или `(bool) $user->is_admin`.

Проверено: `GET /users` без авторизации возвращает 200 и 72 байта JSON.

**Статус: исправлено.** Middleware переписан:

```php
$user = Auth::user();

if ($user === null) {
    // Гостю показываем витрину, где вход сделан модальным окном
    return $request->expectsJson()
        ? response()->json(['success' => false, 'message' => 'Необходима авторизация'], 401)
        : redirect()->route('products.index');
}

if (!$user->is_admin) {
    return $request->expectsJson()
        ? response()->json(['success' => false, 'message' => 'Доступ запрещён'], 403)
        : abort(403);
}

return $next($request);
```

Строгое `=== 1` заменено на проверку значения атрибута, гостю возвращается
редирект на витрину (отдельной страницы входа в проекте нет — вход сделан
модальным окном в шапке).

Проверено на живом сервере и тестами:

| Кто | Запрос | Ответ |
| --- | --- | --- |
| Гость | `GET /admin/dashboard` (HTML) | `302` → `/products` |
| Гость | `GET /admin/dashboard` (JSON) | `401` |
| Гость | `GET /users` (JSON) | `401` (было `200`) |
| Пользователь | `GET /admin/dashboard` | `403` (было `200` + JSON) |
| Пользователь | `GET /admin/orders`, `GET /users` | `403` |
| Администратор | `GET /admin/dashboard` | `200` |

Тесты: `tests/Feature/AdminAccessTest.php` — четыре случая (гость HTML, гость
JSON, пользователь, администратор).

### 4. Фильтр по наличию никогда не срабатывает

**Где:** `app/Models/Product.php`, scope `Filters()`

Было:

```php
->when($request->stock, function (Builder $builder) use ($request) {
    $builder->where('stock', (int) $request->input('stock'));
})
```

Здесь было **две** ошибки:

1. Значение `0` («Нет в наличии», `resources/views/admin/dashboard.blade.php:68-72`)
   — строка `"0"` в PHP ложна, поэтому `when()` пропускал всё условие и фильтр
   не применялся вовсе.
2. Значение `1` («В наличии») превращалось в `where stock = 1`, то есть искало
   товары с остатком ровно 1, а не ненулевой остаток.

Проверено через `toSql()` **до** правки:

| Запрос | SQL (было) |
| --- | --- |
| `?stock=0` | `select * from "products"` — условия нет |
| `?stock=1` | `select * from "products" where "stock" = ?` |

**Статус: исправлено.** Условие переведено на `filled()` (пустое значение
значит «без фильтра») и на семантику, соответствующую подписям в UI:

```php
->when($request->filled('stock'), function (Builder $builder) use ($request) {
    // 1 — «в наличии» (остаток больше нуля), 0 — «нет в наличии»
    $builder->where('stock', $request->boolean('stock') ? '>' : '=', 0);
})
```

`toSql()` **после** правки:

| Запрос | SQL (стало) |
| --- | --- |
| `?stock=0` | `... where "stock" = ?` |
| `?stock=1` | `... where "stock" > ?` |
| `?stock=` | `select * from "products"` — фильтра нет |

Те же `filled()` применены к `search` и `category_id` (см. проблему 11).
Регрессия закрыта тестами `test_stock_filter_accepts_zero` и
`test_empty_filters_do_not_restrict_results`.

### 5. Редирект админа после входа не работает

**Где:** `app/Http/Controllers/Auth/Login.php:34`

```php
$isAdmin = User::where('email', $request->email)->first()->role;
```

Атрибута `role` у модели нет — есть `is_admin`. Eloquent вернёт `null` для
несуществующего атрибута, поэтому `$isAdmin` всегда ложно и в JSON всегда
приходит `"redirect": "/products"`. Администратор после входа попадает на витрину,
а не в админку; ссылка «Админка» в шапке при этом есть.

Побочно: `->first()` здесь может вернуть `null`, и обращение `->role` даст ошибку.

**Что делать:** `$isAdmin = auth()->user()->is_admin;`

### 6. Слияние корзины реализовано в трёх местах

Одна и та же логика «добавить id из гостевой корзины с накоплением количества»
написана трижды:

1. `Auth\Login@mergeGuestCart()` (`app/Http/Controllers/Auth/Login.php:56-85`);
2. `CartController@merge()` (`app/Http/Controllers/CartController.php:105-150`);
3. на клиенте — `products/index.blade.php:180-194` шлёт `localStorage` в
   `/cart/sync`, где количество принудительно сбрасывается в `1`.

Расхождения в деталях: вариант в `Login` пишет `Log::info($cart->id)` в рабочем
коде, вариант в `merge` — нет; `sync` вообще обнуляет количество. Эндпоинт
`/cart/merge` с клиента не вызывается ни разу.

**Что делать:** оставить один серверный метод (например, в контроллере) и
вызывать его из `Login`, а на клиенте полагаться только на `sync` с передачей
количеств, а не только id.

### 7. Выход через GET

**Где:** `routes/web.php:21`

```php
Route::get('/logout', [Login::class, 'logout'])->name('logout');
```

Выход меняет состояние, но сделан `GET`, поэтому не защищён CSRF и работает
по клику в `<img src>` или по prefetch браузера. Достаточно
`Route::post('/logout', ...)` + форма с `@csrf`.

### 8. Загрузка всех изображений в память

**Где:** `app/Http/Controllers/ProductController.php:29` и
`resources/views/products/index.blade.php:15`

```php
$images = ProductImage::all();          // вся таблица, без фильтра и без пагинации
```

```blade
{{ $images->where('product_id', $product->id)->first()?->path ... }}
```

На каждой странице каталога в память грузится **вся** таблица `product_images`,
а поиск картинки идёт перебором коллекции для каждого товара. С ростом числа
изображений каталог будет деградировать.

**Что делать:** грузить изображения только для текущей страницы, например
`ProductImage::whereIn('product_id', $products->pluck('id'))->get()->groupBy('product_id')`,
и использовать связь `$product->images->first()`.

### 9. Гость шлёт sync и получает 401

**Где:** `products/index.blade.php:192-194`, `CartController@sync`

Гость, добавив товар в `localStorage`, вызывает `syncCartToServer()`:

```js
const currentCart = getCartFromStorage();
syncCartToServer(currentCart);   // для гостя тоже
```

`CartController@sync()` первым делом проверяет авторизацию и отвечает `401`.
Клиент этот код отрабатывает (`if (response.status === 401) return;`), поэтому
ошибки не видно, но на каждый клик гостя уходит лишний запрос, и в devtools
консоли полно 401.

**Что делать:** оборачивать вызов в `if (isAuthorized)`, либо, что правильнее,
не синхронизировать корзину гостя вообще.

### 10. View users index не существует

**Где:** `app/Http/Controllers/Users/UserController.php:14-18`

```php
public function index()
{
    $users = User::all();
    return view('users.index', compact('users'));
}
```

Каталога `resources/views/users/` в проекте нет, поэтому запрос падает.
Для гостя это не проявляется — его раньше останавливает `IsAdmin` (проблема 3).
Проверить можно только под администратором: `GET /users` → «View [users.index]
not found».

Дополнительно: `store()` возвращает `User::query()->create($validated)` —
Eloquent-модель не является ответом, поэтому вместо редиректа будет исключение.

Остальные методы ресурса (`create`, `show`, `edit`, `update`, `destroy`) —
пустые заглушки.

**Что делать:** создать `resources/views/users/index.blade.php` и возвращать
redirect с flash-сообщением из `store`.

### 11. Поиск ломает фильтр по категории

**Где:** `app/Models/Product.php:34-47`

```php
->when($request->search, function (Builder $builder) use ($request) {
    $builder->where('name', 'like', "%{$request->search}%")
        ->orWhere('description', 'like', "%{$request->search}%");
})
->when($request->category_id, function (Builder $builder) use ($request) {
    $builder->where('category_id', $request->input('category_id'));
})
```

`when()` не группирует условия, поэтому `orWhere` «вырывается» наружу, а
последующий `where` по категории связывается только с ним — приоритет `AND`
выше `OR` в SQL.

Проверено через `toSql()` для `?search=iPhone&category_id=2`:

```sql
select * from "products"
where ("name" like ? or "description" like ? and "category_id" = ?)
```

Читается как `name LIKE ? OR (description LIKE ? AND category_id = ?)`, то есть
при одновременном поиске и выборе категории в результат попадают товары из
других категорий, совпавшие только по названию.

**Статус: исправлено.** Условия поиска обёрнуты в замыкание-группу, все три
фильтра переведены на `filled()`:

```php
->when($request->filled('search'), function (Builder $builder) use ($request) {
    $term = '%'.$request->input('search').'%';

    $builder->where(function (Builder $builder) use ($term) {
        $builder->where('name', 'like', $term)
            ->orWhere('description', 'like', $term);
    });
})
```

`toSql()` **после** правки:

```sql
select * from "products"
where ("name" like ? or "description" like ?) and "category_id" = ?
```

Регрессия закрыта тестом `test_search_is_combined_with_category_filter`:
при поиске `iPhone` по категории 1 возвращается только смартфон, а не ноутбук
с тем же словом в названии.

### 12. `/products/create` и `edit` недоступны

**Где:** `routes/web.php:13-16`

```php
Route::resource('products', ProductController::class)->only(['index', 'show']);
Route::middleware([IsAdmin::class])->group(function () {
    Route::resource('products', ProductController::class)->except(['index', 'show']);
});
```

`GET /products/create` совпадает с ранее зарегистрированным
`GET /products/{product}`, поэтому срабатывает именно он. Проверено:
`GET /products/create` → **404** (неявная привязка модели не находит товар с
id `"create"`), хотя маршрут `products.create` существует и защищён `IsAdmin`.

`/products/{product}/edit` страдает тем же — плюс ещё и перекрывается
`products.show`, если бы порядок регистрации был другим.

На практике это не мешает: админка добавляет и правит товары через AJAX-модалку
на дашборде. Но `products.create` и `products.edit` — мёртвые маршруты, а
любая ссылка на них даст 404.

**Что делать:** регистрировать админские маршруты первыми либо вынести
`create`/`edit` в явные `Route::get` до ресурса.

### 13. Остатки не уменьшаются при заказе

**Где:** `app/Http/Controllers/OrderController.php:84-106`

При оформлении заказа создаются `orders` и `order_items`, очищается корзина —
но `products.stock` не уменьшается, товары не резервируются и не проверяется
их наличие. Товар без остатка можно заказать сколько угодно раз.

В `cart_items` при этом нет ограничения по остатку: `CartController@sync()`
и `merge()` создают позицию, не сверяясь с `products.stock`.

**Что делать:** оборачивать оформление в проверку и уменьшать `stock`
в той же транзакции (`Product::where('id', ...)->where('stock', '>=', $qty)->decrement('stock')`).

### 14. Цена в корзине не актуализируется

**Где:** `database/migrations/..._create_cart_items_table.php`,
`OrderController@store:85`

`cart_items.price` — копия `products.price` на момент добавления. Сумма заказа
считается как `$item->price * $item->quantity`, то есть по старой цене.
Если цену товара подняли, пока он лежал в корзине, покупатель заплатит прежнюю.

**Что делать:** пересчитывать позиции из `products.price` в момент оформления,
а `cart_items.price` трактовать как справочный «цена на момент добавления».

### 15. Количество товара всегда 1

**Где:** `CartController@sync:87-92`, `products/index.blade.php:152-197`

Кнопка «Добавить в корзину» переключает состояние: добавление или удаление.
Количество не задаётся пользователем и при синхронизации принудительно
ставится `1`; накопить больше можно только через `merge`.

Поле `quantity` в таблице есть, валидации и контроля на нём нет, а `cart-badge`
в шапке показывает число позиций, а не суммарное количество.

### 16. Нет валидации загружаемого изображения

**Где:** `app/Http/Requests/ProductStoreRequest.php`,
`ProductUpdateRequest.php`

Поле `image` используется в контроллере, но не описано ни в одном правиле.
Нет ограничений на тип (`image/*`), размер, количество и имя файла.
Оригинальное имя файла сохраняется как есть (`storeAs(...getClientOriginalName())`),
поэтому пробелы, кириллица и коллизии имён приводят к проблемам на диске.
Ресайза и генерации миниатюр нет.

**Что делать:**

```php
'image' => ['nullable', 'image', 'max:2048'],
```

и генерация безопасного имени при сохранении.

### 17. Синхронизация корзины не атомарна

**Где:** `app/Http/Controllers/CartController.php:74-93`

```php
$cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
CartItem::where('cart_id', $cart->id)->delete();   // всё удаляем…
foreach ($productIds as $productId) {
    ...
    CartItem::create([...]);                        // …и создаём заново
}
```

Между `delete()` и `create()` нет транзакции: при ошибке корзина остаётся пустой.
Два параллельных запроса от одного пользователя также могут перемешать позиции
и продублировать их. Плюс N+1-запрос `Product::find()` в цикле.

**Что делать:** обернуть в `DB::transaction`, заменить `find()` в цикле на
`Product::whereIn('id', $productIds)->get()->keyBy('id')`, вставлять пачкой.

### 18. userSeeder с маленькой буквы — сломается на Linux

**Где:** `database/seeders/DatabaseSeeder.php:21`

```php
$this->call([
    CategorySeeder::class,
    ProductSeeder::class,
    userSeeder::class,      // ← с маленькой буквы
]);
```

`::class` не вызывает автозагрузку, а Laravel резолвит имя через контейнер.
На Windows файловая система регистронезависима, поэтому `UserSeeder.php`
находится и всё работает. На Linux autoload ищет `database/seeders/userSeeder.php`,
не находит и падает.

**Что делать:** `UserSeeder::class`. Заодно стоит исправить имя файла миграции
`2026_10_06_085316_create_categoties_table.php` → `..._create_categories_table.php`.

### 19. Нет смены статуса заказа

Статусы `new`, `processing`, `done`, `canceled` определены в
`orders.status` и в логике отображения (`match` в
`admin/orders/index.blade.php:37` и `show.blade.php:13`), но ни маршрута,
ни метода, ни формы для их изменения нет — заказ навсегда остаётся `new`.
Поле `order_items.status` дублирует статус заказа и не используется вовсе.

### 20. Тестовое покрытие минимальное

Проверено: `php artisan test` — **11 тестов, все проходят**.

Покрыто: редирект главной страницы, открытие каталога, все четыре ветки
`IsAdmin` (гость HTML, гость JSON, пользователь, администратор), удаление товара
вместе с его изображениями, фильтры каталога (наличие, поиск вместе с категорией,
пустые значения фильтров).

Не покрыто: расчёт суммы заказа, слияние гостевой и серверной корзины,
регистрация и вход, загрузка изображений, админские формы, фильтрация товаров
в публичном каталоге.

### 21. Нет миграций sessions, cache, jobs

В проекте 8 миграций — только доменные таблицы. Нет `sessions`, `jobs`,
`cache`, `cache_locks`, `password_reset_tokens`. При этом `.env.example`
содержит `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`,
`CACHE_STORE=database`, а рабочий `.env` отличается — там `SESSION_DRIVER=file`.

Пока драйвер файловый, всё работает. Стоит переключить на `database` — упадёт
с «no such table».

**Что делать:** привести `.env.example` в соответствие с реальностью
(`SESSION_DRIVER=file`) либо добавить недостающие миграции.

### 22. Опечатки и мелкие расхождения

| Где | Что |
| --- | --- |
| `app/Models/ProductImage.php:15-18` | связь называется `user()`, но возвращает `belongsTo(Product::class)` |
| `app/Models/Cart.php:16` | `$this->BelongsTo(User::class)` — с большой буквы (работает: имена методов в PHP регистронезависимы) |
| `database/migrations/..._create_categoties_table.php` | `categoties` вместо `categories` |
| `down()` в миграциях категорий и товаров | `Schema::dropIfExists('Category')`, `'Product'` — регистр не совпадает с `up()` |
| `app/Models/Product.php:9-12` | неиспользуемые `use` (`Request` используется, `HasOne` — нет) |
| `app/Http/Controllers/Auth/Login.php:93-98` | закомментированный метод `GetCarts` |
| `app/Http/Controllers/Users/UserController.php:6,9` | неиспользуемые `use` (`Request`, `Hash` — `Hash` используется) |
| `app/Http/Requests/ProductStoreRequest.php:8` | объявлено правило `authorize()`, возвращающее `auth()->check()` — форма регистрации его использует корректно |
| `composer.json:18` | в `require-dev` есть `laravel/pao` — нестандартный пакет, в коде не используется |
| `.env.example` | `APP_LOCALE=en`, при этом весь интерфейс и сообщения валидации на русском |
| `resources/js/app.js` | содержит только `//` — файл-заглушка |

### 23. Не реализовано

- **Страница товара** — `products.show` отдаёт пустую страницу (200, 0 байт),
  `show` и view отсутствуют.
- **Смена статуса заказа** — см. проблему 19.
- **Управление пользователями** — ресурс `/users` на 80 % заглушек.
- **Поиск по каталогу для посетителя** — `search` работает только в админке;
  публичный `products.index` фильтрует лишь по категории.
- **Количество товара в корзине** — см. проблему 15.
- **Восстановление пароля и подтверждение почты** — нет токенов и писем,
  `MustVerifyEmail` закомментирован.
- **Vite/Tailwind** — настроены, но не подключены (см. [development.md](development.md#фронтенд)).
- **Сид изображений товаров** — `no-image.jpg` и файлы в `images/` не в Git,
  создаются вручную.

### 24. UserFactory несовместима со схемой users

**Где:** `database/factories/UserFactory.php`,
`database/migrations/2026_10_06_144453_create_users_table.php`

Фабрика выдаёт `email_verified_at` и `remember_token`, но в таблице `users`
таких колонок нет: таблица написана в проекте вручную, а не взята из стандартной
миграции Laravel. При этом `phone` объявлена `NOT NULL UNIQUE` и фабрикой не
задаётся. Итог — `User::factory()->create()` падает всегда:

```
SQLSTATE[HY000]: General error: 1 table users has no column named email_verified_at
```

Из-за этого фабрикой нельзя воспользоваться ни в одном тесте, и пользователей
приходится создавать вручную — так сделано в `tests/Feature/AdminAccessTest.php`
и `tests/Feature/ProductAdminTest.php`.

Обнаружено при написании тестов на middleware; проверено запуском.

**Что делать:** привести `definition()` в соответствие со схемой — убрать
`email_verified_at` и `remember_token`, добавить `phone` (через `fake()`)
и `is_admin` (по умолчанию `false`).

## Полезные команды для проверки

```bash
php artisan test                          # 11 тестов, все зелёные
php artisan route:list --except-vendor    # 33 маршрута
php artisan serve --port=8123             # затем curl по маршрутам выше
```

SQL-проверка scope-фильтра:

```powershell
php artisan tinker --execute="$r = Illuminate\Http\Request::create('/admin/dashboard?stock=0','GET'); dump(App\Models\Product::Filters($r)->toSql());"
```