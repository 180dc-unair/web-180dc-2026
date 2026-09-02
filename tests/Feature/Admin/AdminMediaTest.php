<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_imagekit_media_and_receive_media_asset_id(): void
    {
        config()->set('services.imagekit.private_key', 'test-private-key');
        config()->set('services.imagekit.upload_url', 'https://upload.imagekit.io/api/v1/files/upload');

        Http::fake([
            'https://upload.imagekit.io/*' => Http::response([
                'fileId' => 'imagekit-file-123',
                'url' => 'https://ik.imagekit.io/example/cover.jpg',
            ], 200),
        ]);

        $admin = User::query()->create([
            'role' => 'admin',
            'name' => 'Media Admin',
            'username' => 'media-admin',
            'email' => 'media@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($admin)->post('/api/admin/media', [
            'file' => UploadedFile::fake()->createWithContent(
                'cover.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2nGQAAAAASUVORK5CYII='),
            ),
            'purpose' => 'articles',
        ], ['Accept' => 'application/json']);

        $response
            ->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.file_id', 'imagekit-file-123')
            ->assertJsonPath('data.url', 'https://ik.imagekit.io/example/cover.jpg');

        $this->assertDatabaseHas('media_assets', [
            'file_id' => 'imagekit-file-123',
            'url' => 'https://ik.imagekit.io/example/cover.jpg',
        ]);

        Http::assertSentCount(1);
    }

    public function test_media_upload_requires_an_admin_and_valid_image(): void
    {
        $this->post('/api/admin/media', [], ['Accept' => 'application/json'])->assertUnauthorized();

        $admin = User::query()->create([
            'role' => 'admin',
            'name' => 'Validation Admin',
            'username' => 'validation-admin',
            'email' => 'validation@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($admin)
            ->post('/api/admin/media', [
                'file' => UploadedFile::fake()->create('document.pdf', 10, 'application/pdf'),
                'purpose' => 'articles',
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }
}
