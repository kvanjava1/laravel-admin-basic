# 01 — Home Page

## Visual Mockup

![Home Page Mockup](/home/melodelavic/Documents/php/php8.2/laravel-admin-basic/plans/web_blog/mockups/home_page.png)

## Route

```
GET /  →  BlogController@home  →  blog.home
```

## Layout Structure

```
┌─────────────────────────────────────────────────────────────┐
│                     Navbar (sticky)                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │           HERO — Featured Article                    │   │
│  │  [16:9 Big Image with gradient overlay]             │   │
│  │  [Category Badge]                                    │   │
│  │  [Title — large white bold]                         │   │
│  │  [Excerpt — white/light]                            │   │
│  │  [Author • Date]                                    │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  Latest Articles                                           │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐                  │
│  │ Card 1   │ │ Card 2   │ │ Card 3   │                  │
│  │ [Image]  │ │ [Image]  │ │ [Image]  │                  │
│  │ [Badge]  │ │ [Badge]  │ │ [Badge]  │                  │
│  │ [Title]  │ │ [Title]  │ │ [Title]  │                  │
│  │ [Excerpt]│ │ [Excerpt]│ │ [Excerpt]│                  │
│  │ [Meta]   │ │ [Meta]   │ │ [Meta]   │                  │
│  └──────────┘ └──────────┘ └──────────┘                  │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐                  │
│  │ Card 4   │ │ Card 5   │ │ Card 6   │                  │
│  └──────────┘ └──────────┘ └──────────┘                  │
│                                                             │
│  [View All Articles →]                                     │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                       Footer                               │
└─────────────────────────────────────────────────────────────┘
```

## Sections

### 1. Hero — Featured Article

- Menampilkan **1 artikel published terbaru** atau yang di-flag sebagai featured
- Image: `ratio_16_9_big` (1600×900) dengan dark gradient overlay dari bawah
- Content overlay: category badge, title (text-4xl bold white), excerpt (text-lg white/70), author + date
- Full-width, min-height ~500px
- Klik → navigasi ke article detail

### 2. Latest Articles Grid

- Menampilkan **6 artikel published terbaru** (skip featured)
- Grid: 3 kolom desktop, 2 tablet, 1 mobile
- Menggunakan `article-card` component
- Tombol "View All Articles" di bawah grid → link ke `/articles`

## Data Requirements

```php
// BlogController@home
$featured = Article::where('status', 'published')
    ->with(['category', 'featuredImage', 'author'])
    ->latest('published_at')
    ->first();

$latest = Article::where('status', 'published')
    ->where('id', '!=', $featured?->id)
    ->with(['category', 'featuredImage', 'author'])
    ->latest('published_at')
    ->take(6)
    ->get();
```

## SEO

- Title: Site name (e.g., "Blog — Your Trusted News Source")
- Meta description: Deskripsi umum blog
- Open Graph image: Featured article image
