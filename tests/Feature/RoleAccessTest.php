<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    public function test_director_cannot_access_product_create(): void
    {
        $director = User::where('login', 'director')->first();
        $this->actingAs($director);

        $response = $this->get(route('products.create'));
        $response->assertStatus(403);
    }

    public function test_manager_can_access_operations(): void
    {
        $manager = User::where('login', 'manager')->first();
        $this->actingAs($manager);

        $response = $this->get(route('operations.index'));
        $response->assertStatus(200);
    }

    public function test_accountant_can_access_reports(): void
    {
        $accountant = User::where('login', 'accountant')->first();
        $this->actingAs($accountant);

        $response = $this->get(route('reports.index'));
        $response->assertStatus(200);
    }

    public function test_accountant_cannot_access_operations_form(): void
    {
        $accountant = User::where('login', 'accountant')->first();
        $this->actingAs($accountant);

        $response = $this->get(route('operations.index'));
        $response->assertStatus(403);
    }

    public function test_director_can_access_logs(): void
    {
        $director = User::where('login', 'director')->first();
        $this->actingAs($director);

        $response = $this->get(route('logs.index'));
        $response->assertStatus(200);
    }

    public function test_manager_cannot_access_logs(): void
    {
        $manager = User::where('login', 'manager')->first();
        $this->actingAs($manager);

        $response = $this->get(route('logs.index'));
        $response->assertStatus(403);
    }
}
