<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    public function test_login_form_displays(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Логин');
        $response->assertSee('Пароль');
    }

    public function test_successful_login_redirects_to_dashboard(): void
    {
        $response = $this->post('/login', [
            'login' => 'manager',
            'password' => 'manager123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_failed_login_shows_error(): void
    {
        $response = $this->post('/login', [
            'login' => 'manager',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('login');
    }

    public function test_logout_redirects_to_login(): void
    {
        $user = User::where('login', 'manager')->first();
        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
