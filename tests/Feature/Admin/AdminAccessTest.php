<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_renders_empty_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Landing/Index', false));
    }

    public function test_guest_can_open_admin_login_page(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Auth/Login', false));
    }

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_can_login_and_open_dashboard_api(): void
    {
        $admin = User::query()->create([
            'role' => 'admin',
            'name' => 'Admin Test',
            'username' => 'admin-test',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
            'remember' => true,
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
        $this->assertNotNull($admin->fresh()?->remember_token);

        $this->get('/admin')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Dashboard/Index', false));

        $this->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => [
                    'metrics' => ['products', 'articles', 'services', 'clients', 'comments'],
                    'recent' => ['articles', 'products', 'comments'],
                    'activity',
                ],
            ]);
    }

    public function test_non_admin_cannot_login_to_admin_dashboard(): void
    {
        $user = User::query()->create([
            'role' => 'user',
            'name' => 'Regular User',
            'username' => 'regular-user',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->from('/admin/login')->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_open_every_admin_management_page(): void
    {
        $admin = User::query()->create([
            'role' => 'admin',
            'name' => 'Admin Pages',
            'username' => 'admin-pages',
            'email' => 'pages@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($admin);

        foreach ([
            'products',
            'product-categories',
            'services',
            'service-categories',
            'clients',
            'articles',
            'article-categories',
            'users',
        ] as $resource) {
            $this->get("/admin/{$resource}")
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('Admin/Resources/Index', false)
                    ->where('resourceKey', $resource));
        }

        $this->get('/admin/comments')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Comments/Index', false));

        $this->getJson('/api/admin/article-comments')
            ->assertOk()
            ->assertJsonPath('status', 'success');
    }
}
