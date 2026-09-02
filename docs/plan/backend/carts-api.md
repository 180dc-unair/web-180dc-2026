# Planning Backend API — Carts

Status: **Approved** — Docker-native, Gateway-ready
Version: 2.0.0 | Stack: Laravel 13 + MySQL 8.4 + Docker Compose Watch | Env: `http://localhost:8080`

## 1. Tujuan & Prinsip

Menyediakan **single current cart per user** sebagai *staging area* sebelum **Checkout → Order → Payment (3rd party gateway)**. Cart **tidak pernah** menyimpan harga permanen atau status pembayaran; hanya referensi `product_id + quantity`. Harga, stok, dan availability di-*resolve* live saat `GET /api/cart` dan di-*freeze* saat `POST /api/orders` (snapshot ke `order_items`).

**Prinsip gateway-ready:**
- Cart harus bisa di-checkout ke `Order` yang kemudian di-*attach* ke `Payment` manapun (`midtrans/xendit/tripay/manual_transfer`) tanpa ubah contract Cart.
- Cart harus tahan race condition (unique `carts.user_id` + `cart_items(cart_id,product_id)`).
- Semua hitungan uang string decimal (`"150000.00"`) — jangan `float`.

Schema sumber: `2026_06_25_100018_create_carts_table` + `2026_06_25_100019_create_cart_items_table`

```php
carts: id UUID PK, user_id UUID FK -> users ON DELETE CASCADE, timestamps
cart_items: id UUID PK, cart_id UUID FK -> carts CASCADE,
            product_id UUID FK -> products CASCADE, quantity INT default 1, timestamps
```

## 2. Visualisasi Database

```mermaid
erDiagram
    USERS ||--o| CARTS : owns
    CARTS ||--o{ CART_ITEMS : contains
    PRODUCTS ||--o{ CART_ITEMS : selected
    CARTS ||--o{ ORDERS : "checked out to (via checkout)"

    USERS {
        uuid id PK
        string role
        string email UK
    }
    CARTS {
        uuid id PK
        uuid user_id FK UK
        timestamp created_at
        timestamp updated_at
    }
    CART_ITEMS {
        uuid id PK
        uuid cart_id FK
        uuid product_id FK
        int quantity
    }
    PRODUCTS {
        uuid id PK
        string title
        string slug UK
        string type "digital|physical"
        string status "active|inactive"
        decimal price
        int stock
    }
    ORDERS {
        uuid id PK
        string order_number UK
        decimal total_amount
        string status
    }
```

## 3. Keputusan Domain (Lock)

| Keputusan | Detail | Alasan |
|---|---|---|
| **1 user = 1 current cart** | `carts.user_id UNIQUE` | Cegah multi-cart active, sederhanakan checkout |
| **Lazy create** | `GET /api/cart` dan `POST /api/cart/items` auto `firstOrCreate` | Cart kosong = 200 `items:[]`, bukan 404 |
| **Harga live, bukan frozen** | `summary.subtotal` = `SUM(products.price * qty)` saat read | Harga bisa berubah sebelum checkout; freeze hanya di `order_items` |
| **Hanya products** | Cart **hanya** `products` (`status=active`). `events` tidak masuk cart — event checkout via `POST /api/orders` dengan `item_type=event` langsung | Pisahkan concern fisik vs event ticketing |
| **Tidak kurangi stok** | `add-to-cart` **tidak** decrement `products.stock`. Decrement/reserve hanya di `POST /api/orders` dalam transaction | Cart bukan reservasi |
| **Semua route `auth:sanctum`** | Scope ke `auth()->user()->id` | Anti IDOR |
| **Currency fixed IDR** | `summary.currency = IDR` | Konsisten gateway (`Xendit/Midtrans` butuh IDR) |

## 4. Perubahan Schema (Wajib sebelum code — Docker)

> Jalankan semua via Docker, jangan di host.

```bash
# 1. Buat migration
docker compose exec app php artisan make:migration add_unique_constraints_to_carts_and_cart_items --create=carts

# isi up():
# $table->unique('user_id', 'carts_user_id_unique');
# $table->unique(['cart_id','product_id'], 'cart_items_cart_product_unique');
# optional: $table->index('cart_id'); $table->index('product_id');

# 2. Run
docker compose exec app php artisan migrate

# 3. Verifikasi
docker compose exec mysql mysql -u180dc -p180dc -e "SHOW CREATE TABLE carts; SHOW CREATE TABLE cart_items;" 180dc

# 4. Fresh jika perlu (dev only)
docker compose exec app php artisan migrate:fresh --seed
```

**Kenapa wajib:** Tanpa unique, 2 request paralel `GET /api/cart` bisa bikin 2 cart untuk 1 user. Unique + `try/catch QueryException` → retry `firstOrCreate` adalah pola yang benar (lihat Error Contract 409).

