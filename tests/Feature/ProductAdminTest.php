<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ProductAdminTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'Администратор',
            'email' => 'admin-test@ya.ru',
            'password' => 'secret1234',
            'phone' => '8-999-000-00-01',
            'is_admin' => true,
        ]);
    }

    private function makeProduct(array $attributes = []): Product
    {
        $category = Category::firstOrCreate(['id' => 1], ['name' => 'Смартфоны']);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Товар',
            'description' => 'Описание',
            'price' => 1000,
            'stock' => 5,
        ], $attributes));
    }

    private function filtersFor(string $query): Collection
    {
        return Product::Filters(Request::create('/admin/dashboard?'.$query, 'GET'))->get();
    }

    /** При удалении товара записи его изображений тоже должны удаляться */
    public function test_product_images_are_deleted_with_the_product(): void
    {
        $admin = $this->makeAdmin();

        $product = $this->makeProduct();
        ProductImage::create([
            'product_id' => $product->id,
            'path' => '/storage/images/'.$product->id.'/photo.jpg',
            'is_main' => true,
        ]);
        ProductImage::create([
            'product_id' => $product->id,
            'path' => '/storage/images/'.$product->id.'/photo2.jpg',
            'is_main' => false,
        ]);

        $this->actingAs($admin)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_images', ['product_id' => $product->id]);
    }

    /** Значение 0 в фильтре «Нет в наличии» должно применяться */
    public function test_stock_filter_accepts_zero(): void
    {
        $inStock = $this->makeProduct(['name' => 'В наличии', 'stock' => 5]);
        $outOfStock = $this->makeProduct(['name' => 'Нет в наличии', 'stock' => 0]);

        $ids = $this->filtersFor('stock=0')->pluck('id')->all();

        $this->assertSame([$outOfStock->id], $ids);
        $this->assertNotContains($inStock->id, $ids);

        $ids = $this->filtersFor('stock=1')->pluck('id')->all();

        $this->assertSame([$inStock->id], $ids);
    }

    /** Пустой фильтр не должен ничего ограничивать */
    public function test_empty_filters_do_not_restrict_results(): void
    {
        $this->makeProduct(['name' => 'Первый']);
        $this->makeProduct(['name' => 'Второй']);

        $this->assertCount(2, $this->filtersFor('search=&category_id=&stock='));
    }

    /** Поиск и категория должны объединяться через AND, а не через OR */
    public function test_search_is_combined_with_category_filter(): void
    {
        $phone = Category::firstOrCreate(['id' => 1], ['name' => 'Смартфоны']);
        $laptop = Category::firstOrCreate(['id' => 2], ['name' => 'Ноутбуки']);

        $matching = $this->makeProduct([
            'category_id' => $phone->id,
            'name' => 'iPhone 15',
            'description' => 'Описание',
        ]);
        $otherCategory = $this->makeProduct([
            'category_id' => $laptop->id,
            'name' => 'iPhone-ноутбук',
            'description' => 'Описание',
        ]);

        $ids = $this->filtersFor('search=iPhone&category_id=1')->pluck('id')->all();

        $this->assertSame([$matching->id], $ids);
        $this->assertNotContains($otherCategory->id, $ids);
    }
}
