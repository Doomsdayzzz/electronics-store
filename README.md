# ЦифроStore

Интернет-магазин электроники на Laravel 13: каталог с категориями и фильтрами, корзина
(работает и без авторизации, и с ней), оформление заказов, админ-панель.

Проект учебно-портфолийный: вся бизнес-логика — в контроллерах, без сервисного слоя
и без тестов домена. Подробности и честный список недоработок — в
[docs/known-issues.md](docs/known-issues.md).

## Возможности

- **Каталог** — пагинация (12 товаров на страницу), фильтр по категории, отображение
  цены, старой цены и остатка; товар без остатка помечается «Нет в наличии».
- **Регистрация и вход** — через модальные окна в шапке, AJAX-отправка без перезагрузки.
- **Корзина** — гостевая хранится в `localStorage`, пользовательская — в БД
  (`carts` / `cart_items`); при входе гостевая корзина автоматически сливается
  с серверной.
- **Заказы** — оформление из корзины в одной транзакции, список своих заказов,
  карточка заказа, админский список всех заказов.
- **Админ-панель** — дашборд с поиском и фильтрами по товарам, CRUD товаров с
  загрузкой фотографий, список пользователей.
- **Роли** — пользователь и администратор, различаются полем `users.is_admin`.

## Стек

| Слой | Технология |
| --- | --- |
| Язык | PHP `^8.3` (проверено на 8.3.33) |
| Фреймворк | Laravel `^13.17` |
| БД | SQLite (`database/database.sqlite`) |
| Шаблоны | Blade |
| Сборка | Vite `^8`, Tailwind CSS `^4` (см. предупреждение ниже) |
| Авторизация | сессии, middleware `App\Http\Middleware\IsAdmin` |
| Тесты | PHPUnit `^12.5` |
| Стиль кода | Laravel Pint |

> **Важно про фронтенд.** Vite и Tailwind подключены и собираются, но **ни один шаблон
> не содержит директиву `@vite`**. Вся вёрстка и весь JavaScript написаны инлайном прямо
> в `.blade.php` (в `layouts/main.blade.php` — около 560 строк CSS и 160 строк JS).
> Запуск `npm run dev` / `npm run build` на текущий результат не влияет.
> Подробнее — [docs/development.md](docs/development.md#фронтенд).

## Требования

- PHP 8.3+ с расширениями `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`
- Composer 2.x
- Node.js 20+ и npm (только если планируется работа с ассетами — см. предупреждение выше)
- Расширение GD или Imagick не требуется — загрузка изображений идёт без ресайза

## Быстрый старт

```powershell
# 1. Зависимости PHP
composer install

# 2. Окружение
copy .env.example .env
php artisan key:generate

# 3. Файл базы данных (в git не попадает — см. database/.gitignore)
New-Item -ItemType File database\database.sqlite

# 4. Схема и демо-данные
php artisan migrate
php artisan db:seed

# 5. Симлинк public/storage → storage/app/public (без него не покажутся фото товаров)
php artisan storage:link

# 6. Запуск
php artisan serve            # http://localhost:8000
```

Всё в одну команду есть в `composer setup`, но учтите: он выполняет
`migrate --force`, собирает ассеты (которые не используются) и требует `npm`.

## Демо-доступы

Создаются сидером `UserSeeder`:

| Роль | Email | Пароль |
| --- | --- | --- |
| Пользователь | `user1@ya.ru` | `1234` |
| Администратор | `admin1@ya.ru` | `132456` |

Сидер также наполняет каталог: 7 категорий и 10 товаров (смартфоны, ноутбуки,
наушники, планшеты, колонки, фотокамеры, клавиатуры).

## Команды

| Команда | Что делает |
| --- | --- |
| `php artisan serve` | Встроенный веб-сервер на `:8000` |
| `php artisan migrate` | Применить миграции |
| `php artisan migrate:fresh --seed` | Пересоздать БД и заполнить демо-данными |
| `php artisan db:seed` | Только сидеры |
| `php artisan storage:link` | Создать симлинк `public/storage` |
| `php artisan route:list` | Список маршрутов |
| `php artisan test` | PHPUnit |
| `vendor/bin/pint` | Форматирование кода по стилю Laravel |
| `vendor/bin/pail` | Просмотр логов в терминале |
| `php artisan tinker` | REPL |
| `composer dev` | `php artisan dev` (dev-команда проекта) |

## Структура проекта

```
app/
  Http/Controllers/
    ProductController.php      каталог + CRUD товаров
    CartController.php         AJAX-корзина (sync / remove / merge / getById / guest-details)
    CartPageController.php     страница корзины
    OrderController.php        оформление и просмотр заказов
    Auth/Login.php             вход, выход, слияние гостевой корзины
    Auth/Register.php          регистрация (invokable)
    Admin/DashboardController.php  админ-дашборд
    Users/UserController.php   REST-заготовка пользователей (реализован только store)
  Http/Requests/               валидация форм
  Http/Middleware/IsAdmin.php  проверка is_admin
  Models/                      Category, Product, ProductImage, User, Cart, CartItem, Order, OrderItem
database/
  migrations/                  8 миграций (плюс sessions/cache/jobs отсутствуют)
  seeders/                     CategorySeeder, ProductSeeder, UserSeeder
resources/views/
  layouts/main.blade.php       шапка, модалки входа/регистрации, скрипт счётчика корзины
  products/index.blade.php     каталог + логика кнопки «в корзину»
  cart/index.blade.php         страница корзины и оформление заказа
  orders/                      список и карточка заказов пользователя
  admin/                       дашборд и заказы администратора
routes/web.php                 все маршруты (32)
```

## Документация

| Документ | О чём |
| --- | --- |
| [docs/architecture.md](docs/architecture.md) | Слои, доменная модель, поток запросов, устройство корзины, права |
| [docs/database.md](docs/database.md) | Схема БД: 8 таблиц, поля, связи, сидеры |
| [docs/routes.md](docs/routes.md) | Полный справочник маршрутов и JSON-эндпоинтов |
| [docs/development.md](docs/development.md) | Настройка, команды, соглашения, добавление фич |
| [docs/known-issues.md](docs/known-issues.md) | Найденные баги и недоработки — **прочитайте перед правками** |

Для ИИ-агентов: правила работы с проектом — в [AGENTS.md](AGENTS.md).

## Тесты

```bash
php artisan test
```

На данный момент проходит 1 тест из 2: `tests/Feature/ExampleTest.php` проверяет
ответ `200` на `/`, но такого маршрута в проекте нет, поэтому падает с 404.
Описание причин — в [docs/known-issues.md](docs/known-issues.md#1-нет-маршрута-home).