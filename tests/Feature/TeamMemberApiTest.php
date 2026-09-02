<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeamMemberApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::query()->create([
            'role' => 'admin',
            'name' => 'Admin',
            'username' => 'admin-team',
            'email' => 'admin-team@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    private function user(): User
    {
        return User::query()->create([
            'role' => 'user',
            'name' => 'User',
            'username' => 'user-team',
            'email' => 'user-team@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_public_can_list_team_members_with_pagination(): void
    {
        $admin = $this->admin();
        // create via API to ensure flow works
        $this->actingAs($admin)->postJson('/api/team-members', [
            'name' => 'Alice',
            'slug' => 'alice',
            'is_active' => true,
        ])->assertCreated();

        $this->actingAs($admin)->postJson('/api/team-members', [
            'name' => 'Bob',
            'slug' => 'bob',
            'is_active' => true,
        ])->assertCreated();

        // public list with pagination
        $this->getJson('/api/team-members?per_page=1&page=1')
            ->assertOk()
            ->assertJsonStructure([
                'status', 'message', 'data', 'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_public_only_sees_active_by_default(): void
    {
        TeamMember::create(['name' => 'Active One', 'slug' => 'active-one', 'is_active' => true]);
        TeamMember::create(['name' => 'Inactive One', 'slug' => 'inactive-one', 'is_active' => false]);
        $admin = $this->admin();

        // public (guest) should only see active by default - ensure guest by not actingAs
        $this->getJson('/api/team-members')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'active-one');

        // public cannot see inactive even if explicitly requesting is_active=false (security)
        $this->getJson('/api/team-members?is_active=false')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'active-one');

        // admin can see inactive explicitly
        $this->actingAs($admin)->getJson('/api/team-members?is_active=false')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'inactive-one');

        // admin without filter sees all
        $this->actingAs($admin)->getJson('/api/team-members')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_search_and_sort_and_pagination(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->postJson('/api/team-members', ['name' => 'Zebra', 'slug' => 'zebra', 'bio' => 'engineering'])->assertCreated();
        $this->actingAs($admin)->postJson('/api/team-members', ['name' => 'Apple', 'slug' => 'apple', 'bio' => 'design'])->assertCreated();

        $this->getJson('/api/team-members?search=Zebra')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/team-members?sort=name&direction=asc')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Apple');

        $this->getJson('/api/team-members?sort=created_at&direction=desc')
            ->assertOk();
    }

    public function test_show_detail(): void
    {
        $admin = $this->admin();
        $res = $this->actingAs($admin)->postJson('/api/team-members', ['name' => 'Detail Guy', 'slug' => 'detail-guy'])->assertCreated();
        $id = $res->json('data.id');

        $this->getJson("/api/team-members/{$id}")
            ->assertOk()
            ->assertJsonPath('data.slug', 'detail-guy');

        $this->getJson('/api/team-members/non-existent-uuid')
            ->assertNotFound();
    }

    public function test_admin_can_create_update_delete(): void
    {
        $admin = $this->admin();
        $media = MediaAsset::query()->create(['file_id' => 'test123', 'url' => 'https://example.com/a.jpg']);

        $create = $this->actingAs($admin)->postJson('/api/team-members', [
            'name' => 'Create Me',
            'slug' => 'create-me',
            'image_id' => $media->id,
            'email' => 'create@example.com',
            'linkedin_url' => 'https://linkedin.com/in/create',
            'instagram_url' => 'https://instagram.com/create',
            'bio' => 'bio text',
            'is_active' => true,
        ])->assertCreated()->assertJsonPath('data.name', 'Create Me');

        $id = $create->json('data.id');

        // slug unique validation
        $this->actingAs($admin)->postJson('/api/team-members', ['name' => 'Dup', 'slug' => 'create-me'])
            ->assertUnprocessable();

        $this->actingAs($admin)->patchJson("/api/team-members/{$id}", [
            'name' => 'Updated Name',
            'slug' => 'updated-name',
            'bio' => 'updated bio',
        ])->assertOk()->assertJsonPath('data.slug', 'updated-name');

        $this->actingAs($admin)->deleteJson("/api/team-members/{$id}")->assertOk();

        $this->getJson("/api/team-members/{$id}")->assertNotFound();
    }

    public function test_non_admin_cannot_mutate(): void
    {
        $user = $this->user();
        $this->actingAs($user)->postJson('/api/team-members', ['name' => 'Hack', 'slug' => 'hack'])
            ->assertForbidden();

        $admin = $this->admin();
        $res = $this->actingAs($admin)->postJson('/api/team-members', ['name' => 'Victim', 'slug' => 'victim'])->assertCreated();
        $id = $res->json('data.id');

        $this->actingAs($user)->patchJson("/api/team-members/{$id}", ['name' => 'Hacked'])->assertForbidden();
        $this->actingAs($user)->deleteJson("/api/team-members/{$id}")->assertForbidden();
    }

    public function test_validation_rejects_invalid_image_and_urls(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->postJson('/api/team-members', [
            'name' => 'Bad',
            'slug' => 'bad',
            'image_id' => '00000000-0000-0000-0000-000000000000',
        ])->assertUnprocessable();

        $this->actingAs($admin)->postJson('/api/team-members', [
            'name' => 'Bad2',
            'slug' => 'bad2',
            'email' => 'not-email',
        ])->assertUnprocessable();
    }
}