## 5. Endpoint

Base path: `/api/cart` — group `middleware: auth:sanctum`

| Method | Path | Akses | Tujuan | Idempotent |
|---|---|---|---|---|
| `GET` | `/api/cart` | User (owner) | Ambil current cart + items + summary (lazy create) | Yes |
| `DELETE` | `/api/cart` | User (owner) | Kosongkan cart (hapus semua `cart_items`) | Yes |

**Tidak ada** `POST /api/cart`, `PATCH /api/cart`. Mutasi lewat `POST /api/cart/items` (lihat `cart-items-api.md`). Ini by design.

### 5.1 GET /api/cart — Contract Lengkap

**Request:** `GET /api/cart` header `Authorization: Bearer <sanctum_token>` — tidak ada query.

**Response 200 — Cart kosong / berisi:**

```json
{
  "status": "success",
  "message": "Cart retrieved successfully.",
  "data": {
    "id": "0196a1b2-...",
    "user_id": "0196a1a0-...",
    "items": [
      {
        "id": "0196a1b5-...",
        "quantity": 2,
        "line_total_amount": "300000.00",
        "product": {
          "id": "01969f33-...",
          "title": "Kaos 180DC",
          "slug": "kaos-180dc",
          "type": "physical",
          "status": "active",
          "price": "150000.00",
          "stock": 12,
          "image": { "id": "...", "url": "https://..." }
        },
        "is_valid": true,
        "invalid_reason": null
      }
    ],
    "summary": {
      "item_count": 1,
      "total_quantity": 2,
      "subtotal_amount": "300000.00",
      "currency": "IDR",
      "invalid_items_count": 0
    },
    "created_at": "2026-08-30T00:00:00.000000Z",
    "updated_at": "2026-08-30T00:00:00.000000Z"
  }
}
```

**Aturan summary:**
- `item_count` = `COUNT(cart_items)`
- `total_quantity` = `SUM(quantity)` (hanya valid items)
- `subtotal_amount` = `SUM(product.price * quantity)` — string `decimal:2`, hitung server-side, jangan dari client
- `is_valid` per item = `product.status === active && (type === digital || quantity <= product.stock)` — jika invalid, tetap kembalikan tapi `is_valid:false` + `invalid_reason: "out_of_stock" | "inactive"`
- Invalid items **tidak ikut** `subtotal`, tapi tetap tampil agar frontend bisa warning. Checkout harus reject jika ada invalid.

**Expand:** Frontend butuh `items.product.image` — selalu `with(['items.product.image'])` di service, cegah N+1.

### 5.2 DELETE /api/cart — Clear

**Request:** `DELETE /api/cart` header `Authorization`.

**Response 200 (pilih salah satu, konsisten):**

```json
{
  "status": "success",
  "message": "Cart cleared successfully.",
  "data": {
    "id": "0196a1b2-...",
    "items": [],
    "summary": { "item_count": 0, "total_quantity": 0, "subtotal_amount": "0.00", "currency": "IDR" },
    "created_at": "...",
    "updated_at": "..."
  }
}
```

Alternatif `204 No Content` — tapi **rekomendasi 200** agar frontend dapat update badge tanpa `GET` lagi. Dokumentasikan di Bruno.

**Idempotent:** `DELETE` cart kosong tetap 200, tidak 404.

## 6. Transaksi & Concurrency (Wajib)

```php
// CartService::getCurrentCart(User $user): Cart
return DB::transaction(function () use ($user) {
    try {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    } catch (QueryException $e) {
        // unique violation race -> retry fetch
        if ($e->getCode() === '23000') {
            return Cart::where('user_id', $user->id)->firstOrFail();
        }
        throw $e;
    }
});

// CartService::clear(Cart $cart): void
DB::transaction(function () use ($cart) {
    $cart->items()->lockForUpdate()->get(); // optional lock
    $cart->items()->delete();
    $cart->touch();
});
```

- Gunakan `lockForUpdate` pada `cart_items` saat clear bila khawatir race dengan `POST /cart/items`.
- Jangan gunakan `Cart::truncate` — scope harus per user.

## 7. Validasi & Error Contract (Konsisten dengan app)

| Status | Kapan | Body |
|---|---|---|
| `200` | GET success, DELETE success (cart kosong pun) | `status:success` |
| `401` | Tanpa token / token invalid / expired | `{"status":"error","message":"Unauthenticated."}` |
| `403` | Mencoba akses cart user lain (tidak mungkin via current cart, tapi untuk future `GET /carts/{id}`) | `Forbidden` |
| `404` | Hanya untuk resource eksplisit yang tidak ditemukan — **tidak untuk current cart** | — |
| `409` | Race `firstOrCreate` unique violation (sudah di-handle retry di service) — jika masih 409, kembalikan retryable | `{"status":"error","message":"Conflict, please retry."}` |
| `500` | Unexpected — log `storage/logs/laravel.log` via `docker compose logs app` | — |

