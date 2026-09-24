<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class CartApiTest extends TestCase
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
            'title' => 'Test Product',
            'slug' => 'test-product-' . Str::uuid(),
            'type' => 'physical',
            'status' => 'active',
            'price' => 50000,
            'stock' => 10,
        ], $attributes));
    }

    public function test_guest_cannot_access_cart(): void
    {
        $this->getJson('/api/cart')->assertUnauthorized();
        $this->deleteJson('/api/cart')->assertUnauthorized();
    }

    public function test_lazy_create_on_first_get(): void
    {
        $user = $this->user();

        $this->assertDatabaseCount('carts', 0);

        $response = $this->actingAs($user)->getJson('/api/cart');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user_id', $user->id);

        $this->assertDatabaseCount('carts', 1);

        // Subsequent GET does not create another cart
        $this->actingAs($user)->getJson('/api/cart')->assertOk();
        $this->assertDatabaseCount('carts', 1);
    }

    public function test_returns_empty_items(): void
    {
        $user = $this->user();

        $response = $this->actingAs($user)->getJson('/api/cart');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.items', [])
            ->assertJsonPath('data.summary.item_count', 0)
            ->assertJsonPath('data.summary.total_quantity', 0)
            ->assertJsonPath('data.summary.subtotal_amount', '0.00')
            ->assertJsonPath('data.summary.currency', 'IDR')
            ->assertJsonPath('data.summary.invalid_items_count', 0);
    }

    public function test_returns_items_with_summary(): void
    {
        $user = $this->user();
        $productA = $this->createProduct(['price' => 50000, 'stock' => 10]);
        $productB = $this->createProduct(['price' => 25000, 'stock' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $productA->id,
            'quantity' => 2,
        ])->assertCreated();

        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $productB->id,
            'quantity' => 3,
        ])->assertCreated();

        $response = $this->actingAs($user)->getJson('/api/cart');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(2, 'data.items')
            ->assertJsonPath('data.summary.item_count', 2)
            ->assertJsonPath('data.summary.total_quantity', 5)
            ->assertJsonPath('data.summary.subtotal_amount', '175000.00')
            ->assertJsonPath('data.summary.currency', 'IDR')
            ->assertJsonPath('data.summary.invalid_items_count', 0);
    }

    public function test_clear_is_idempotent(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['price' => 50000, 'stock' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertCreated();

        $this->assertDatabaseCount('cart_items', 1);

        // First clear
        $response1 = $this->actingAs($user)->deleteJson('/api/cart');
        $response1->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.items', [])
            ->assertJsonPath('data.summary.item_count', 0)
            ->assertJsonPath('data.summary.subtotal_amount', '0.00');

        $this->assertDatabaseCount('cart_items', 0);

        // Second clear on already empty cart (idempotent)
        $response2 = $this->actingAs($user)->deleteJson('/api/cart');
        $response2->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.items', [])
            ->assertJsonPath('data.summary.item_count', 0);
    }

    public function test_user_a_cannot_see_user_b_cart(): void
    {
        $userA = $this->user('User A');
        $userB = $this->user('User B');

        $productA = $this->createProduct(['title' => 'Product A', 'price' => 50000]);
        $productB = $this->createProduct(['title' => 'Product B', 'price' => 75000]);

        // User A adds Product A
        $this->actingAs($userA)->postJson('/api/cart/items', [
            'product_id' => $productA->id,
            'quantity' => 1,
        ])->assertCreated();

        // User B adds Product B
        $this->actingAs($userB)->postJson('/api/cart/items', [
            'product_id' => $productB->id,
            'quantity' => 1,
        ])->assertCreated();

        // User A views cart
        $responseA = $this->actingAs($userA)->getJson('/api/cart');
        $responseA->assertOk()
            ->assertJsonPath('data.user_id', $userA->id)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.product.id', $productA->id)
            ->assertJsonPath('data.summary.subtotal_amount', '50000.00');

        // User B views cart
        $responseB = $this->actingAs($userB)->getJson('/api/cart');
        $responseB->assertOk()
            ->assertJsonPath('data.user_id', $userB->id)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.product.id', $productB->id)
            ->assertJsonPath('data.summary.subtotal_amount', '75000.00');
    }

    public function test_summary_excludes_invalid_items(): void
    {
        $user = $this->user();

        $validProduct = $this->createProduct(['price' => 100000, 'stock' => 10, 'status' => 'active']);
        $outOfStockProduct = $this->createProduct(['price' => 200000, 'stock' => 5, 'status' => 'active']);
        $inactiveProduct = $this->createProduct(['price' => 50000, 'stock' => 10, 'status' => 'active']);

        // User adds all 3 products initially
        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $validProduct->id,
            'quantity' => 2,
        ])->assertCreated();

        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $outOfStockProduct->id,
            'quantity' => 4,
        ])->assertCreated();

        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $inactiveProduct->id,
            'quantity' => 1,
        ])->assertCreated();

        // Now simulate product state changes after items were in cart
        // 1. Out of stock product stock drops below cart quantity
        $outOfStockProduct->update(['stock' => 2]); // cart has 4 > stock 2
        // 2. Inactive product becomes inactive
        $inactiveProduct->update(['status' => 'inactive']);

        $response = $this->actingAs($user)->getJson('/api/cart');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(3, 'data.items')
            // Summary only counts valid items (validProduct: 2 * 100000 = 200000.00)
            ->assertJsonPath('data.summary.item_count', 3)
            ->assertJsonPath('data.summary.total_quantity', 2)
            ->assertJsonPath('data.summary.subtotal_amount', '200000.00')
            ->assertJsonPath('data.summary.invalid_items_count', 2);

        // Check is_valid flags on items
        $items = collect($response->json('data.items'));

        $validItem = $items->firstWhere('product.id', $validProduct->id);
        $this->assertTrue($validItem['is_valid']);
        $this->assertNull($validItem['invalid_reason']);

        $oosItem = $items->firstWhere('product.id', $outOfStockProduct->id);
        $this->assertFalse($oosItem['is_valid']);
        $this->assertEquals('out_of_stock', $oosItem['invalid_reason']);

        $inactiveItem = $items->firstWhere('product.id', $inactiveProduct->id);
        $this->assertFalse($inactiveItem['is_valid']);
        $this->assertEquals('inactive', $inactiveItem['invalid_reason']);
    }
}
