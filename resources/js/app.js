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
