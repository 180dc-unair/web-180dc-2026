# Plan Tugas CRUD Product dan Category

## Task 1 — CRUD Product Category

### Backend

* Membuat model `ProductCategory`.
* Membuat migration sesuai struktur database:

  * `id`
  * `name`
  * `slug`
  * `sort_order`
  * `timestamps`
* Membuat relasi kategori ke banyak produk.
* Membuat validasi:

  * `name` wajib diisi.
  * `slug` wajib unik.
  * `sort_order` berupa integer.
* Membuat endpoint:

```text
GET    /api/product-categories
GET    /api/product-categories/{slug}
POST   /api/product-categories
PATCH  /api/product-categories/{id}
DELETE /api/product-categories/{id}
```

### Functionality

* Menampilkan daftar kategori.
* Mencari kategori berdasarkan nama.
* Mengurutkan kategori berdasarkan `sort_order`.
* Menampilkan detail kategori.
* Membuat kategori baru.
* Mengubah nama, slug, dan urutan kategori.
* Menghapus kategori.
* Saat kategori dihapus, `category_id` pada produk menjadi `null`.

### Authorization

* `GET` dapat diakses guest, user, dan admin.
* `POST`, `PATCH`, dan `DELETE` menggunakan `auth:sanctum`.
* Aksi create, update, dan delete hanya dapat dilakukan admin.

---

## Task 2 — CRUD Product

### Backend

* Membuat model `Product`.
* Membuat migration sesuai struktur database:

  * `id`
  * `category_id`
  * `image_id`
  * `title`
  * `slug`
  * `type`
  * `status`
  * `short_description`
  * `description`
  * `price`
  * `stock`
  * `is_featured`
  * `is_best_seller`
  * `sold_count`
  * `digital_file_url`
  * `timestamps`
* Membuat relasi:

  * Produk memiliki satu kategori.
  * Produk memiliki satu media asset.
* Membuat validasi:

  * `title` wajib diisi.
  * `slug` wajib unik.
  * `type` hanya `digital` atau `physical`.
  * `status` hanya `active` atau `inactive`.
  * `price` berupa angka.
  * `stock` berupa integer.
  * `is_featured` dan `is_best_seller` berupa boolean.
  * `category_id` dan `image_id` harus valid jika diisi.
* Membuat endpoint:

```text
GET    /api/products
GET    /api/products/{slug}
POST   /api/products
PATCH  /api/products/{id}
DELETE /api/products/{id}
```

### Functionality

* Menampilkan daftar produk.
* Menampilkan detail produk berdasarkan slug.
* Membuat produk baru.
* Mengubah seluruh data produk.
* Menghapus produk.
* Menambahkan pencarian berdasarkan judul.
* Menambahkan filter:

  * Kategori.
  * Tipe produk.
  * Status.
  * Featured.
  * Best seller.
* Menambahkan sorting berdasarkan harga, waktu pembuatan, dan jumlah terjual.
* Menampilkan relasi kategori dan media asset pada response.

### Authorization

* Guest dan user biasa hanya dapat melihat produk dengan `status = active`.
* Admin dapat melihat produk aktif dan nonaktif.
* `POST`, `PATCH`, dan `DELETE` menggunakan `auth:sanctum`.
* Create, update, dan delete hanya dapat dilakukan admin.

---

## Task 3 — Laravel Sanctum Authorization

* Memastikan model `User` menggunakan `HasApiTokens`.
* Melindungi endpoint mutation dengan:

```text
auth:sanctum
```

* Membuat middleware atau policy untuk mengecek role admin.
* Mengembalikan:

  * `401 Unauthorized` jika belum login.
  * `403 Forbidden` jika sudah login tetapi bukan admin.
* Memastikan user biasa tidak dapat membuat, mengubah, atau menghapus kategori dan produk.

---

## Task 4 — Testing Category

* Guest dapat melihat daftar kategori.
* Guest dapat melihat detail kategori.
* User biasa tidak dapat membuat kategori.
* User biasa tidak dapat mengubah kategori.
* User biasa tidak dapat menghapus kategori.
* Admin dapat menjalankan seluruh CRUD kategori.
* Slug kategori tidak boleh duplikat.
* Penghapusan kategori tidak menghapus produk.

---

## Task 5 — Testing Product

* Guest hanya dapat melihat produk aktif.
* User biasa hanya dapat melihat produk aktif.
* Admin dapat melihat produk aktif dan nonaktif.
* User biasa tidak dapat membuat produk.
* User biasa tidak dapat mengubah produk.
* User biasa tidak dapat menghapus produk.
* Admin dapat menjalankan seluruh CRUD produk.
* Slug produk tidak boleh duplikat.
* Filter dan pencarian produk berjalan.
* Relasi kategori dan media asset tampil dengan benar.
