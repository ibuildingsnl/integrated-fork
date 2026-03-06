function initSeoSettingsToggle() {
    const seoButton = document.querySelector('.seo-button');
    const seoSettings = document.querySelector(
        '.publication-settings-aside.seo-settings');

    if (!seoButton || !seoSettings) {
        return;
    }

    if (seoButton.dataset.boundSeoToggle === 'true') {
        return;
    }

    seoButton.addEventListener('click', (e) => {
        e.preventDefault();
        seoSettings.classList.toggle('show');
        document.querySelectorAll('.editor-overlay').forEach(div => {
            div.classList.toggle('show');
        });
    });

    seoButton.dataset.boundSeoToggle = 'true';
}

document.addEventListener('DOMContentLoaded', initSeoSettingsToggle);
document.addEventListener('turbo:load', initSeoSettingsToggle);
document.addEventListener('turbo:render', initSeoSettingsToggle);
