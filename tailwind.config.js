import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Dark theme colors
                'kotel-black': '#000000',
                'kotel-dark': '#0f172a', // slate-900
                'kotel-darker': '#0b1020',
                'kotel-gray': '#1e293b', // slate-800
                'kotel-gray-dark': '#334155', // slate-700
                'kotel-gray-medium': '#475569', // slate-600

                // Light theme colors
                'kotel-white': '#ffffff',
                'kotel-light': '#f8fafc', // slate-50
                'kotel-lighter': '#f1f5f9', // slate-100
                'kotel-light-medium': '#e2e8f0', // slate-200

                // Accent colors with proper contrast
                'kotel-yellow': '#f59e0b', // amber-500
                'kotel-yellow-dark': '#b45309', // amber-700
                'kotel-yellow-light': '#fde68a', // amber-300

                'kotel-sky-blue': '#3b82f6', // blue-500
                'kotel-sky-blue-dark': '#1d4ed8', // blue-700
                'kotel-sky-blue-light': '#93c5fd', // blue-300

                'kotel-green': '#22c55e', // green-500
                'kotel-green-dark': '#16a34a', // green-600
                'kotel-green-light': '#86efac', // green-300

                'kotel-red': '#ef4444', // red-500
                'kotel-red-dark': '#dc2626', // red-600
                'kotel-red-light': '#fca5a5', // red-300

                'kotel-purple': '#8b5cf6', // violet-500
                'kotel-purple-dark': '#7c3aed', // violet-600
                'kotel-purple-light': '#c084fc', // violet-300

                'kotel-orange': '#f97316', // orange-500
                'kotel-orange-dark': '#ea580c', // orange-700
                'kotel-orange-light': '#fb923c', // orange-400

                // Text colors for contrast
                'kotel-text-primary': '#f8fafc', // slate-50 (white text)
                'kotel-text-secondary': '#cbd5e1', // slate-300 (light gray text)
                'kotel-text-tertiary': '#94a3b8', // slate-400 (medium gray text)
                'kotel-text-muted': '#64748b', // slate-500 (dark gray text)

                // Background colors with proper contrast
                'kotel-bg-card': '#111827', // gray-900
                'kotel-bg-card-hover': '#1f2937', // gray-800
                'kotel-bg-sidebar': '#0b1220',
                'kotel-bg-header': '#0f172a',
                'kotel-bg-modal': '#0b1020',

                // Border colors
                'kotel-border': '#334155', // slate-700
                'kotel-border-light': '#475569', // slate-600
                'kotel-border-dark': '#1e293b', // slate-800
            },
            boxShadow: {
                'kotel-card': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
                'kotel-card-hover': '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
                'kotel-button': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
            },
        },
    },

    plugins: [forms, typography],
};
