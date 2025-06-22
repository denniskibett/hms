import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/index.js',
                'resources/js/bootstrap.js',
                'resources/js/components/calendar-init.js',
                'resources/js/components/image-resize.js',
                'resources/js/components/map-01.js',
                'resources/js/components/charts/chart-01.js',
                'resources/js/components/charts/chart-02.js',
                'resources/js/components/charts/chart-03.js'
            ],
            refresh: true,
        }),
    ],
});
