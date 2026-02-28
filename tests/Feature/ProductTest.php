<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([\Database\Seeders\UserSeeder::class, \Database\Seeders\ProductSeeder::class]);
        $this->manager = User::where('login', 'manager')->first();
    }

    public function test_manager_can_create_product(): void
    {
        $this->actingAs($this->manager);

        $response = $this->post(route('products.store'), [
            'name' => 'Новый товар',
            'category' => 'Для дома',
            'cost_price' => 100,
            'sale_price' => 200,
            'stock' => 10,
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'Товар успешно добавлен');
        $this->assertDatabaseHas('products', ['name' => 'Новый товар']);
    }

    public function test_manager_can_update_product(): void
    {
        $this->actingAs($this->manager);
        $product = Product::first();

        $response = $this->put(route('products.update', $product), [
            'category' => 'Для автомобиля',
            'cost_price' => 90,
            'sale_price' => 180,
            'stock' => 20,
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'Товар обновлен');
        $product->refresh();
        $this->assertSame('Для автомобиля', $product->category);
    }

    public function test_manager_can_delete_product(): void
    {
        $this->actingAs($this->manager);
        $product = Product::first();
        $productId = $product->id;

        $response = $this->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }

    public function test_accountant_cannot_access_products_create(): void
    {
        $accountant = User::where('login', 'accountant')->first();
        $this->actingAs($accountant);

        $response = $this->get(route('products.create'));
        $response->assertStatus(403);
    }
}
