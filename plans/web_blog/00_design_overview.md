# Web Blog Frontend — Design Overview

## Tujuan

Public-facing blog frontend untuk menampilkan artikel yang dikelola dari admin dashboard. Gaya: **news-article blog** dengan kategori. Menggunakan **Blade templates** dan **Tailwind CSS**.

## Halaman Yang Akan Dibuat

| # | Halaman | File Blade | Route | Deskripsi |
|---|---------|------------|-------|-----------|
| 1 | Home | `blog/home.blade.php` | `GET /` | Landing page: hero featured article, latest articles grid, category highlights |
| 2 | Article List | `blog/articles/index.blade.php` | `GET /articles` | Paginated article grid dengan search dan category filter |
| 3 | Article Detail | `blog/articles/show.blade.php` | `GET /articles/{slug}` | Full article content, author, tags, related posts |
| 4 | Category Page | `blog/categories/show.blade.php` | `GET /categories/{slug}` | Articles filtered by category |
| 5 | Search Results | `blog/search.blade.php` | `GET /search?q=` | Hasil pencarian artikel |
| 6 | 404 Page | `errors/404.blade.php` | - | Custom error page |

## Design System

### Color Palette

Menggunakan color palette yang sudah ada di `tailwind.config.js` + tambahan untuk public blog:

| Token | Value | Usage |
|-------|-------|-------|
| `sidebar-bg` / `#27374D` | Dark navy | Navbar, footer background |
| `primary` / `#526D82` | Slate blue | Links, accents, buttons |
| `background-light` / `#DDE6ED` | Light gray | Page background |
| `surface-card` / `#ffffff` | White | Card backgrounds |
| `text-primary` / `#27374D` | Dark navy | Headings, body text |
| `text-secondary` / `#526D82` | Slate blue | Metadata, captions |

### Typography

- **Font**: Inter (sudah dikonfigurasi di Tailwind)
- **Heading 1**: `text-4xl font-bold` (article title)
- **Heading 2**: `text-2xl font-bold` (section headings)
- **Body**: `text-base leading-relaxed` (article content)
- **Caption**: `text-sm text-text-secondary` (dates, author, metadata)

### Layout Structure

```
┌──────────────────────────────────────────┐
│              Navbar (sticky)             │
│  Logo    Home  Articles  Categories  🔍  │
├──────────────────────────────────────────┤
│                                          │
│              Page Content                │
│                                          │
├──────────────────────────────────────────┤
│              Footer                      │
│  About  Links  Categories  Social  ©    │
└──────────────────────────────────────────┘
```

### Shared Components (Blade)

| Component | File | Purpose |
|-----------|------|---------|
| Layout | `layouts/blog.blade.php` | Base layout: navbar + content + footer |
| Navbar | `blog/partials/navbar.blade.php` | Top navigation with category links, search |
| Footer | `blog/partials/footer.blade.php` | Site info, category links, copyright |
| Article Card | `blog/partials/article-card.blade.php` | Reusable card: thumbnail, title, excerpt, meta |
| Article Card Featured | `blog/partials/article-card-featured.blade.php` | Large hero card for featured article |
| Category Badge | `blog/partials/category-badge.blade.php` | Colored category label |
| Pagination | `blog/partials/pagination.blade.php` | Page navigation |
| Search Bar | `blog/partials/search-bar.blade.php` | Search input component |

### Responsive Breakpoints

| Breakpoint | Grid | Description |
|------------|------|-------------|
| Mobile (`< 640px`) | 1 column | Single column, stacked layout |
| Tablet (`640px - 1024px`) | 2 columns | Two-column article grid |
| Desktop (`> 1024px`) | 3 columns | Three-column article grid, sidebar optional |

## Page Mockup Details

Lihat file individual untuk wireframe dan detail setiap halaman:

- `01_home.md` — Home / Landing Page
- `02_article_list.md` — Article List (Blog Index)
- `03_article_detail.md` — Article Detail (Single Post)
- `04_category_page.md` — Category Page
- `05_search_results.md` — Search Results
- `06_404_page.md` — 404 Error Page

## Route Plan (web.php)

```php
// Public Blog Routes
Route::get('/', [BlogController::class, 'home'])->name('blog.home');
Route::get('/articles', [BlogController::class, 'index'])->name('blog.articles.index');
Route::get('/articles/{slug}', [BlogController::class, 'show'])->name('blog.articles.show');
Route::get('/categories/{slug}', [BlogCategoryController::class, 'show'])->name('blog.categories.show');
Route::get('/search', [BlogSearchController::class, 'index'])->name('blog.search');
```

## Image Usage

Artikel menggunakan media dari system Media yang sudah ada. Variant yang digunakan di public blog:

| Context | Variant | Ukuran |
|---------|---------|--------|
| Hero / Featured | `ratio_16_9_big` | 1600×900 |
| Article Card | `ratio_16_9_medium` | 800×450 |
| Article Detail | `ratio_16_9_big` | 1600×900 |
| Thumbnail (sidebar) | `ratio_4_3_medium` | 800×600 |
