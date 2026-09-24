<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class CartItemApiTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $name = 'User'): User
    {
        return User::query()->create([
            'role' => 'user',
            'name' => $name,
            'username' => 'user-' . Str::random(8),
            'email' => 'user-' . Str::random(8) . '@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    private function createProduct(array $attributes = []): Product
    {
        return Product::query()->create(array_merge([
            'title' => 'Kaos 180DC',
            'slug' => 'kaos-180dc-' . Str::uuid(),
            'type' => 'physical',
            'status' => 'active',
            'price' => 50000,
            'stock' => 10,
        ], $attributes));
    }

    public function test_guest_cannot_access_cart_items(): void
    {
        $dummyId = Str::uuid()->toString();

        $this->postJson('/api/cart/items', [
            'product_id' => $dummyId,
            'quantity' => 1,
        ])->assertUnauthorized();

        $this->patchJson("/api/cart/items/{$dummyId}", [
            'quantity' => 2,
        ])->assertUnauthorized();

        $this->deleteJson("/api/cart/items/{$dummyId}")->assertUnauthorized();
    }

    public function test_add_new_item(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['price' => 50000, 'stock' => 10]);

        $response = $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Cart item added successfully.')
            ->assertJsonPath('data.quantity', 2)
            ->assertJsonPath('data.line_total_amount', '100000.00')
            ->assertJsonPath('data.is_valid', true)
            ->assertJsonPath('meta.cart_summary.item_count', 1)
            ->assertJsonPath('meta.cart_summary.total_quantity', 2)
            ->assertJsonPath('meta.cart_summary.subtotal_amount', '100000.00');

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_add_same_product_merges(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['price' => 50000, 'stock' => 10]);

        // First add
        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertCreated();

        // Second add of same product
        $response = $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.quantity', 5)
            ->assertJsonPath('data.line_total_amount', '250000.00')
            ->assertJsonPath('meta.cart_summary.item_count', 1)
            ->assertJsonPath('meta.cart_summary.total_quantity', 5)
            ->assertJsonPath('meta.cart_summary.subtotal_amount', '250000.00');

        // Merges into 1 row, not 2
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
    }

    public function test_add_exceeds_stock_409(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['price' => 50000, 'stock' => 5]);

        // Direct quantity > stock
        $response1 = $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 6,
        ]);
        $response1->assertStatus(409)
            ->assertJsonPath('status', 'error');

        $this->assertDatabaseCount('cart_items', 0);

        // Add 3 (valid, stock is 5)
        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ])->assertCreated();

        // Add 3 more (3 + 3 = 6 > 5) -> merge exceeds stock
        $response2 = $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
        $response2->assertStatus(409)
            ->assertJsonPath('status', 'error');

        // Quantity in DB stays 3
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_add_inactive_409(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['status' => 'inactive', 'stock' => 10]);

        $response = $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('status', 'error');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_add_validation_errors_422(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['stock' => 50]);

        // Quantity 0
        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 0,
        ])->assertUnprocessable();

        // Quantity 100 (> 99)
        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 100,
        ])->assertUnprocessable();

        // Non-existent product UUID
        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => Str::uuid()->toString(),
            'quantity' => 1,
        ])->assertUnprocessable();

        // Non-UUID product_id
        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => 'not-a-uuid',
            'quantity' => 1,
        ])->assertUnprocessable();
    }

    public function test_update_quantity(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['price' => 50000, 'stock' => 10]);

        $addResponse = $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertCreated();

        $itemId = $addResponse->json('data.id');

        $response = $this->actingAs($user)->patchJson("/api/cart/items/{$itemId}", [
            'quantity' => 4,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.quantity', 4)
            ->assertJsonPath('data.line_total_amount', '200000.00')
            ->assertJsonPath('meta.cart_summary.item_count', 1)
            ->assertJsonPath('meta.cart_summary.total_quantity', 4)
            ->assertJsonPath('meta.cart_summary.subtotal_amount', '200000.00');

        $this->assertDatabaseHas('cart_items', [
            'id' => $itemId,
            'quantity' => 4,
        ]);
    }

    public function test_update_to_zero_422(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['stock' => 10]);

        $addResponse = $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertCreated();

        $itemId = $addResponse->json('data.id');

        $this->actingAs($user)->patchJson("/api/cart/items/{$itemId}", [
            'quantity' => 0,
        ])->assertUnprocessable();

        // Quantity remains 2
        $this->assertDatabaseHas('cart_items', [
            'id' => $itemId,
            'quantity' => 2,
        ]);
    }

    public function test_update_exceeds_stock_409(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['stock' => 5]);

        $addResponse = $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertCreated();

        $itemId = $addResponse->json('data.id');

        $response = $this->actingAs($user)->patchJson("/api/cart/items/{$itemId}", [
            'quantity' => 6,
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('status', 'error');

        $this->assertDatabaseHas('cart_items', [
            'id' => $itemId,
            'quantity' => 2,
        ]);
    }

    public function test_delete(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['price' => 50000, 'stock' => 10]);

        $addResponse = $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertCreated();

        $itemId = $addResponse->json('data.id');
        $this->assertDatabaseCount('cart_items', 1);

        $response = $this->actingAs($user)->deleteJson("/api/cart/items/{$itemId}");

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data', null)
            ->assertJsonPath('meta.cart_summary.item_count', 0)
            ->assertJsonPath('meta.cart_summary.total_quantity', 0)
            ->assertJsonPath('meta.cart_summary.subtotal_amount', '0.00');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_idor_cannot_update_other_user_item(): void
    {
        $userA = $this->user('User A');
        $userB = $this->user('User B');

        $product = $this->createProduct(['stock' => 10]);

        // User A adds item
        $addResponse = $this->actingAs($userA)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertCreated();

        $userAItemId = $addResponse->json('data.id');

        // User B tries to update User A's item -> 404
        $this->actingAs($userB)->patchJson("/api/cart/items/{$userAItemId}", [
            'quantity' => 5,
        ])->assertNotFound();

        // User B tries to delete User A's item -> 404
        $this->actingAs($userB)->deleteJson("/api/cart/items/{$userAItemId}")
            ->assertNotFound();

        // User A's item is unchanged
        $this->assertDatabaseHas('cart_items', [
            'id' => $userAItemId,
            'quantity' => 2,
        ]);
    }

    public function test_digital_product_no_stock_check(): void
    {
        $user = $this->user();
        $digitalProduct = $this->createProduct([
            'title' => 'E-Book Digital',
            'type' => 'digital',
            'status' => 'active',
            'price' => 30000,
            'stock' => 0, // Stock is 0 for digital
        ]);

        $response = $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $digitalProduct->id,
            'quantity' => 5,
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.quantity', 5)
            ->assertJsonPath('data.line_total_amount', '150000.00');
    }

    public function test_stock_not_decremented_on_cart_operations(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['stock' => 10]);

        // Add
        $addResponse = $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ])->assertCreated();

        $product->refresh();
        $this->assertEquals(10, $product->stock, 'Stock should NOT decrement on cart add');

        $itemId = $addResponse->json('data.id');

        // Update
        $this->actingAs($user)->patchJson("/api/cart/items/{$itemId}", [
            'quantity' => 5,
        ])->assertOk();

        $product->refresh();
        $this->assertEquals(10, $product->stock, 'Stock should NOT decrement on cart update');

        // Delete
        $this->actingAs($user)->deleteJson("/api/cart/items/{$itemId}")->assertOk();

        $product->refresh();
        $this->assertEquals(10, $product->stock, 'Stock should NOT change on cart delete');
    }
}
