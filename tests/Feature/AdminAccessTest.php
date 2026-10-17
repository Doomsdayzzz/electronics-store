<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Фабрика UserFactory несовместима со схемой users (email_verified_at,
     * remember_token), поэтому создаём пользователей напрямую.
     */
    private function makeUser(bool $isAdmin, string $phone): User
    {
        return User::create([
            'name' => $isAdmin ? 'Администратор' : 'Пользователь',
            'email' => $isAdmin ? 'admin-test@ya.ru' : 'user-test@ya.ru',
            'password' => 'secret1234',
            'phone' => $phone,
            'is_admin' => $isAdmin,
        ]);
    }

    public function test_guest_is_redirected_from_admin_pages(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('products.index'));
    }

    public function test_guest_gets_401_when_json_is_expected(): void
    {
        $this->getJson(route('admin.dashboard'))->assertUnauthorized();
    }

    public function test_user_without_admin_role_gets_403(): void
    {
        $user = $this->makeUser(false, '8-999-000-00-01');

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_can_open_admin_pages(): void
    {
        $admin = $this->makeUser(true, '8-999-000-00-02');

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }
}
