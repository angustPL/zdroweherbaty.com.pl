import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
    },
    build: {
        rollupOptions: {
            output: {
                // Wyłącz preloadowanie dla JS aby uniknąć ostrzeżeń w konsoli
                manualChunks: undefined,
            },
        },
    },
    // Wyłącz automatyczne preloadowanie JS
    // Laravel Vite plugin automatycznie dodaje preload, ale możemy to kontrolować
    // poprzez modyfikację build manifest
});
