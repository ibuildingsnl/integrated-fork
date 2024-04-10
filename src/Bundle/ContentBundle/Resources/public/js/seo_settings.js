document.addEventListener('DOMContentLoaded', (event) => {
    const seoButton = document.querySelector('.seo-button');
    const seoSettings = document.querySelector(
        '.publication-settings-aside.seo-settings');

    if (seoButton && seoSettings) {
        seoButton.addEventListener('click', (e) => {
            e.preventDefault();
            seoSettings.classList.toggle('show');
            document.querySelectorAll('.editor-overlay').forEach(div => {
                div.classList.toggle('show');
            });
        });
    }
});
