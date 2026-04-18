export interface Media {
    id: number;
    title: string;
    alt_text: string;
    slug: string;
    original_path: string;
    path_1200: string;
    path_800: string;
    path_300: string;
    size: number;
    mime_type: string;
    created_at: string;
}

export const mockMedia: Media[] = [
    {
        id: 1,
        title: 'Sepatu Lari Nike Pro Blue',
        alt_text: 'Sepatu lari warna biru merk Nike Pro',
        slug: 'sepatu-lari-nike-pro-blue',
        original_path: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=2070&auto=format&fit=crop',
        path_1200: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=1200&auto=format&fit=crop',
        path_800: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=800&auto=format&fit=crop',
        path_300: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=300&auto=format&fit=crop',
        size: 5242880,
        mime_type: 'image/webp',
        created_at: '2026-03-18 21:00:00'
    },
    {
        id: 2,
        title: 'Laptop Gaming ROG Strix',
        alt_text: 'Laptop gaming Asus ROG Strix terbaru',
        slug: 'laptop-gaming-rog-strix',
        original_path: 'https://images.unsplash.com/photo-1593642702821-c8da6771f0c6?q=80&w=2064&auto=format&fit=crop',
        path_1200: 'https://images.unsplash.com/photo-1593642702821-c8da6771f0c6?q=80&w=1200&auto=format&fit=crop',
        path_800: 'https://images.unsplash.com/photo-1593642702821-c8da6771f0c6?q=80&w=800&auto=format&fit=crop',
        path_300: 'https://images.unsplash.com/photo-1593642702821-c8da6771f0c6?q=80&w=300&auto=format&fit=crop',
        size: 3145728,
        mime_type: 'image/webp',
        created_at: '2026-03-17 10:30:00'
    },
    {
        id: 3,
        title: 'Kamera Mirrorless Sony A7IV',
        alt_text: 'Kamera professional Sony A7 mark IV',
        slug: 'kamera-mirrorless-sony-a7iv',
        original_path: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=2076&auto=format&fit=crop',
        path_1200: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=1200&auto=format&fit=crop',
        path_800: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=800&auto=format&fit=crop',
        path_300: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=300&auto=format&fit=crop',
        size: 4194304,
        mime_type: 'image/webp',
        created_at: '2026-03-16 15:45:00'
    }
];
