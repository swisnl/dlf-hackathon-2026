import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            publicDirectory: 'public_html',
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        vue(),
        tailwindcss(),
    ],
    server: {
        ...(process.env.IS_DDEV_PROJECT ? {
            strictPort: true,
            host: true,
            hmr: {
                host: process.env.DDEV_HOSTNAME.split(',')[0],
                protocol: 'wss',
            },
            cors: {
                origin: process.env.DDEV_HOSTNAME.split(',').map((host) => new RegExp((host.startsWith('*.') ? host.substring(1) : host).replaceAll('.', '\\.'))),
            }
        } : {}),
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

