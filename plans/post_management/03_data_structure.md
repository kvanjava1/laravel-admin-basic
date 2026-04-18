# 03 - Data Structure: Post Management (Articles)

## 1. Main Table: `articles`
Tabel utama untuk menyimpan konten artikel.

| Field | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt | Primary Key. |
| `title` | String | Judul Artikel. |
| `slug` | String | URL friendly slug (Unique). |
| `content` | LongText | Isi utama artikel (HTML). |
| `excerpt` | Text | Ringkasan pendek artikel. |
| `status_id` | ForeignID | Relasi ke `article_statuses`. |
| `category_id`| ForeignID | Relasi ke `categories` (Scoped to `category_group_id = 1`). |
| `featured_image`| String | Path ke image utama. |
| `seo_title` | String | Meta title untuk SEO. |
| `seo_desc` | String | Meta description untuk SEO. |
| `created_by` | ForeignID | User yang membuat artikel. |
| `published_by`| ForeignID | User yang terakhir mempublikasi. |
| `deleted_by` | ForeignID | User yang melakukan penghapusan. |
| `published_at`| DateTime | Waktu tayang artikel. |
| `created_at` | DateTime | Waktu pembuatan record. |
| `updated_at` | DateTime | Waktu perubahan terakhir. |
| `deleted_at` | DateTime | Soft delete timestamp. |

## 2. Reference Table: `article_statuses`
Tabel referensi untuk status publikasi artikel.

| Field | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt | Primary Key. |
| `name` | String | Nama status (`Draft`, `Published`, `Scheduled`, `Archived`). |
| `label` | String | Label untuk UI. |
| `color_class`| String | Warna badge di UI. |

## 3. History Table: `article_histories`
Tabel untuk mencatat setiap perubahan signifikan pada artikel.

### Metadata Convention:
- **Action `created`**: `{ "article": { ... data awal ... } }`
- **Action `updated`**: `{ "before": { ... }, "after": { ... } }` (Hanya field yang berubah)
- **Action `deleted`**: `{ "article": { ... snapshot terakhir ... } }`

| Field | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt | Primary Key. |
| `article_id` | ForeignID | Relasi ke `articles`. |
| `user_id` | ForeignID | Pelaku aksi. |
| `action` | String | `created`, `updated`, `published`, `unpublished`, `deleted`, `restored`. |
| `note` | Text | Catatan tambahan. |
| `metadata` | JSON | Perbandingan data sesuai konvensi di atas. |
| `created_at` | DateTime | Waktu aksi dilakukan. |

## 4. Pivot Table: `article_tag`
Tabel penghubung Many-to-Many antara Artikel dan Tags.

| Field | Type | Description |
| :--- | :--- | :--- |
| `article_id` | ForeignID | Relasi ke `articles`. |
| `tag_id` | ForeignID | Relasi ke `tags`. |

## 4. Mockup Integration
File `@/mockup/articles.ts` akan disesuaikan untuk mencerminkan struktur relasi ID ini agar transisi ke API asli menjadi mulus.
