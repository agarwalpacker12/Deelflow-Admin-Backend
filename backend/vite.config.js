import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        port: 5174,
        host: '0.0.0.0',
        hmr: {
            port: 5176, // Use a different port for backend HMR to avoid conflicts
            host: '0.0.0.0', // Use 0.0.0.0 for Codespaces compatibility
        },
        cors: true, // Enable CORS for all origins in development
        headers: {
            'Access-Control-Allow-Origin': '*',
            'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers': 'Content-Type, Authorization',
        },
        ws: true, // Enable WebSocket support
    }
});
