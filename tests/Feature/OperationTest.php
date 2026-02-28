<?php

namespace Tests\Feature;

use App\Models\Operation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            \Database\Seeders\UserSeeder::class,
            \Database\Seeders\ProductSeeder::class,
        ]);
        $this->manager = User::where('login', 'manager')->first();
    }

    public function test_manager_can_add_purchase_operation(): void
    {
        $this->actingAs($this->manager);
        $product = Product::first();
        $stockBefore = $product->stock;

        $response = $this->post(route('operations.store'), [
            'type' => 'Закупка',
            'product_id' => $product->id,
            'quantity' => 5,
            'operation_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('operations.index'));
        $product->refresh();
        $this->assertSame($stockBefore + 5, (int) $product->stock);
        $this->assertDatabaseHas('operations', ['type' => 'Закупка', 'product_id' => $product->id]);
    }

    public function test_manager_can_add_sale_operation_when_stock_sufficient(): void
    {
        $this->actingAs($this->manager);
        $product = Product::first();
        $stockBefore = $product->stock;
        $qty = min(3, $stockBefore);
        if ($qty < 1) {
            $product->update(['stock' => 10]);
            $stockBefore = 10;
            $qty = 5;
        }

        $response = $this->post(route('operations.store'), [
            'type' => 'Продажа',
            'product_id' => $product->id,
            'quantity' => $qty,
            'operation_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('operations.index'));
        $product->refresh();
        $this->assertSame($stockBefore - $qty, (int) $product->stock);
    }

    public function test_sale_fails_when_insufficient_stock(): void
    {
        $this->actingAs($this->manager);
        $product = Product::first();
        $product->update(['stock' => 2]);

        $response = $this->post(route('operations.store'), [
            'type' => 'Продажа',
            'product_id' => $product->id,
            'quantity' => 10,
            'operation_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('quantity');
        $response->assertSessionHasErrorsIn('default', ['quantity' => 'Недостаточно товара на складе']);
        $product->refresh();
        $this->assertSame(2, (int) $product->stock);
    }
}
