import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'localhost',
            // Ajoutez ces options
            protocol: 'ws',
            overlay: true
        },
        // Ajoutez cette option pour les proxys
        cors: true,
        // Force le rechargement complet
        watch: {
            usePolling: true,
        }
    },
});
