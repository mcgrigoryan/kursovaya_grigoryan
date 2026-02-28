<?php

namespace Database\Seeders;

use App\Models\Operation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OperationSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::where('login', 'manager')->first();
        $products = Product::all();

        if ($products->isEmpty() || !$manager) {
            return;
        }

        $operations = [];
        $now = now();

        // Операции за последние 3 месяца
        for ($i = 0; $i < 12; $i++) {
            $date = $now->copy()->subMonths(2)->subDays(rand(0, 60));
            $product = $products->random();
            $qty = rand(2, 15);
            $type = ['Производство', 'Закупка', 'Продажа'][rand(0, 2)];

            $operations[] = [
                'type' => $type,
                'quantity' => $qty,
                'operation_date' => $date->format('Y-m-d'),
                'product_id' => $product->id,
                'user_id' => $manager->id,
                'created_at' => $date,
            ];
        }

        foreach ($operations as $op) {
            Operation::create($op);
        }

        // Пересчёт остатков после операций из сидов (упрощённо: сиды создают операции, но не обновляют stock)
        foreach ($products as $product) {
            $sold = Operation::where('product_id', $product->id)->where('type', 'Продажа')->sum('quantity');
            $produced = Operation::where('product_id', $product->id)->whereIn('type', ['Производство', 'Закупка'])->sum('quantity');
            $product->stock = $product->stock - $sold + $produced;
            $product->save();
        }
    }
}
