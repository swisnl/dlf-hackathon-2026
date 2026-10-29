import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import Layout from './Layouts/Layout.vue'

// Only Inertia pages have a page object; login and registration are still plain Blade pages.
if (document.querySelector('script[data-page]')) {
    createInertiaApp({
        title: title => (title ? `${title} · Eigenwijs` : 'Eigenwijs'),
        resolve: name => {
            const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
            return pages[`./Pages/${name}.vue`]
        },
        layout: () => Layout,
        progress: { color: '#9333EA' },
        setup({ el, App, props, plugin }) {
            createApp({ render: () => h(App, props) })
                .use(plugin)
                .mount(el)
        },
    })
}
