# 02 — Article List (Blog Index)

## Visual Mockup

![Article List Mockup](/home/melodelavic/Documents/php/php8.2/laravel-admin-basic/plans/web_blog/mockups/article_list.png)

## Route

```
GET /articles  →  BlogController@index  →  blog.articles.index
```

Query params: `?search=`, `?category=`, `?page=`

## Layout Structure

```
┌─────────────────────────────────────────────────────────────┐
│                       Navbar                               │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Home > Articles                                           │
│  All Articles                                              │
│                                                             │
│  ┌─────────────────────┬──────────────┬─────────┐          │
│  │ 🔍 Search articles  │ [Category ▼] │ Filter  │          │
│  └─────────────────────┴──────────────┴─────────┘          │
│                                                             │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐                  │
│  │ Card     │ │ Card     │ │ Card     │                  │
│  │ [Image]  │ │ [Image]  │ │ [Image]  │                  │
│  │ [Badge]  │ │ [Badge]  │ │ [Badge]  │                  │
│  │ [Title]  │ │ [Title]  │ │ [Title]  │                  │
│  │ [Excerpt]│ │ [Excerpt]│ │ [Excerpt]│                  │
│  │ [Author] │ │ [Author] │ │ [Author] │                  │
│  └──────────┘ └──────────┘ └──────────┘                  │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐                  │
│  │  ...     │ │  ...     │ │  ...     │                  │
│  └──────────┘ └──────────┘ └──────────┘                  │
│                                                             │
│              [ 1 ] [ 2 ] [ 3 ] ... [ Next ]                │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                       Footer                               │
└─────────────────────────────────────────────────────────────┘
```

## Sections

### 1. Page Header

- Breadcrumb: Home > Articles
- Title: "All Articles" (`text-3xl font-bold`)
- Jika filter aktif, tambahkan context: "Showing results for 'keyword' in Technology"

### 2. Filter Bar

- **Search input**: Text field, placeholder "Search articles..."
- **Category dropdown**: Populated dari CategoryGroup "Artikel", default "All Categories"
- **Filter button**: Slate blue, trigger reload halaman dengan query params
- Filter state dipertahankan via URL query params (server-side)

### 3. Article Grid

- Grid: 3 kolom (desktop), 2 kolom (tablet), 1 kolom (mobile)
- **9 artikel per halaman**
- Setiap card: image (16:9 medium), category badge, title, excerpt (2 baris), author avatar + name + date
- Hover effect: subtle shadow increase dan slight translateY

### 4. Pagination

- Laravel built-in pagination dengan custom Tailwind styling
- Tampilkan nomor halaman, Previous, Next
- Preserve query params (search, category) saat pindah halaman

## Data Requirements

```php
// BlogController@index
$query = Article::where('status', 'published')
    ->with(['category', 'featuredImage', 'author']);

if ($request->filled('search')) {
    $query->where(function ($q) use ($request) {
        $q->where('title', 'like', '%' . $request->search . '%')
          ->orWhere('excerpt', 'like', '%' . $request->search . '%');
    });
}

if ($request->filled('category')) {
    $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
}

$articles = $query->latest('published_at')->paginate(9)->withQueryString();

$categories = Category::whereHas('group', fn($q) => $q->where('slug', 'artikel'))
    ->where('is_active', true)
    ->get();
```

## SEO

- Title: "All Articles — Blog" atau "Technology Articles — Blog" (jika filtered)
- Meta description: Dynamic berdasarkan filter atau generic
- Canonical URL: Include query params untuk filtered pages
