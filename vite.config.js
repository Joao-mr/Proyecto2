import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            // reactivityTransform: true,
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (!id.includes('node_modules')) return;

                    if (id.includes('primevue') || id.includes('@primevue') || id.includes('primeicons')) {
                        return 'vendor-primevue';
                    }

                    if (id.includes('vue') || id.includes('pinia') || id.includes('vue-router')) {
                        return 'vendor-vue';
                    }

                    if (id.includes('bootstrap')) {
                        return 'vendor-bootstrap';
                    }

                    if (id.includes('axios') || id.includes('yup') || id.includes('sweetalert2') || id.includes('@casl')) {
                        return 'vendor-app';
                    }
                },
            },
        },
    },
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler' // or "modern"
            }
        }
    }
});
