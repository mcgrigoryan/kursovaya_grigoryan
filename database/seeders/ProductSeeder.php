<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Освежитель воздуха "Лесной"',
                'category' => 'Для дома',
                'cost_price' => 120.00,
                'sale_price' => 250.00,
                'stock' => 50,
            ],
            [
                'name' => 'Ароматизатор "Цитрус"',
                'category' => 'Для автомобиля',
                'cost_price' => 80.00,
                'sale_price' => 180.00,
                'stock' => 30,
            ],
            [
                'name' => 'Спрей для мебели "Лаванда"',
                'category' => 'Для дома',
                'cost_price' => 95.00,
                'sale_price' => 220.00,
                'stock' => 45,
            ],
            [
                'name' => 'Подвеска автомобильная "Хвойный лес"',
                'category' => 'Для автомобиля',
                'cost_price' => 65.00,
                'sale_price' => 150.00,
                'stock' => 60,
            ],
            [
                'name' => 'Освежитель "Морская свежесть"',
                'category' => 'Для дома',
                'cost_price' => 110.00,
                'sale_price' => 240.00,
                'stock' => 25,
            ],
        ];

        foreach ($products as $data) {
            Product::create($data);
        }
    }
}