## 8. Integrasi Checkout → Order → Payment (Gateway-ready)

```
[GET /api/cart] --user klik Checkout--> [POST /api/orders {customer_name,email}]
       |                                          |
       | snapshot items + freeze price             v
       |                                   Order pending
       |                                          |
       |                                    POST /api/orders/{order}/payments
       |                                          |
       |                              PaymentGatewayInterface::create()
       |                              -> gateway_reference + payment_url
       |                                          |
       +-- Cart DIKOSONGKAN hanya setelah Order CREATED (bukan setelah Payment paid)
```

- **Cart clear timing:** `CartService::clear` dipanggil **di dalam** `CheckoutService::createOrder` setelah `Order + OrderItems` committed — bukan setelah payment success. Jika payment gagal/expired, user bisa reorder (cart sudah kosong, tapi order tetap `pending` → bisa `POST /payments` lagi).
- **Stock:** Decrement `products.stock` dan increment `sold_count` **hanya** saat `payments` webhook `paid` — bukan saat cart add.

## 9. Rencana Implementasi (Docker-native, step-by-step)

> Ikuti urutan ini, jangan loncat ke Payments sebelum Cart stabil.

1. **Migration** — `add_unique_to_carts` (lihat §4) + `docker compose exec app php artisan migrate`
2. **Model** — `app/Models/Cart.php` (`HasUuids`, `fillable user_id`, `user(): BelongsTo`, `items(): HasMany`) + `CartItem` (lihat `cart-items-api.md`)
3. **Resource** — `app/Http/Resources/CartResource.php` + `CartItemResource` (hitung `line_total`, `is_valid`, `summary`)
4. **Service** — `app/Services/CartService.php` (`getCurrentCart`, `clear`, `getSummary`) + bind di `AppServiceProvider`
5. **Controller** — `app/Http/Controllers/Api/CartController.php` (`show`, `clear`)
6. **Routes** — `routes/api.php` dalam `Route::middleware('auth:sanctum')->group(...)`:
   ```php
   Route::get('/cart', [CartController::class, 'show']);
   Route::delete('/cart', [CartController::class, 'clear']);
   ```
7. **Test (Docker)** —
   ```bash
   docker compose exec app php artisan make:test CartApiTest
   docker compose exec app php artisan test --filter=CartApiTest
   docker compose exec app php artisan test
   ```
   Cases: `test_guest_cannot_access_cart`, `test_lazy_create_on_first_get`, `test_returns_empty_items`, `test_returns_items_with_summary`, `test_clear_is_idempotent`, `test_user_a_cannot_see_user_b_cart`, `test_summary_excludes_invalid_items`
8. **Bruno** — `docs/API/Cart/Get Cart.yml` + `Clear Cart.yml` sudah ada — update `Local.yml` `token` dan validasi `summary`.

## 10. Acceptance Criteria (QA Checklist)

- [ ] 1 user tidak pernah punya 2 rows di `carts` (cek `SELECT user_id, COUNT(*) FROM carts GROUP BY user_id HAVING COUNT(*)>1` = 0)
- [ ] `GET /api/cart` tanpa cart sebelumnya → 200 `items:[]` + cart ter-create di DB
- [ ] `GET /api/cart` dengan 2 items → `summary` benar, `line_total` = `price * qty` string
- [ ] Product `inactive` atau `stock < qty` → item `is_valid:false` + tidak ikut subtotal + checkout reject (di Orders)
- [ ] `DELETE /api/cart` 2x berturut-turut → 200 dua kali, item tetap 0
- [ ] User A `GET /api/cart` tidak pernah melihat item User B (test dengan 2 token)
- [ ] `docker compose logs app` tidak bocor SQL saat 500

## 11. Docker QA & Troubleshooting

```bash
# Logs
docker compose logs -f app
docker compose logs -f mysql

# Masuk container
docker compose exec app sh
docker compose exec mysql mysql -u180dc -p180dc 180dc

# Typecheck & build (frontend badge cart)
docker compose exec node sh -c "npm run build"

# Watch sync
docker compose up --build --watch  # carts & cart_items kode auto-sync ke container app
```

## 12. Referensi File

- Migration: `database/migrations/2026_06_25_100018_create_carts_table.php`
- Planning items: `docs/plan/backend/cart-items-api.md`
- Next: `docs/plan/backend/orders-api.md` (checkout)
- Bruno: `docs/API/Cart/*`, `docs/API/environments/Local.yml`
