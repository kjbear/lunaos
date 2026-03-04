import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class', // Enable class-based dark mode
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

    // LunaOS Custom Theme - Purple/Violet aesthetic
    daisyui: {
        themes: [
            "black",  // Base dark theme
            {
                lunaos: {
                    primary: "#7c3aed",      // Violet-600
                    "primary-focus": "#6d28d9", // Violet-700
                    "primary-content": "#ffffff",
                    secondary: "#06b6d4",    // Cyan-500
                    "secondary-focus": "#0891b2", // Cyan-600
                    "secondary-content": "#ffffff",
                    accent: "#f43f5e",       // Rose-500
                    "accent-focus": "#e11d48", // Rose-600
                    "accent-content": "#ffffff",
                    neutral: "#1e1e3f",      // Custom dark
                    "neutral-focus": "#2a2a50",
                    "neutral-content": "#e4e4f0",
                    "base-100": "#0f0f1a",   // LunaOS background
                    "base-200": "#12121f",   // LunaOS sidebar
                    "base-300": "#1a1a2e",   // Slightly lighter
                    "base-content": "#e4e4f0",
                    info: "#3abff8",
                    success: "#36d399",
                    warning: "#fbbf24",
                    error: "#f87272",
                },
            },
        ],
        darkTheme: "lunaos",
        base: true,
        styled: true,
        utils: true,
        prefix: "",
        logs: false,
    },
};
