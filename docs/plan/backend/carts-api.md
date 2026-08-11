# Planning Backend API — Carts

Status: Draft

## Tujuan

Menyediakan API untuk mengambil dan mengosongkan keranjang aktif milik user. Detail mutasi item dipisahkan ke [cart-items-api.md](./cart-items-api.md) agar aturan produk, quantity, dan stok tidak bercampur dengan lifecycle cart.

Schema sumber saat ini adalah migration `2026_06_25_100018_create_carts_table`:

- `id` UUID primary key
- `user_id` foreign key ke `users`, cascade delete
- timestamps

## Keputusan domain

- Satu user memiliki satu **current cart** pada satu waktu.
- Cart dibuat lazy saat endpoint `GET /api/cart` atau add item pertama kali dipanggil.
- Cart tidak menyimpan harga atau total permanen; total dihitung dari harga produk aktif saat dibaca.
- Cart bukan order dan tidak menangani payment/checkout. Order akan mengambil snapshot harga di `order_items` pada flow checkout terpisah.
- Semua endpoint cart memerlukan `auth:sanctum`; user hanya boleh mengakses cart miliknya sendiri.

### Perubahan schema yang direncanakan

Tambahkan unique index pada `carts.user_id` untuk menjamin invariant satu cart per user. Jika nanti diperlukan riwayat cart, ubah desain menjadi `status`/`checked_out_at` sebelum menghapus unique index.

## Endpoint yang direncanakan

Base path: `/api/cart`

| Method | Path | Akses | Tujuan |
|---|---|---|---|
| GET | `/api/cart` | Authenticated user | Mengambil current cart beserta item dan summary |
| DELETE | `/api/cart` | Authenticated user | Mengosongkan seluruh item current cart |

Tidak ada `POST /api/cart` atau `PATCH /api/cart` pada MVP: pembuatan dan perubahan cart terjadi sebagai efek dari operasi cart items. Ini menghindari user membuat beberapa cart aktif.

## Query dan response

`GET /api/cart` tidak memerlukan query wajib. Response yang direncanakan:

```json
{
  "status": "success",
  "message": "Cart retrieved successfully.",
  "data": {
    "id": "uuid",
    "items": [],
    "summary": {
      "item_count": 0,
      "total_quantity": 0,
      "subtotal_amount": "0.00",
      "currency": "IDR"
    },
    "created_at": "2026-08-12T00:00:00Z",
    "updated_at": "2026-08-12T00:00:00Z"
  }
}
```

Cart kosong tetap `200` dengan `items: []`; endpoint tidak mengembalikan `404` hanya karena cart belum pernah dibuat. Nilai uang dikirim sebagai string decimal untuk menghindari kehilangan presisi.

`DELETE /api/cart` mengembalikan cart yang sudah kosong atau `204` bila standar API memilih no-content. Pilih satu gaya dan gunakan konsisten dengan frontend.

## Perhitungan summary

- `item_count`: jumlah baris `cart_items`.
- `total_quantity`: total seluruh quantity.
- `subtotal_amount`: `sum(products.price * cart_items.quantity)` untuk item valid.
- Harga dibaca ulang saat request; jangan percaya total dari client.
- Item dengan product nonaktif/deleted harus ditandai atau dikeluarkan sesuai keputusan product policy, tetapi tidak boleh ikut checkout tanpa validasi ulang.

## Rencana implementasi

1. Tambahkan `Cart` model dengan `user()` dan `items()`.
2. Tambahkan `CartResource` dan transformer summary yang menghitung subtotal dari eager-loaded product.
3. Tambahkan `CartService::getCurrentCart(User $user)` dengan `firstOrCreate` yang dilindungi unique index.
4. Tambahkan `CartService::clear(Cart $cart)` dalam transaction.
5. Tambahkan `CartController@show` dan `CartController@clear`.
6. Daftarkan route di group `auth:sanctum`.
7. Gunakan eager loading `items.product.image` untuk mencegah N+1 query.
8. Tambahkan feature test untuk cart kosong, cart berisi item, isolasi antar-user, dan clear.

## Error contract

- `401` bila token tidak valid/tidak ada.
- `403` bila ada akses ke cart user lain.
- `404` hanya untuk resource cart eksplisit yang tidak ditemukan; current cart memakai lazy create.
- `409` bila terjadi race condition pembuatan cart dan unique constraint menang; service harus mengambil ulang cart.
- `500` untuk kegagalan tak terduga tanpa membocorkan detail database.

## Acceptance criteria

- Satu user tidak dapat memiliki dua current cart.
- User A tidak dapat membaca atau mengosongkan cart user B.
- Cart kosong dan cart berisi item memiliki response shape yang sama.
- Summary selalu dihitung server-side dari product saat request.
- Clear menghapus item dalam transaction dan aman dipanggil berulang kali.
- Checkout/order/payment tidak diimplementasikan di endpoint cart ini.

## Testing API

Request collection untuk pengujian manual/CI diletakkan di `docs/API/Cart/`:

- `Get Cart.yml`
- `Clear Cart.yml`

Jalankan setelah menyiapkan token user dan minimal satu cart item. Verifikasi bahwa cart user lain tidak dapat dibaca dan clear tidak menghapus data product.
