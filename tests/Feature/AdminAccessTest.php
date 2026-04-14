<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_admin_area_redirects_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_non_admin_gets_403_on_admin_area(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->get('/admin/categories')->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')->assertOk();
    }
}
