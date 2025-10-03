import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({
            input: "resources/js/app.js",
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
                // JS utama langsung hash
                entryFileNames: () => `assets/[hash].js`,
                // Chunk JS hasil split
                chunkFileNames: () => `assets/[hash].js`,
                // Asset (CSS, images, fonts)
                assetFileNames: () => `assets/[hash][extname]`,
            },
        },
    },
});
