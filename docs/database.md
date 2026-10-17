# Схема базы данных

## Подключение

| Параметр | Значение |
| --- | --- |
| Драйвер | SQLite |
| Файл | `database/database.sqlite` |
| Создаётся | вручную (файл в `.gitignore`) или автоматически при `composer create-project` |

В `.env`:

```dotenv
DB_CONNECTION=sqlite
```

Полный дамп БД в репозитории не хранится — `database/.gitignore` игнорирует
`*.sqlite*`. После клонирования файл нужно создать заново:

```powershell
New-Item -ItemType File database\database.sqlite
php artisan migrate --seed
```

Пользовательские загрузки (`storage/app/public/.gitignore` с правилом `*`)
тоже не коммитятся: в репозитории лежат только `.gitignore` и сама папка
`images/` — при этом `no-image.jpg` в рабочей копии есть, но в Git не отслеживается
и должен быть создан заново при развёртывании.

## Карта таблиц

| Таблица | Назначение |
| --- | --- |
| `users` | пользователи и администраторы, `is_admin` |
| `categories` | категории товаров |
| `products` | товары, цена, остаток |
| `product_images` | изображения товаров, путь и признак основного |
| `carts` | корзина, одна на пользователя |
| `cart_items` | позиции корзины |
| `orders` | заказы, адрес доставки, статус, сумма |
| `order_items` | позиции заказа |

Связи:

```
users ──1:N──> carts ──1:N──> cart_items ──N:1──> products
   │                                                    │
   │                                        categories ──┘ (products.category_id)
   │
   └───1:N──> orders ──1:N──> order_items ──N:1──> products
                                          │
                              product_images ──N:1──> products
```

## Таблицы

### `users`

Создана миграцией `2026_10_06_144453_create_users_table`.

| Колонка | Тип | Ограничения | Комментарий |
| --- | --- | --- | --- |
| `id` | integer | PK | |
| `name` | string | NOT NULL, `isNotEmpty()` | |
| `email` | string | NOT NULL, **UNIQUE** | |
| `password` | string | NOT NULL | хеш bcrypt; каст `password => hashed` |
| `phone` | string | NOT NULL, **UNIQUE** | |
| `is_admin` | boolean | default `false` | признак администратора |
| `created_at`, `updated_at` | timestamp | nullable | |

Обратите внимание: в таблице **нет** `password_reset_tokens`, `remember_token`
и `email_verified_at`. Восстановление пароля и подтверждение почты в проекте
не реализованы; трейт `MustVerifyEmail` в модели `User` закомментирован.

### `categories`

Миграция `2026_10_06_085316_create_categoties_table` (в имени файла опечатка —
`categoties`; на схему не влияет).

| Колонка | Тип | Ограничения |
| --- | --- | --- |
| `id` | integer | PK |
| `name` | string | NOT NULL |
| `created_at`, `updated_at` | timestamp | nullable |

### `products`

Миграция `2026_10_06_085529_create_products_table`.

| Колонка | Тип | Ограничения | Комментарий |
| --- | --- | --- | --- |
| `id` | integer | PK | |
| `category_id` | integer | FK → `categories.id` | `constrained()`, `cascadeOnDelete` не задан |
| `name` | string | NOT NULL | |
| `description` | string | NOT NULL | |
| `price` | decimal(8,2) | NOT NULL | актуальная цена |
| `stock` | integer | default `0` | остаток; в каталоге `0` означает «нет в наличии» |
| `old_price` | decimal(8,2) | nullable | перечёркнутая цена, показывается если `old_price > price` |
| `created_at`, `updated_at` | timestamp | nullable | |

### `product_images`

Миграция `2026_10_06_130409_product_images_table`.

| Колонка | Тип | Ограничения | Комментарий |
| --- | --- | --- | --- |
| `id` | integer | PK | |
| `product_id` | integer | FK → `products.id` | `constrained('products')` |
| `path` | string | nullable | путь **с** `/storage/`, например `/storage/images/8/photo.jpg` |
| `is_main` | boolean | default `false` | всегда `true` у единственной записи на товар |
| `created_at`, `updated_at` | timestamp | nullable | |

Модель поддерживает несколько изображений на товар (связь `hasMany`), но
контроллер всегда создаёт ровно одну запись и заменяет её целиком при
загрузке новой картинки. Поле `is_main` в коде нигде не читается.

### `carts`

Миграция `2026_10_15_180951_create_carts_table`.

| Колонка | Тип | Ограничения | Комментарий |
| --- | --- | --- | --- |
| `id` | integer | PK | |
| `user_id` | integer | FK → `users.id`, **nullable**, `cascadeOnDelete` | `null` заложен под гостевую корзину, но не используется |
| `created_at`, `updated_at` | timestamp | nullable | |

Уникального индекса на `user_id` нет: одна корзина на пользователя
поддерживается только дисциплиной приложения (`Cart::firstOrCreate`).

### `cart_items`

Миграция `2026_10_15_180957_create_cart_items_table`.

