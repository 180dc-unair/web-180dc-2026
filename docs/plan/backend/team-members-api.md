# Planning Backend API — Team Members

Status: Draft

## Tujuan

Menyediakan CRUD standar untuk data anggota tim 180DC. Domain ini berdiri sendiri; tabel `history_timelines`, `team_divisions`, `team_positions`, dan `team_periods` tidak lagi menjadi dependency.

Schema sumber saat ini adalah migration `2026_06_25_100007_create_team_members_table`:

- `id` UUID primary key
- `image_id` nullable, relasi ke `media_assets`
- `name`, `slug` unique
- `email` nullable
- `linkedin_url`, `instagram_url` nullable
- `bio` nullable
- `is_active` default `true`
- timestamps

## Batasan akses

- `GET` dapat diakses publik dan hanya menampilkan member aktif secara default.
- `POST`, `PATCH`, dan `DELETE` memakai `auth:sanctum` + role `admin`, mengikuti pola route admin yang sudah ada di `routes/api.php`.
- Admin dapat meminta member nonaktif melalui filter eksplisit.
- Response mengikuti envelope yang sudah dipakai API: `status`, `message`, `data`.

## Endpoint yang direncanakan

Base path: `/api/team-members`

| Method | Path | Akses | Tujuan |
|---|---|---|---|
| GET | `/api/team-members` | Public | List member dengan pagination, filter, dan sorting |
| POST | `/api/team-members` | Admin | Membuat member |
| GET | `/api/team-members/{teamMember}` | Public | Detail member |
| PATCH | `/api/team-members/{teamMember}` | Admin | Mengubah sebagian/seluruh field member |
| DELETE | `/api/team-members/{teamMember}` | Admin | Menghapus member |

`{teamMember}` menggunakan UUID route model binding. `slug` tetap dikembalikan untuk kebutuhan URL frontend dan harus unik.

## Query list

- `page`, `per_page` dengan batas maksimum `100`.
- `search` mencari pada `name`, `email`, dan `bio`.
- `is_active` menerima `true`/`false`; tanpa parameter berarti hanya `true` untuk public.
- `sort` hanya mengizinkan `name` dan `created_at`.
- `direction` hanya `asc` atau `desc`.

Default: `name asc`.


## Request contract

### Create

```json
{
  "name": "Nama Anggota",
  "slug": "nama-anggota",
  "image_id": "uuid-opsional",
  "email": "anggota@example.com",
  "linkedin_url": "https://www.linkedin.com/in/example",
  "instagram_url": "https://instagram.com/example",
  "bio": "Bio singkat",
  "is_active": true
}
```

Rules utama:

- `name` wajib, string, panjang 1–255.
- `slug` wajib, lowercase/kebab-case, unique.
- `image_id` nullable dan harus ada di `media_assets` bila dikirim.
- URL sosial harus valid dan dibatasi ke host yang diizinkan bila policy keamanan mengharuskannya.

### Update

Semua field bersifat `sometimes`; validasi `slug` unique mengecualikan record yang sedang diubah. `image_id: null` boleh dipakai untuk melepas foto.

## Response contract

Detail:

```json
{
  "status": "success",
  "message": "Team member retrieved successfully.",
  "data": {
    "id": "uuid",
    "name": "Nama Anggota",
    "slug": "nama-anggota",
    "image": null,
    "email": "anggota@example.com",
    "linkedin_url": "https://www.linkedin.com/in/example",
    "instagram_url": "https://instagram.com/example",
    "bio": "Bio singkat",
    "is_active": true,
    "created_at": "2026-08-12T00:00:00Z",
    "updated_at": "2026-08-12T00:00:00Z"
  }
}
```

List memakai `data` collection dan metadata pagination Laravel (`current_page`, `last_page`, `per_page`, `total`). Jangan mengekspos password, token, atau kolom internal media.

## Error contract

- `401` unauthenticated.
- `403` bukan admin.
- `404` member tidak ditemukan.
- `422` validation error dengan `errors` per field.
- `409` bila slug bentrok dan tidak ditangani sebagai validation error.
- `500` hanya untuk kegagalan tak terduga; log tidak boleh memuat token.

## Rencana implementasi

1. Tambahkan `TeamMember` model dan relasi `image()` ke `MediaAsset`.
2. Tambahkan `TeamMemberResource`.
3. Tambahkan `StoreTeamMemberRequest` dan `UpdateTeamMemberRequest`.
4. Tambahkan service/repository bila mengikuti pola `ClientController`/`ProductController`; service menangani slug dan query list.
5. Tambahkan `TeamMemberController` dengan `index`, `store`, `show`, `update`, `destroy`.
6. Daftarkan route public dan route admin di `routes/api.php`.
7. Tambahkan feature test untuk auth, filter, pagination, validasi, slug unique, dan ownership role admin.
8. Tambahkan collection/request API docs setelah endpoint stabil.

## Acceptance criteria

- Public hanya melihat member aktif secara default.
- Admin dapat CRUD dan mengatur `is_active`.
- Slug tidak pernah duplikat.
- `image_id` invalid ditolak dan penghapusan member tidak menghapus `media_assets`.
- Semua endpoint mengembalikan envelope response yang konsisten.
- Test mencakup happy path dan seluruh error status di atas.

## Testing API

Request collection untuk pengujian manual/CI diletakkan di `docs/API/Team Members/`:

- `List Team Members.yml`
- `Create Team Member.yml`
- `Detail Team Member.yml`
- `Update Team Member.yml`
- `Delete Team Member.yml`

Urutan smoke test: list publik → create admin → detail → update → delete. Set `token` dan `team_member_id` pada environment/API client sebelum menjalankan request.
