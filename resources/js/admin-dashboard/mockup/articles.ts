/**
 * Mockup data for Articles module.
 */

export interface Article {
    id: number;
    title: string;
    slug: string;
    excerpt: string;
    content: string;
    status: 'Published' | 'Draft' | 'Scheduled';
    category: string;
    author: string;
    featured_image: string | null;
    tags: string[];
    seo_title: string;
    seo_description: string;
    published_at: string | null;
    created_at: string;
    updated_at: string;
}

export const mockArticles: Article[] = [
    {
        id: 1,
        title: "Panduan Lengkap Laravel 12 untuk Pemula",
        slug: "panduan-lengkap-laravel-12-untuk-pemula",
        excerpt: "Belajar Laravel 12 dari nol dengan praktek langsung membuat aplikasi web modern.",
        content: "<p>Laravel 12 membawa banyak perubahan signifikan...</p>",
        status: "Published",
        category: "Tutorial",
        author: "Kvanjava",
        featured_image: "https://images.unsplash.com/photo-1518770660439-4636190af475?w=800",
        tags: ["PHP", "Laravel", "Web Development"],
        seo_title: "Belajar Laravel 12 dari Dasar | Kvanjava",
        seo_description: "Panduan lengkap belajar Laravel 12 untuk pemula beserta contoh kode dan praktik terbaik.",
        published_at: "2026-04-10 10:00:00",
        created_at: "2026-04-10 09:00:00",
        updated_at: "2026-04-18 12:00:00"
    },
    {
        id: 2,
        title: "Tips Optimasi Performa Vue 3",
        slug: "tips-optimasi-performa-vue-3",
        excerpt: "Cara mempercepat loading aplikasi Vue 3 Anda menggunakan teknik terbaru.",
        content: "<p>Optimasi performa sangat krusial dalam SPA...</p>",
        status: "Draft",
        category: "Frontend",
        author: "Admin",
        featured_image: null,
        tags: ["Vue", "Javascript", "Performance"],
        seo_title: "Optimasi Vue 3: Performa Kilat",
        seo_description: "Berbagai teknik optimasi Vue 3 untuk meningkatkan user experience.",
        published_at: null,
        created_at: "2026-04-15 14:30:00",
        updated_at: "2026-04-15 14:30:00"
    },
    {
        id: 3,
        title: "Masa Depan Web Development di 2026",
        slug: "masa-depan-web-development-di-2026",
        excerpt: "Prediksi teknologi web yang akan mendominasi industri di tahun ini.",
        content: "<p>Artificial Intelligence telah mengubah cara kita menulis kode...</p>",
        status: "Scheduled",
        category: "Insights",
        author: "Kvanjava",
        featured_image: "https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800",
        tags: ["AI", "Technology", "Future"],
        seo_title: "Web Development 2026: Apa yang Berubah?",
        seo_description: "Tren teknologi web terbaru di tahun 2026 yang wajib Anda ketahui.",
        published_at: "2026-05-01 08:00:00",
        created_at: "2026-04-17 11:20:00",
        updated_at: "2026-04-17 11:20:00"
    }
];
