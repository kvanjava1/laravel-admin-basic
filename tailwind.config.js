import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    safelist: [
        // Figure & Images
        'my-8', 'mx-auto', 'max-w-full',
        'rounded-2xl', 'border', 'border-slate-200', 'shadow-sm', 'h-auto',
        
        // Figcaption & Typography
        'text-left', 'text-center', 'text-right', 'text-sm', 'text-slate-500', 'mt-3', 'font-medium',
        
        // Potential alignments for other content
        'mx-0', 'ml-auto', 'mr-auto'
    ],

    theme: {
        extend: {
            colors: {
                "primary": "#526D82",
                "background-light": "#DDE6ED",
                "sidebar-bg": "#27374D",
                "surface-card": "#ffffff",
                "text-primary": "#27374D",
                "text-secondary": "#526D82",
                "accent-purple": "#9DB2BF",
                "accent-teal": "#526D82",
                "border-light": "#9DB2BF",
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Inter', 'sans-serif'],
            },
            borderRadius: {
                "DEFAULT": "0.25rem",
                "lg": "0.5rem",
                "xl": "0.75rem",
                "full": "9999px"
            },
            boxShadow: {
                "card": "0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)",
                "card-hover": "0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)"
            }
        },
    },

    plugins: [forms],
};
