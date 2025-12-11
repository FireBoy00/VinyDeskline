import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // General
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/bootstrap.js",
                "resources/css/general-admin.css",
                "resources/css/general-user.css",

                // Plotly (for charts)
                "resources/js/plotly.js",

                // Dashboard
                "resources/css/dashboard.css",
                "resources/js/dashboard.js",

                // Admin Account
                "resources/css/admin-account.css",
                "resources/js/admin-account.js",

                // Home
                "resources/css/home.css",
                "resources/js/home.js",

                // Login
                "resources/css/login.css",

                // Personalize
                "resources/css/personalize.css",

                // About & Help
                "resources/css/about.css",

                // Settings
                "resources/css/settings.css",
                "resources/js/settings.js",

                // Schedules
                "resources/css/schedules.css",

                // Desk Management
                "resources/css/desk-management.css",
                "resources/js/desk-management.js",

                // User Management
                "resources/css/user-management.css",
                "resources/js/user-management.js",
            ],
            refresh: true,
        }),
    ],
    build: {
        chunkSizeWarningLimit: 1200,
    },
});
