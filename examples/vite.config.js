import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('./rendered', import.meta.url));

export default defineConfig({
    root,
    base: './',
    plugins: [tailwindcss()],
    build: {
        outDir: '../dist',
        emptyOutDir: true,
        rollupOptions: {
            input: {
                bootstrap: `${root}/bootstrap.html`,
                tailwind: `${root}/tailwind.html`,
                index: `${root}/index.html`,
            },
        },
    },
});
