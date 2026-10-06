<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['id' => '1', 'name' => 'Смартфоны'],
            ['id' => '2', 'name' => 'Ноутбуки'],
            ['id' => '3', 'name' => 'Наушники'],
            ['id' => '4', 'name' => 'Планшеты'],
            ['id' => '5', 'name' => 'Беспроводные колонки'],
            ['id' => '6', 'name' => 'Фотокамеры'],
            ['id' => '7', 'name' => 'Клавиатуры']
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
