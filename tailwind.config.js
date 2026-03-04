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

    // LunaOS Custom Theme - Purple/Violet aesthetic (v2 - HIGH CONTRAST)
    daisyui: {
        themes: [
            "black",  // Base dark theme
            {
                lunaos: {
                    // Purple brand - kept vibrant but accessible
                    primary: "#a78bfa",      // Violet-400 - even brighter
                    "primary-focus": "#8b5cf6", // Violet-500
                    "primary-content": "#0f172a", // Dark text on purple
                    secondary: "#22d3ee",    // Cyan-400
                    "secondary-focus": "#06b6d4", // Cyan-500
                    "secondary-content": "#0f172a", // Dark text on cyan
                    accent: "#fb7185",       // Rose-400
                    "accent-focus": "#f43f5e", // Rose-500
                    "accent-content": "#ffffff",
                    // Neutrals - more separation between levels
                    neutral: "#1e293b",      // Slate-800
                    "neutral-focus": "#475569", // Slate-600
                    "neutral-content": "#ffffff", // Pure white text
                    // Base layers - lighter for better contrast
                    "base-100": "#1e293b",   // Slate-800 - main bg (was too dark)
                    "base-200": "#334155",   // Slate-700 - cards
                    "base-300": "#475569",   // Slate-600 - elevated
                    "base-content": "#ffffff", // PURE WHITE text (max contrast)
                    // State colors - bright and accessible
                    info: "#0ea5e9",         // Sky-500
                    success: "#22c55e",      // Green-500
                    warning: "#f59e0b",      // Amber-500
                    error: "#ef4444",        // Red-500
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
