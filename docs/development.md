# Разработка

## Настройка с нуля

```powershell
composer install
copy .env.example .env
php artisan key:generate
New-Item -ItemType File database\database.sqlite
php artisan migrate --seed
php artisan storage:link
```

Две вещи, которые легко пропустить:

- `database/database.sqlite` **не в репозитории** (`database/.gitignore`
  игнорирует `*.sqlite*`). Без файла `migrate` упадёт.
- `public/storage` — симлинк, он **не в репозитории**
  (`.gitignore`: `/public/storage`). Без `storage:link` в каталоге
  не покажутся фотографии товаров.

Проверить, что всё на месте:

```bash
php artisan about
php artisan route:list --except-vendor
php artisan db:show
```

## Требуемое окружение

| Компонент | Версия |
| --- | --- |
| PHP | 8.3+ |
| Composer | 2.x |
| Node.js | 20+ (нужен только для ассетов, см. «Фронтенд») |

Проверено на PHP 8.3.33 (CLI, Windows) и Composer 2.10.3.

Возможные проблемы на Windows:

- симлинк `public/storage` требует прав администратора или Developer Mode
  (иначе `php artisan storage:link` завершится ошибкой);
- `vendor/bin/pint` может не запускаться из PowerShell — используйте
  `php vendor/bin/pint`.

## Команды

### Композитора

```bash
composer install          # зависимости
composer test             # config:clear + php artisan test
composer setup            # полная инициализация нового клона
composer dev              # php artisan dev
```

`composer setup` выполняет `composer install`, копирует `.env`, генерирует ключ,
запускает `migrate --force`, ставит npm-пакеты и собирает ассеты. Ассеты
собирать не нужно — см. «Фронтенд».

### Artisan

```bash
php artisan serve                  # :8000
php artisan migrate                # применить миграции
php artisan migrate:fresh --seed   # пересоздать и наполнить
php artisan db:seed
php artisan storage:link
php artisan route:list
php artisan route:list --path=cart
php artisan optimize:clear         # сбросить кэш конфигуров и видов
php artisan tinker
php artisan test
php artisan test --filter=OrderTest
```

### Форматирование и логи

```bash
vendor/bin/pint                    # отформатировать всё
vendor/bin/pint --test             # только проверить
vendor/bin/pint app/Models         # только каталог
vendor/bin/pail                    # логи в реальном времени
```

Хвост логов: `storage/logs/laravel.log`.

## Фронтенд

**Vite и Tailwind подключены, но не используются.** Ни один шаблон не содержит
директиву `@vite`; `resources/js/app.js` пуст (содержит только `//`), а
`resources/css/app.css` — только `@import 'tailwindcss';`. Вся вёрстка,
стили и скрипты написаны инлайном в Blade-шаблонах.

Практические следствия:

- `npm run dev` / `npm run build` не влияют на страницы;
- чтобы посмотреть результат правок в Blade, достаточно обновить страницу;
- `vite.config.js` подключает плагин шрифтов (`bunny('Instrument Sans')`),
  но сами шрифты не подключаются — CSS использует `Segoe UI, Tahoma, ...`;
- шаблоны большие: `admin/dashboard.blade.php` — 956 строк,
  `layouts/main.blade.php` — 853, `cart/index.blade.php` — 849.

При работе со стилями учитывайте, что одинаковые классы (`.form-input`,
`.form-submit-btn`, `.btn`) переопределены в нескольких шаблонах независимо.

## Тесты

```bash
php artisan test
```

Состояние на текущий момент: **11 тестов, все проходят**.

| Файл | Что проверяет |
| --- | --- |
| `tests/Feature/ExampleTest.php` | редирект `/` → каталог и открытие каталога |
| `tests/Feature/AdminAccessTest.php` | все четыре ветки `IsAdmin`: гость (HTML и JSON), пользователь, администратор |
| `tests/Feature/ProductAdminTest.php` | удаление товара вместе с изображениями, фильтры каталога |
| `tests/Unit/ExampleTest.php` | заглушка `assertTrue(true)` |

Тесты пишутся на PHPUnit 12 (`phpunit.xml`), база — SQLite `:memory:`
(`DB_DATABASE=:memory:`), поэтому рабочая БД не затрагивается.
Значения окружения для тестов заданы в `phpunit.xml`: `CACHE_STORE=array`,
`SESSION_DRIVER=array`, `QUEUE_CONNECTION=sync`.

Любой тест, который ходит в базу, обязан использовать `RefreshDatabase` —
иначе таблиц в `:memory:` не будет.

