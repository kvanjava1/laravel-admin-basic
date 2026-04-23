# 01 (Alt) — Home Page (Vertical List)

## Visual Mockup

![Home Page Vertical Mockup](/home/melodelavic/Documents/php/php8.2/laravel-admin-basic/plans/web_blog/mockups/home_page_vertical.png)

## Deskripsi
Alternatif desain Home Page ini mengganti grid artikel terbaru menjadi daftar vertikal. Cocok untuk portal berita yang ingin menonjolkan ringkasan artikel.

## Layout Structure

```
┌─────────────────────────────────────────────────────────────┐
│                     Navbar (sticky)                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  [ HERO — Featured Article ]                                │
│                                                             │
│  Latest Articles                                           │
│                                                             │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ ┌──────────┐  [Category Badge]                        │  │
│  │ │          │  Article Title — text-xl bold            │  │
│  │ │  Image   │  Excerpt text (3-4 lines)...             │  │
│  │ │  (4:3)   │                                          │  │
│  │ │          │  👤 Author • Oct 26, 2023                │  │
│  │ └──────────┘                                          │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                             │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ ... (Next Article Card)                               │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                             │
│  [View All Articles →]                                     │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                       Footer                               │
└─────────────────────────────────────────────────────────────┘
```

## Perbedaan Teknis dengan Grid

- **Image Variant**: Menggunakan `ratio_4_3_medium` untuk thumbnail di sisi kiri agar tidak terlalu memakan ruang vertikal.
- **Card Component**: Membutuhkan komponen Blade baru `blog/partials/article-card-horizontal.blade.php`.
- **Responsive**: Di mobile, layout ini otomatis akan menumpuk (Stack) gambar di atas teks kembali.
