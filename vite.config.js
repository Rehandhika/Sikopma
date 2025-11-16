import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [
                'resources/views/**',
                'app/Livewire/**',
            ],
        }),
        tailwindcss(),
    ],
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks: {
                    charts: [
                        'chart.js',
                    ],
                    forms: [
                        'flatpickr',
                        'tom-select',
                    ],
                    utils: [
                        'sortablejs',
                        'filepond',
                    ],
                },
            },
        },
    },
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});
