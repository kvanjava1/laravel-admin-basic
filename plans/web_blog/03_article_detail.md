# 03 — Article Detail (Single Post)

## Visual Mockup

![Article Detail Mockup](/home/melodelavic/Documents/php/php8.2/laravel-admin-basic/plans/web_blog/mockups/article_detail.png)

## Route

```
GET /articles/{slug}  →  BlogController@show  →  blog.articles.show
```

## Layout Structure

```
┌─────────────────────────────────────────────────────────────┐
│                       Navbar                               │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│       ┌───────────────────────────────────────┐             │
│       │ Home > Articles > Category            │             │
│       │                                       │             │
│       │ [Category Badge]                      │             │
│       │ Article Title Here — text-4xl bold    │             │
│       │                                       │             │
│       │ 👤 Author Name • Jan 22, 2026 • 5 min│             │
│       │                                       │             │
│       │ ┌──────────────────────────────────┐  │             │
│       │ │   Featured Image (16:9 big)      │  │             │
│       │ └──────────────────────────────────┘  │             │
│       │ Image caption — text-sm italic        │             │
│       │                                       │             │
│       │ Article body content...               │             │
│       │ Paragraph text with relaxed leading   │             │
│       │                                       │             │
│       │ ## Subheading H2                      │             │
│       │                                       │             │
│       │ > Blockquote with left border         │             │
│       │                                       │             │
│       │ More content paragraphs...            │             │
│       │                                       │             │
│       │ ┌──────────────────────────────────┐  │             │
│       │ │ Tags: [#tag1] [#tag2] [#tag3]    │  │             │
│       │ └──────────────────────────────────┘  │             │
│       │                                       │             │
│       │ Share: [FB] [TW] [LI] [Copy Link]    │             │
│       │                                       │             │
│       └───────────────────────────────────────┘             │
│                                                             │
│  ─────────────────────────────────────────────              │
│                                                             │
│  Related Articles                                          │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐                  │
│  │ Card     │ │ Card     │ │ Card     │                  │
│  └──────────┘ └──────────┘ └──────────┘                  │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                       Footer                               │
└─────────────────────────────────────────────────────────────┘
```

## Sections

### 1. Article Header

- Breadcrumb: Home > Articles > {Category Name}
- Category badge (colored)
- Title: `text-4xl font-bold text-text-primary`
- Author info row: Avatar (small circle), name, published date, estimated reading time
- Reading time: `ceil(str_word_count(strip_tags($article->content)) / 200)` menit

### 2. Featured Image

- Image: `ratio_16_9_big` (1600×900) dengan `rounded-2xl`
- Caption dari `featured_image_caption` (jika ada) — `text-sm italic text-text-secondary`
- Alt text dari `featured_image_alt`

### 3. Article Body

- Render `$article->content` (rich HTML dari Tiptap editor)
- Styling via Tailwind typography plugin atau custom prose classes:
  - Paragraphs: `text-base leading-relaxed text-text-primary mb-6`
  - H2: `text-2xl font-bold text-text-primary mt-10 mb-4`
  - H3: `text-xl font-semibold text-text-primary mt-8 mb-3`
  - Blockquote: `border-l-4 border-primary pl-4 italic text-text-secondary`
  - Images dalam content: `rounded-2xl my-8 max-w-full` (sudah ada safelist di tailwind.config.js)
  - Links: `text-primary hover:underline`
  - Lists: Styled `ul`/`ol` dengan proper spacing
- Container max-width: `max-w-3xl mx-auto` (~768px) untuk optimal reading width

### 4. Tags

- Row of tag pills/badges
- Setiap tag clickable → navigasi ke `/articles?tag={slug}` (atau dedicated tag page)
- Styling: `px-3 py-1 bg-slate-100 text-text-secondary text-sm rounded-full`

### 5. Share Section

- Label "Share" dengan icon buttons:
  - Facebook share (URL-based, no SDK needed)
  - Twitter/X share
  - LinkedIn share
  - Copy link button (clipboard API)
- Styling: Icon buttons `w-10 h-10 rounded-full bg-slate-100 hover:bg-primary hover:text-white`

### 6. Related Articles

- **3 artikel** dari kategori yang sama, exclude current article
- Jika kurang dari 3, fill dari latest articles
- Grid: 3 kolom desktop, 1-2 kolom mobile
- Menggunakan `article-card` component

## Data Requirements

```php
// BlogController@show
$article = Article::where('slug', $slug)
    ->where('status', 'published')
    ->with(['category', 'featuredImage', 'author', 'tags'])
    ->firstOrFail();

$related = Article::where('status', 'published')
    ->where('id', '!=', $article->id)
    ->where('category_id', $article->category_id)
    ->with(['category', 'featuredImage', 'author'])
    ->latest('published_at')
    ->take(3)
    ->get();

// Fill with latest if not enough related
if ($related->count() < 3) {
    $fill = Article::where('status', 'published')
        ->whereNotIn('id', $related->pluck('id')->push($article->id))
        ->with(['category', 'featuredImage', 'author'])
        ->latest('published_at')
        ->take(3 - $related->count())
        ->get();
    $related = $related->merge($fill);
}
```

## SEO

- Title: `$article->seo_title ?: $article->title . ' — Blog'`
- Meta description: `$article->seo_description ?: Str::limit(strip_tags($article->excerpt ?: $article->content), 160)`
- Meta keywords: `$article->seo_focus_keyword`
- Meta robots: `$article->is_indexable ? 'index, follow' : 'noindex, nofollow'`
- Open Graph: title, description, featured image URL, article type
- Canonical: Full article URL
- Schema.org: `Article` structured data (author, datePublished, image)
