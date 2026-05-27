<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StorefrontAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_storefront_styled(): void
    {
        $this->get('/login')->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('loot4/auth/Login'),
        );
    }

    public function test_register_page_renders_storefront_styled(): void
    {
        $this->get('/register')->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('loot4/auth/Register'),
        );
    }

    public function test_user_can_register(): void
    {
        $this->post('/register', [
            'name' => 'Gamer',
            'email' => 'gamer@example.com',
            'password' => 'Sup3rSecret!2026',
            'password_confirmation' => 'Sup3rSecret!2026',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'gamer@example.com']);
        $this->assertAuthenticated();
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Sup3rSecret!2026')]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Sup3rSecret!2026',
        ])->assertRedirect();
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect();
        $this->assertGuest();
    }

    public function test_login_rejects_bad_credentials(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Sup3rSecret!2026')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
