<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'category_id' => 1,
                'name' => 'Смартфон Samsung Galaxy S23',
                'description' => 'Флагманский смартфон с AMOLED дисплеем, камерой 108 Мп и батареей 3900 мАч',
                'price' => 79990,
                'old_price' => 89990,
                'stock' => 25,
            ],
            [
                'category_id' => 1,
                'name' => 'Смартфон iPhone 15 Pro',
                'description' => 'Премиальный смартфон с чипом A17 Pro, титановым корпусом и системой камер Pro',
                'price' => 129990,
                'old_price' => 139990,
                'stock' => 18,
            ],
            [
                'category_id' => 2,
                'name' => 'Ноутбук ASUS ROG Strix G16',
                'description' => 'Игровой ноутбук с процессором Intel Core i9, RTX 4070, 16 ГБ ОЗУ и SSD 1 ТБ',
                'price' => 159990,
                'old_price' => 179990,
                'stock' => 12,
            ],
            [
                'category_id' => 2,
                'name' => 'Ноутбук MacBook Air M3',
                'description' => 'Ультратонкий ноутбук с чипом Apple M3, 16 ГБ ОЗУ и SSD 512 ГБ',
                'price' => 149990,
                'old_price' => null,
                'stock' => 20,
            ],
            [
                'category_id' => 3,
                'name' => 'Наушники Sony WH-1000XM5',
                'description' => 'Беспроводные наушники с активным шумоподавлением, 30 часов работы',
                'price' => 29990,
                'old_price' => 34990,
                'stock' => 40,
            ],
            [
                'category_id' => 3,
                'name' => 'Наушники AirPods Pro 2',
                'description' => 'Беспроводные наушники с адаптивным шумоподавлением и пространственным звуком',
                'price' => 24990,
                'old_price' => 27990,
                'stock' => 35,
            ],
            [
                'category_id' => 4,
                'name' => 'Планшет iPad Air M2',
                'description' => 'Универсальный планшет с чипом M2, дисплеем 11 дюймов и поддержкой Apple Pencil',
                'price' => 59990,
                'old_price' => 64990,
                'stock' => 15,
            ],
            [
                'category_id' => 5,
                'name' => 'Умная колонка Яндекс Станция Макс',
                'description' => 'Умная колонка с Алисой, LED-дисплеем, мощным звуком и ZigHub',
                'price' => 29990,
                'old_price' => 32990,
                'stock' => 30,
            ],
            [
                'category_id' => 6,
                'name' => 'Фотокамера Canon EOS R6 Mark II',
                'description' => 'Полнокадровая беззеркальная камера с матрицей 24.2 Мп, 4K 60fps и стабилизацией',
                'price' => 199990,
                'old_price' => 219990,
                'stock' => 8,
            ],
            [
                'category_id' => 7,
                'name' => 'Механическая клавиатура Keychron Q1 Pro',
                'description' => 'Проводная/беспроводная клавиатура с переключателями Gateron, RGB подсветкой и алюминиевым корпусом',
                'price' => 14990,
                'old_price' => null,
                'stock' => 50,
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }
    }
}
