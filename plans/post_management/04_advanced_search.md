# 04 - Advanced Search: Post Management (Articles)

## Filter Interface
Modal atau panel Advanced Search akan mencakup:

1. **Keyword Search**: Mencari di kolom `title` dan `content`.
2. **Category Filter**: Dropdown untuk memilih kategori.
3. **Status Toggle**: Radio/Button group untuk `Published`, `Draft`, `Scheduled`.
4. **Author Filter**: Mencari berdasarkan nama penulis.
5. **Date Range**: Filter berdasarkan tanggal `created_at` atau `published_at`.

## Implementation Logic
- Filter akan dikelola di dalam composable `useArticleList.ts`.
- State filter akan bersifat reaktif; mengubah nilai filter akan mentrigger ulang fetching data.
- Pada tahap mockup, filtering akan dilakukan menggunakan fungsi `.filter()` pada array Javascript.