| Колонка | Тип | Ограничения | Комментарий |
| --- | --- | --- | --- |
| `id` | integer | PK | |
| `cart_id` | integer | FK → `carts.id` | `cascadeOnDelete` |
| `product_id` | integer | FK → `products.id` | `cascadeOnDelete` |
| `quantity` | unsigned integer | default `1` | |
| `price` | decimal(10,2) | NOT NULL | **цена на момент добавления**, копия `products.price` |
| `created_at`, `updated_at` | timestamp | nullable | |

### `orders`

Миграция `2026_10_17_135354_create_orders_table`.

| Колонка | Тип | Ограничения | Комментарий |
| --- | --- | --- | --- |
| `id` | integer | PK | |
| `user_id` | integer | FK → `users.id` | `onDelete('cascade')` |
| `delivery_address` | string | NOT NULL, `isNotEmpty()` | |
| `comment` | string | nullable, default `null` | |
| `total_price` | decimal(10,2) | default `0` | сумма позиций на момент оформления |
| `status` | string | default `'new'` | `new` / `processing` / `done` / `canceled` |
| `created_at`, `updated_at` | timestamp | nullable | |

### `order_items`

Миграция `2026_10_17_135409_create_order_items_table`.

| Колонка | Тип | Ограничения | Комментарий |
| --- | --- | --- | --- |
| `id` | integer | PK | |
| `order_id` | integer | FK → `orders.id` | `cascadeOnDelete` |
| `product_id` | integer | FK → `products.id` | `cascadeOnDelete` |
| `quantity` | unsigned integer | default `1` | |
| `total_price` | decimal(10,2) | NOT NULL | `price * quantity` на момент оформления |
| `status` | string | default `'new'` | дублирует статус заказа, в коде не используется |
| `created_at`, `updated_at` | timestamp | nullable | |

## Миграции

| Файл | Таблица |
| --- | --- |
| `2026_10_06_085316_create_categoties_table.php` | `categories` |
| `2026_10_06_085529_create_products_table.php` | `products` |
| `2026_10_06_130409_product_images_table.php` | `product_images` |
| `2026_10_06_144453_create_users_table.php` | `users` |
| `2026_10_15_180951_create_carts_table.php` | `carts` |
| `2026_10_15_180957_create_cart_items_table.php` | `cart_items` |
| `2026_10_17_135354_create_orders_table.php` | `orders` |
| `2026_10_17_135409_create_order_items_table.php` | `order_items` |

В `down()` у миграций `categories`, `products` и `users` указаны имена таблиц
в другом регистре (`'Category'`, `'Product'`) — на MySQL из-за регистронезависимых
таблиц это сработает, на PostgreSQL потребует кавычек. На SQLite
`Schema::dropIfExists` регистронезависим.

### Отсутствуют служебные таблицы

В проекте **нет** миграций для `sessions`, `cache`, `cache_locks` и `jobs`.
При этом `.env.example` по умолчанию содержит:

```dotenv
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

Рабочая копия `.env` отличается — там `SESSION_DRIVER=file`, поэтому приложение
поднимается. Если переключить драйвер на `database`, понадобятся соответствующие
таблицы:

```bash
php artisan session:table
php artisan queue:table
php artisan cache:table
php artisan make:migration create_cache_locks_table
```

## Сидеры

Запуск: `php artisan db:seed` (или `migrate:fresh --seed`).

### `CategorySeeder`

7 категорий с фиксированными id — важно, потому что `ProductSeeder` ссылается на
эти id вручную:

| id | Название |
| --- | --- |
| 1 | Смартфоны |
| 2 | Ноутбуки |
| 3 | Наушники |
| 4 | Планшеты |
| 5 | Беспроводные колонки |
| 6 | Фотокамеры |
| 7 | Клавиатуры |

Сидер не идемпотентен — повторный вызов упадёт на дубликатах ключей.

### `ProductSeeder`

10 товаров: по 2 смартфона (Samsung Galaxy S23, iPhone 15 Pro), 2 ноутбука
(ASUS ROG Strix G16, MacBook Air M3), 2 наушника (Sony WH-1000XM5, AirPods Pro 2),
планшет iPad Air M2, колонка Яндекс Станция Макс, фотокамера Canon EOS R6 Mark II,
клавиатура Keychron Q1 Pro. У всех товаров заполнены `price`, `stock`, а у части —
`old_price` для показа зачёркнутой цены.

### `UserSeeder`

Два пользователя через `firstOrCreate`:

| Имя | Email | Пароль | `is_admin` |
| --- | --- | --- | --- |
| user1 | `user1@ya.ru` | `1234` | 0 |
| admin1 | `admin1@ya.ru` | `132456` | 1 |

Пароли в виду репозитория — только для локальной разработки.

## Работа с БД вручную

```bash
# содержимое всех таблиц
php artisan db:table products --json

# схема
php artisan db:show --counts

# пересоздать всё
php artisan migrate:fresh --seed
```