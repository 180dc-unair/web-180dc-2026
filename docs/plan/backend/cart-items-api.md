# Planning Backend API — Cart Items

Status: Draft

## Tujuan dan schema

API ini menangani item di current cart. Schema sumber adalah migration `2026_06_25_100019_create_cart_items_table`:

- `id` UUID primary key
- `cart_id` foreign key ke `carts`, cascade delete
- `product_id` foreign key ke `products`, cascade delete
- `quantity` integer default `1`
- timestamps

Cart hanya berisi `products`; event/service tidak boleh masuk melalui endpoint ini. Product yang dipakai harus `status = active`. Untuk product `physical`, quantity tidak boleh melebihi stock yang tersedia; aturan stock product digital perlu dikunci sebagai policy (MVP: digital tidak dibatasi stock).

## Perubahan schema yang direncanakan

Tambahkan unique composite index `cart_items(cart_id, product_id)`. Dengan begitu satu product hanya memiliki satu baris per cart dan operasi add dapat menaikkan quantity secara atomic/upsert, bukan membuat duplikat.

Tambahkan index pada `cart_id` dan `product_id` bila belum tercakup oleh foreign key/index engine database.

## Endpoint yang direncanakan

Base path: `/api/cart/items`

| Method | Path | Akses | Tujuan |
|---|---|---|---|
| POST | `/api/cart/items` | Authenticated user | Menambah product; jika sudah ada, merge quantity |
| PATCH | `/api/cart/items/{cartItem}` | Authenticated user | Mengganti quantity |
| DELETE | `/api/cart/items/{cartItem}` | Authenticated user | Menghapus satu item |

`GET` item tidak dibuat terpisah pada MVP; item dikembalikan melalui `GET /api/cart`. Jika kebutuhan mobile/client memerlukan endpoint terpisah, tambahkan `GET /api/cart/items` sebagai read-only alias.

`{cartItem}` wajib di-scope ke current cart user. Jangan menerima `cart_id` dari client sebagai sumber authorization.

## Request contract

### Add item

```json
{
  "product_id": "uuid-product",
  "quantity": 2
}
```

Rules:

- `product_id` wajib dan harus ada di `products`.
- `quantity` wajib integer minimal `1`.
- Product `status != active` ditolak.
- Physical product: validasi `existing_quantity + quantity <= stock`.
- Digital product: validasi ketersediaan sesuai policy digital, tanpa mengurangi stock saat add-to-cart.

### Update item

```json
{
  "quantity": 3
}
```

`quantity = 0` ditolak dengan `422`; client memakai `DELETE` untuk menghapus item. Server melakukan validasi stok ulang sebelum menyimpan.

## Response contract

```json
{
  "status": "success",
  "message": "Cart item added successfully.",
  "data": {
    "id": "uuid-cart-item",
    "product": {
      "id": "uuid-product",
      "title": "Nama Produk",
      "slug": "nama-produk",
      "price": "100000.00",
      "image": null
    },
    "quantity": 2,
    "line_total_amount": "200000.00"
  }
}
```

Setelah mutasi, response boleh menyertakan `cart`/`summary` terbaru agar frontend tidak perlu request kedua. Pilih satu response shape pada implementasi dan dokumentasikan di collection API.

## Transaksi dan concurrency

- Resolve current cart dari authenticated user, lalu `lockForUpdate` pada cart item/product saat add atau update.
- Gunakan transaction untuk merge quantity dan validasi stock.
- Jangan mengurangi stock saat add-to-cart; reserve/decrement dilakukan pada checkout/order flow.
- Bila product dihapus, FK cascade menghapus item; `GET /api/cart` harus tetap aman bila item stale ditemukan.

## Error contract

- `401` unauthenticated.
- `403` bila cart item bukan milik current user.
- `404` product atau cart item tidak ditemukan.
- `409` stock tidak cukup atau product tidak lagi tersedia.
- `422` payload/quantity tidak valid.

Contoh stock error:

```json
{
  "status": "error",
  "message": "Insufficient stock for this product.",
  "data": null,
  "errors": {
    "quantity": ["Only 1 item is available."]
  }
}
```

## Rencana implementasi

1. Tambahkan `CartItem` model dengan `cart()` dan `product()`.
2. Tambahkan `CartItemResource` untuk product summary dan line total.
3. Tambahkan `AddCartItemRequest` dan `UpdateCartItemRequest`.
4. Tambahkan `CartItemService` untuk resolve cart, validate product/stock, merge, update, dan delete.
5. Tambahkan `CartItemController@store`, `update`, `destroy`.
6. Daftarkan route di group `auth:sanctum`.
7. Tambahkan migration unique composite `cart_id + product_id`.
8. Tambahkan feature test untuk add baru, merge duplicate, update, delete, stock, product inactive, IDOR, dan race/concurrency path.

## Acceptance criteria

- Add product yang sama dua kali menghasilkan satu row dengan quantity tergabung.
- Product inactive, product tidak ditemukan, dan stock tidak cukup ditolak dengan status yang konsisten.
- User tidak dapat memanipulasi item cart user lain dengan mengganti UUID.
- Harga line total selalu dihitung server-side dari product.
- Add/update tidak mengurangi stock dan tidak membuat order/payment.
- Cart response setelah mutasi dapat langsung dipakai frontend untuk memperbarui badge dan subtotal.

## Testing API

Request collection untuk pengujian manual/CI diletakkan di `docs/API/Cart Item/`:

- `Add Cart Item.yml`
- `Update Cart Item.yml`
- `Delete Cart Item.yml`

Smoke test memakai `product_id`, `cart_item_id`, dan `token` dari environment. Uji juga product inactive, quantity melebihi stock, quantity `0`, dan UUID cart item milik user lain.
