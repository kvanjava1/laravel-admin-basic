# 05 — Search Results

## Visual Mockup

![Search Results Mockup](/home/melodelavic/Documents/php/php8.2/laravel-admin-basic/plans/web_blog/mockups/search_results.png)

## Route

```
GET /search?q={keyword}  →  BlogSearchController@index  →  blog.search
```

## Layout Structure

```
┌─────────────────────────────────────────────────────────────┐
│                       Navbar                               │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│     ┌───────────────────────────────────────┬────┐         │
│     │ web development                       │ 🔍 │         │
│     └───────────────────────────────────────┴────┘         │
│                                                             │
│  12 results for 'web development'                          │
│                                                             │
│  ┌──────────────────────────────────────────────────┐      │
│  │ [Thumb] Title Here                                │      │
│  │         Excerpt with **highlighted** keyword...   │      │
│  │         [Category] • Author • Date                │      │
│  └──────────────────────────────────────────────────┘      │
│  ┌──────────────────────────────────────────────────┐      │
│  │ [Thumb] Title Here                                │      │
│  │         Excerpt with **highlighted** keyword...   │      │
│  │         [Category] • Author • Date                │      │
│  └──────────────────────────────────────────────────┘      │
│  ...                                                        │
│                                                             │
│              [ 1 ] [ 2 ] [ 3 ] ... [ Next ]                │
│                                                             │
│  ───── EMPTY STATE (jika 0 results) ─────                  │
│  🔍                                                        │
│  No articles found for 'keyword'                           │
│  Try different keywords                                    │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                       Footer                               │
└─────────────────────────────────────────────────────────────┘
```

## Sections

### 1. Search Bar (Prominent)

- Full-width search input, centered, larger than navbar search
- Pre-filled dengan query saat ini (`$request->q`)
- Submit via form GET ke `/search`
- Button: slate blue search icon button

### 2. Results Count

- Format: `{count} results for '{keyword}'` atau `No articles found for '{keyword}'`
- `text-lg font-semibold`

### 3. Results List (Horizontal Cards)

- **Layout: list, bukan grid** — setiap result adalah horizontal card
- Card: thumbnail kiri (4:3 medium, `w-32 h-24 rounded-xl`), content kanan
- Content: title (bold, link), excerpt (2 baris, keyword highlighted `bg-yellow-100`), category badge + author + date
- **10 results per halaman**

### 4. Empty State

- Icon search besar (`text-6xl text-slate-300`)
- Message: "No articles found for '{keyword}'"
- Sub-message: "Try different keywords or browse by category"
- Link ke `/articles`

### 5. Pagination

- Sama dengan halaman lain, preserve `?q={keyword}&page=N`

## Data Requirements

```php
// BlogSearchController@index
$keyword = $request->input('q', '');

if (empty($keyword)) {
    return view('blog.search', ['articles' => collect(), 'keyword' => '']);
}

$articles = Article::where('status', 'published')
    ->where(function ($q) use ($keyword) {
        $q->where('title', 'like', "%{$keyword}%")
          ->orWhere('excerpt', 'like', "%{$keyword}%")
          ->orWhere('content', 'like', "%{$keyword}%");
    })
    ->with(['category', 'featuredImage', 'author'])
    ->latest('published_at')
    ->paginate(10)
    ->withQueryString();
```

## Keyword Highlighting Helper

```php
// In a Blade helper or accessor
function highlightKeyword(string $text, string $keyword): string
{
    if (empty($keyword)) return $text;
    return preg_replace(
        '/(' . preg_quote($keyword, '/') . ')/i',
        '<mark class="bg-yellow-100 px-0.5 rounded">$1</mark>',
        e(Str::limit(strip_tags($text), 200))
    );
}
```

## SEO

- Title: "Search: '{keyword}' — Blog"
- Meta robots: `noindex, follow` (halaman search tidak perlu diindeks)
