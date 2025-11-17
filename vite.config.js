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

                // Dashboard
                'resources/css/dashboard.css',
                'resources/js/dashboard.js',
                
                // Home
                'resources/css/home.css',
            ],
            refresh: true,
        }),
    ],
});
