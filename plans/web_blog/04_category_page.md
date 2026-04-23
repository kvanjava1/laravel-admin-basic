# 04 — Category Page

## Visual Mockup

![Category Page Mockup](/home/melodelavic/Documents/php/php8.2/laravel-admin-basic/plans/web_blog/mockups/category_page.png)

## Route

```
GET /categories/{slug}  →  BlogCategoryController@show  →  blog.categories.show
```

## Layout Structure

```
┌─────────────────────────────────────────────────────────────┐
│                       Navbar                               │
├─────────────────────────────────────────────────────────────┤
│  Home > Categories > Technology                            │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  CATEGORY HEADER (dark navy bg)                      │   │
│  │  Technology                          — text-3xl bold │   │
│  │  Articles about software...          — text lighter  │   │
│  │  24 Articles                         — text-sm       │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐                  │
│  │ Card     │ │ Card     │ │ Card     │                  │
│  │ [Image]  │ │ [Image]  │ │ [Image]  │                  │
│  │ [Title]  │ │ [Title]  │ │ [Title]  │                  │
│  │ [Excerpt]│ │ [Excerpt]│ │ [Excerpt]│                  │
│  │ [Author] │ │ [Author] │ │ [Author] │                  │
│  └──────────┘ └──────────┘ └──────────┘                  │
│                                                             │
│              [ 1 ] [ 2 ] [ 3 ] ... [ Next ]                │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                       Footer                               │
└─────────────────────────────────────────────────────────────┘
```

## Sections

### 1. Category Header Banner

- Background: `bg-sidebar-bg` (dark navy #27374D) dengan `rounded-2xl`
- Category name: `text-3xl font-bold text-white`
- Description: Bisa diambil dari field tambahan di Category model, atau static text
- Article count: `text-sm text-slate-300`, contoh "24 Articles"

### 2. Article Grid

- Sama dengan Article List page, tapi tanpa filter bar
- Grid: 3 kolom (desktop), 2 (tablet), 1 (mobile)
- **9 artikel per halaman**
- Category badge tetap ditampilkan di card (untuk konsistensi visual)

### 3. Pagination

- Sama dengan Article List
- Route tetap di `/categories/{slug}?page=N`

## Data Requirements

```php
// BlogCategoryController@show
$category = Category::where('slug', $slug)
    ->where('is_active', true)
    ->firstOrFail();

$articles = Article::where('status', 'published')
    ->where('category_id', $category->id)
    ->with(['category', 'featuredImage', 'author'])
    ->latest('published_at')
    ->paginate(9);
```

## SEO

- Title: "{Category Name} — Blog"
- Meta description: Category description atau "Read the latest {category} articles"
- Canonical: `/categories/{slug}`
