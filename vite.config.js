import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/scss/app.scss',
                'node_modules/glightbox/dist/css/glightbox.min.css',
                'resources/js/app.js',
                'resources/js/config.js',
                'resources/js/layout.js',
                'resources/js/pages/dashboard.js',
                'resources/js/pages/gallery.js',
            ],
            refresh: true,
        }),
    ],
});
