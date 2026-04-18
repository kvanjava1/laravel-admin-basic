# 02 - UI Components: Post Management (Articles)

## 1. Index Page (`ArticleIndex.vue`)
- **BasePageHeader**: Judul "Articles" dengan tombol "Create New Article".
- **DataTable**:
  - Kolom: Thumbnail, Title, Category, Author, Status, Published At.
  - **Action Row**: Menggunakan 3-dot menu (dropdown) berisi:
    - `visibility`: View Details
    - `edit`: Edit Article
    - `delete`: Delete (dengan konfirmasi modal)

## 1. Article Form Interface
Halaman Create/Edit akan menggunakan **Vertical One-Column Workflow** (identik dengan Media Management) untuk pengalaman menulis yang fokus.

### Panel 1: Article Content
- `BaseInput`: Judul Artikel.
- `BaseEditor (Tiptap)`: Rich Text Editor utama.
- **Media Integration**: Toolbar Tiptap terhubung ke Media Library Modal.
- `BaseInput`: Excerpt (Ringkasan pendek).

### Panel 2: Classification
- `BaseSelect`: Category (Filtered: Only categories where `category_group_id = 1`).
- `BaseTagsInput`: Article Tags (with Auto-Suggestions from `tagService`).

### Panel 3: Media & SEO
- `BaseMediaPicker`: Memilih gambar utama dari library Media.
- `BaseInput`: SEO Title (Target: 60 chars).
- `BaseInput`: SEO Description (Target: 160 chars).

### Panel 4: Publication
- `BaseSelect`: Status (Draft, Published, Scheduled).
- `BaseInput`: Publish Date & Time (muncul jika Scheduled).
