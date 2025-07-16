import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/css/instruction-paper.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