> **Осторожно с фабриками.** `User::factory()->create()` не работает: фабрика
> выдаёт `email_verified_at` и `remember_token`, которых нет в таблице `users`,
> и не задаёт обязательную `phone`. Пользователей в тестах создают напрямую
> через `User::create([...])` — так сделано в `AdminAccessTest` и
> `ProductAdminTest`. Причина — в
> [known-issues.md](known-issues.md#24-userfactory-несовместима-со-схемой-users).

Чтобы добавить тест:

```bash
php artisan make:test ProductFilterTest
php artisan make:test --unit CartTotalTest
```

## Соглашения проекта

Это код учебного проекта — придерживайтесь того, что в нём уже есть,
и не привносите стандарты, которые здесь не используются.

**Модели.** Атрибут `#[Fillable]` вместо свойства `$fillable`, `HasFactory`
на моделях, `casts()` как метод (не свойство):

```php
#[Fillable(['id', 'category_id', 'name', 'description', 'price', 'stock', 'old_price'])]
class Product extends Model
{
    use HasFactory;

    public function category(): BelongsTo { /* ... */ }
}
```

**Валидация.** `FormRequest` в `app/Http/Requests`, сообщения об ошибках
на русском. `authorize()` возвращает `auth()->check() && auth()->user()->is_admin`
для админских операций — не дублируйте эту проверку в контроллере.

**Права.** Для админских маршрутов — `->middleware(IsAdmin::class)`.
Для проверки прав на конкретную запись — `abort_unless(...)`.

**Ответы.** Страницы отдают `view(...)`, AJAX-операции — `response()->json([...])`
с явными кодами (`401`, `422`). Редиректы — `redirect()->route('...')`
с `->with('success', 'текст')`.

**Имена.** Контроллеры в единственном числе (`ProductController`), но
`IsAdmin` — прилагательное, `Login` и `Register` — существительные без суффикса.
JSON-эндпоинты корзины — в единственном (`/cart/sync`, `/cart/remove`),
товарные — во множественном (`/products`). Сохраняйте эту схему.

**Комментарии и тексты интерфейса** — на русском.

## Как добавить фичу

**Новый маршрут.** Объявите в `routes/web.php` сразу после `use`-импорта
контроллера, внутри нужной группы middleware:

```php
Route::middleware('auth')->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
});
```

**Новую модель и таблицу:**

```bash
php artisan make:model Review -m
php artisan make:migration create_reviews_table
php artisan make:factory ReviewFactory
php artisan make:seeder ReviewSeeder
php artisan migrate --seed
```

Не забудьте добавить поле в `#[Fillable]`, связи, а сидер — в
`DatabaseSeeder::run()`.

**Админский раздел:** контроллер в `app/Http/Controllers/Admin`, view в
`resources/views/admin`, маршрут под `IsAdmin`, а для записи — ещё и
`authorize()` в `FormRequest`.

**Страницу товара.** Маршрут `products.show` зарегистрирован, но метод
`ProductController@show` пуст, а view `resources/views/products/show.blade.php`
отсутствует. Сейчас `/products/{id}` отдаёт `200` с пустым телом — страница
нужна для реализации метода. Учтите, что пустая страница товара — это 200,
а не 404, поэтому дефект легко не заметить.

## Работа с изображениями

Файлы пишутся на диск `public` в `images/{product_id}/` под оригинальным именем,
в БД сохраняется путь с `/storage/`:

```php
$path = $request->file('image')->storeAs(
    'images/' . $product->id,
    $request->file('image')->getClientOriginalName(),
    'public'
);

ProductImage::create([
    'product_id' => $product->id,
    'path'       => '/storage/' . $path,
    'is_main'    => true,
]);
```

При замене картинки сначала удаляется вся папка и все записи таблицы.
Валидации файла в `ProductStoreRequest` и `ProductUpdateRequest` нет —
`image` не описан ни в одном правиле, ограничений на тип и размер нет.

Заглушка для товара без фото: `storage/app/public/images/no-image.jpg`
(в Git не отслеживается, см. [database.md](database.md)).

## Git

Ветка — `master`. История коммитов на русском языке, в стиле
«добавлена админка», «1. Добавлены пользователи».

```bash
git status
git add -A
git commit -m "добавлен раздел избранного"
```

В `.gitignore` под обычными каталогами Laravel есть `/auth.json`,
`/.codex`, `/.cursor`, `/.zed` — список инструментов, с которыми проект
работал раньше.

Перед коммитом полезно прогнать Pint и тесты:

```bash
vendor/bin/pint --test
php artisan test
```

## Проверка перед сдачей

```bash
vendor/bin/pint --test          # стиль
php artisan test                # тесты
php artisan route:list --except-vendor
php artisan optimize:clear
```

`vendor/bin/pint --test` по всему проекту сейчас падает: 16 файлов не
отформатированы по стилю Pint (`Login`, `Register`, `CartController`,
`CartPageController`, `OrderController`, `UserController`, три `FormRequest`,
`Cart`, `Category`, `OrderItem` и все сидеры). Это существующий долг, не связанный
с текущими задачами. Чтобы проект проходил проверку целиком:

```bash
vendor/bin/pint
```

Но учтите: это переформатирует много файлов разом — обсудите перед тем как
запускать на общей ветке.

## Куда смотреть дальше

- [architecture.md](architecture.md) — как всё устроено
- [database.md](database.md) — схема и сидеры
- [routes.md](routes.md) — маршруты и форматы JSON
- [known-issues.md](known-issues.md) — что уже сломано, читать до правок