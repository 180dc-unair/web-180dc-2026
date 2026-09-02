<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EventCategoryApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::query()->create([
            'role' => 'admin',
            'name' => 'Admin',
            'username' => 'admin-ecat',
            'email' => 'admin-ecat@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    private function user(): User
    {
        return User::query()->create([
            'role' => 'user',
            'name' => 'User',
            'username' => 'user-ecat',
            'email' => 'user-ecat@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_public_can_list_and_show(): void
    {
        EventCategory::create(['name' => 'Workshop', 'slug' => 'workshop']);
        EventCategory::create(['name' => 'Seminar', 'slug' => 'seminar']);

        $this->getJson('/api/event-categories')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);

        $this->getJson('/api/event-categories?search=Workshop')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/event-categories/workshop')
            ->assertOk()
            ->assertJsonPath('data.slug', 'workshop');

        $this->getJson('/api/event-categories/notfound')->assertNotFound();
    }

    public function test_events_by_category(): void
    {
        $cat = EventCategory::create(['name' => 'Workshop', 'slug' => 'workshop']);
        Event::create(['title' => 'E1', 'slug' => 'e1', 'category_id' => $cat->id, 'status' => 'active']);
        Event::create(['title' => 'E2', 'slug' => 'e2', 'category_id' => $cat->id, 'status' => 'active']);

        $this->getJson('/api/event-categories/workshop/events')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_crud(): void
    {
        $admin = $this->admin();
        $create = $this->actingAs($admin)->postJson('/api/event-categories', ['name' => 'New Cat'])
            ->assertCreated()->assertJsonPath('data.slug', 'new-cat');
        $id = $create->json('data.id');

        $this->actingAs($admin)->postJson('/api/event-categories', ['name' => 'New Cat'])->assertUnprocessable();

        $this->actingAs($admin)->patchJson("/api/event-categories/{$id}", ['name' => 'Updated'])
            ->assertOk()->assertJsonPath('data.slug', 'updated');

        $this->actingAs($admin)->deleteJson("/api/event-categories/{$id}")->assertOk();
        $this->getJson('/api/event-categories/updated')->assertNotFound();
    }

    public function test_non_admin_cannot_mutate(): void
    {
        $user = $this->user();
        $this->actingAs($user)->postJson('/api/event-categories', ['name' => 'Hack'])->assertForbidden();
    }

    public function test_pagination(): void
    {
        for ($i = 0; $i < 5; $i++) {
            EventCategory::create(['name' => "Cat $i", 'slug' => "cat-$i"]);
        }
        $this->getJson('/api/event-categories?per_page=2&page=2')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.current_page', 2);
    }
}
