import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/auth/login.css',
                'resources/js/login.js',
                'resources/css/layouts/header.css',
                'resources/css/layouts/header-mobile.css',
                'resources/css/layouts/footer.css',
                'resources/css/pages/home.css',
                'resources/css/admin/setor.css',
                'resources/css/empreendimentos/form.css',
                'resources/css/setor/consulta-requerimento.css',
                'resources/css/requerimentos/form.css',
                'resources/css/requerimentos/form-etapa-1.css',
                'resources/css/requerimentos/form-etapa-2.css',
                'resources/css/requerimentos/form-etapa-3.css',
                'resources/css/requerimentos/show.css',
                'resources/css/requerimentos/meus-requerimentos.css',
                'resources/css/profile/cards.css',
                'resources/css/profile/forms.css',
                'resources/css/profile/hero.css',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
