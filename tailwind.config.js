import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class', // Enable class-based dark mode
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Outfit', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Space Grotesk"', 'Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // ── Cream & Brown palette ──────────────────────
                dark:        '#3B2417',   // dark cocoa  — body text / headings
                card:        '#F5E6CE',   // soft cream   — card backgrounds
                borderdark:  '#E3C79A',   // sand beige   — borders in dark contexts
                light:       '#FFF8ED',   // ivory cream  — main background
                cardlight:   '#F5E6CE',   // soft cream   — card background alias
                borderlight: '#E3C79A',   // sand beige   — borders
                border:      '#E3C79A',   // sand beige   — generic border alias
                // Primary action / CTA colour
                pinterest:   '#8B5E3C',   // warm brown   — replaces old pinterest red
                // Accent scale
                accent: {
                    100: '#f0dcc4',
                    200: '#e8cfa8',
                    300: '#dbb882',
                    400: '#c69c6d',
                    500: '#8B5E3C',
                    600: '#7a5234',
                    700: '#5c3a21',
                    800: '#3b2417',
                    900: '#1e1009',
                },
                // Caramel tan for secondary badges
                caramel:      '#C69C6D',
                espresso:     '#5C3A21',
                cocoa:        '#3B2417',
                cream:        '#FFF8ED',
                'soft-cream': '#F5E6CE',
                sand:         '#E3C79A',
                brown:        '#8B5E3C',
            },
            boxShadow: {
                'minimal':      '0 2px 12px rgba(91,58,33,0.07)',
                'minimal-dark': '0 2px 12px rgba(91,58,33,0.18)',
                'warm':         '0 4px 20px rgba(139,94,60,0.12)',
            },
            animation: {
                'fade-in': 'fadeIn 0.25s ease forwards',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                }
            }
        },
    },

    plugins: [forms],
};
