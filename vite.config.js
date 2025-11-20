import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // General
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/bootstrap.js',

                // Admin Pages
                'resources/css/admin-pages.css',
                
                // Plotly (for charts)
                'resources/js/plotly.js',

                // Dashboard
                'resources/css/dashboard.css',
                'resources/js/dashboard.js',
                
                // Home
                'resources/css/home.css',
                'resources/js/home.js',

                // Login
                'resources/css/login.css',

                // Personalize
                'resources/css/personalize.css',

                // Schedules
                'resources/css/schedules.css',
            ],
            refresh: true,
        }),
    ],
    build: {
        chunkSizeWarningLimit: 1200,
    },
});
