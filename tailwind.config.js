import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './vendor/robsontenorio/mary/src/View/Components/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [
        forms,
        require('daisyui')
    ],

    daisyui: {
        themes: [
            "black",
            {
                lunaos: {
                    "colorScheme": "dark",
                    "primary": "#a78bfa",
                    "primary-content": "#0f172a",
                    "secondary": "#22d3ee",
                    "secondary-content": "#0f172a",
                    "accent": "#fb7185",
                    "accent-content": "#ffffff",
                    "neutral": "#1e293b",
                    "neutral-content": "#ffffff",
                    "base-100": "#1e293b",
                    "base-200": "#334155",
                    "base-300": "#475569",
                    "base-content": "#ffffff",
                    "info": "#0ea5e9",
                    "info-content": "#ffffff",
                    "success": "#22c55e",
                    "success-content": "#ffffff",
                    "warning": "#f59e0b",
                    "warning-content": "#ffffff",
                    "error": "#ef4444",
                    "error-content": "#ffffff",
                },
            },
        ],
        darkTheme: "lunaos",
        base: true,
        styled: true,
        utils: true,
        logs: false,
    },
};
