import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/plotly.js',
                'resources/js/dashboard.js',
                'resources/js/home.js',
                'resources/css/home.css',
                'resources/css/dashboard.css',
            ],
            refresh: true,
        }),
    ],
    build: {
        chunkSizeWarningLimit: 1200,
    },
});
