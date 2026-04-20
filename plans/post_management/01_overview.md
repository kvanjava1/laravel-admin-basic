# 01 - Overview: Post Management

## Module Goal
Mengelola berbagai tipe konten (Post) dalam satu payung manajemen yang terpusat. Untuk fase awal, fokus utama adalah pada tipe konten **Articles**.

## Sidebar Structure
Menu akan disusun secara hierarkis di sidebar:
- **Post Management** (Parent Menu / Group)
  - **Articles** (Child Menu - Aktif)
  - **Other Types** (Placeholder untuk masa depan)

## Core Features (Articles)
1. **Article Index**: Tabel interaktif dengan manajemen baris (3-dot menu).
2. **Post Management**: Workflow pembuatan artikel yang terbagi dalam panel logis.
3. **Advanced Search**: Pencarian mendalam berdasarkan berbagai parameter metadata.
4. **Rich Editor**: Menggunakan **Tiptap** (Headless Editor) untuk pengalaman menulis yang premium dan terintegrasi dengan Media Library.
5. **Media Bridge & Ratio Security**: Pengguna wajib memilih rasio gambar (16:9/4:3/Original) saat insert media untuk menjaga integritas desain frontend.
6. **Media Standard**: Semua gambar (konten & featured) harus melalui Media Library untuk standarisasi cropping dan SEO.
7. **Advanced SEO Engine**: Dukungan penuh untuk Google SEO (Focus Keyword, Indexing) dan Social Media (Open Graph) terintegrasi.
8. **Mockup Driven**: Pengembangan awal menggunakan data dummy yang realistis.

## Architecture
- **Views**: `post/article/ArticleIndex.vue`, `post/article/ArticleCreate.vue`.
- **Services**: `articleService.ts`.
- **Composables**: `useArticleList.ts`, `useArticleForm.ts`.
