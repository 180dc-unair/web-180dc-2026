<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EventApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::query()->create([
            'role' => 'admin',
            'name' => 'Admin',
            'username' => 'admin-event',
            'email' => 'admin-event@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    private function user(): User
    {
        return User::query()->create([
            'role' => 'user',
            'name' => 'User',
            'username' => 'user-event',
            'email' => 'user-event@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_public_can_list_events_with_pagination_and_filters(): void
    {
        $cat = EventCategory::create(['name' => 'Workshop', 'slug' => 'workshop']);
        Event::create(['title' => 'Event A', 'slug' => 'event-a', 'status' => 'active', 'category_id' => $cat->id, 'is_featured' => false]);
        Event::create(['title' => 'Event B', 'slug' => 'event-b', 'status' => 'active', 'is_featured' => true]);
        Event::create(['title' => 'Draft Event', 'slug' => 'draft-event', 'status' => 'draft']);

        // public sees only active (2)
        $this->getJson('/api/events')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonCount(2, 'data');

        // pagination
        $this->getJson('/api/events?per_page=1&page=1')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);

        // search
        $this->getJson('/api/events?search=Event A')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        // filter by featured
        $this->getJson('/api/events?is_featured=true')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        // filter by category
        $this->getJson('/api/events?category_slug=workshop')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        // admin sees draft with status filter
        $admin = $this->admin();
        $this->actingAs($admin)->getJson('/api/events?status=draft')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_show_event(): void
    {
        $event = Event::create(['title' => 'Show Event', 'slug' => 'show-event', 'status' => 'active']);
        $this->getJson("/api/events/{$event->slug}")
            ->assertOk()
            ->assertJsonPath('data.slug', 'show-event');

        $draft = Event::create(['title' => 'Draft', 'slug' => 'draft-show', 'status' => 'draft']);
        $this->getJson("/api/events/{$draft->slug}")->assertNotFound();

        $admin = $this->admin();
        $this->actingAs($admin)->getJson("/api/events/{$draft->slug}")->assertOk();
    }

    public function test_admin_can_crud_event(): void
    {
        $admin = $this->admin();
        $cat = EventCategory::create(['name' => 'Seminar', 'slug' => 'seminar']);
        $media = MediaAsset::create(['file_id' => 'img123', 'url' => 'https://example.com/img.jpg']);

        $create = $this->actingAs($admin)->postJson('/api/events', [
            'title' => 'My Event',
            'category_id' => $cat->id,
            'image_id' => $media->id,
            'description' => 'desc',
            'location' => 'Surabaya',
            'start_at' => now()->addDay()->toISOString(),
            'end_at' => now()->addDays(2)->toISOString(),
            'gdoc_url' => 'https://docs.google.com/test',
            'status' => 'active',
            'price' => 100000,
            'is_paid' => true,
            'is_featured' => true,
        ])->assertCreated()->assertJsonPath('data.title', 'My Event');
        $id = $create->json('data.id');
        $slug = $create->json('data.slug');

        // update
        $this->actingAs($admin)->patchJson("/api/events/{$id}", ['title' => 'Updated Event', 'is_featured' => false])
            ->assertOk()->assertJsonPath('data.title', 'Updated Event');

        // slug unique
        $this->actingAs($admin)->postJson('/api/events', ['title' => 'Updated Event'])->assertUnprocessable();

        // delete
        $this->actingAs($admin)->deleteJson("/api/events/{$id}")->assertOk();
        $this->getJson("/api/events/{$slug}")->assertNotFound();
    }

    public function test_validation_and_auth(): void
    {
        $user = $this->user();
        $this->actingAs($user)->postJson('/api/events', ['title' => 'Hack'])->assertForbidden();

        $admin = $this->admin();
        $this->actingAs($admin)->postJson('/api/events', ['title' => 'Bad', 'gdoc_url' => 'not-url'])->assertUnprocessable();
        $this->actingAs($admin)->postJson('/api/events', ['title' => 'Bad', 'end_at' => now()->toISOString(), 'start_at' => now()->addDay()->toISOString()])->assertUnprocessable();
    }
}
