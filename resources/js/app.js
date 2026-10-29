import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

// Only Inertia pages have a page object; the Blade pages of the student flow don't.
if (document.querySelector('script[data-page]')) {
    createInertiaApp({
        resolve: name => {
            const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
            return pages[`./Pages/${name}.vue`]
        },
        setup({ el, App, props, plugin }) {
            createApp({ render: () => h(App, props) })
                .use(plugin)
                .mount(el)
        },
    })
}

// AI calls can take a minute: show that we're working and prevent double submits.
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-ai')) {
        return;
    }

    form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.disabled = true;
        button.textContent = form.dataset.ai || 'De AI denkt na…';
    });
    form.setAttribute('aria-busy', 'true');
});
