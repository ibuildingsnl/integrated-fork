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

document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape' || ev.keyCode === 27) {
        document.querySelectorAll('.publication-settings-aside.show').forEach(div => {
            div.classList.remove('show');
        });
        document.querySelectorAll('.editor-overlay.show').forEach(div => {
            div.classList.remove('show');
        });
    }
});

document.addEventListener('click', function (ev) {
    if (!ev.target.closest('.publication-settings-aside') && !ev.target.closest('.aside-holder') && !ev.target.closest('#toolbar')) {
        document.querySelectorAll('.publication-settings-aside.show').forEach(div => {
            div.classList.remove('show');
        });
        document.querySelectorAll('.editor-overlay.show').forEach(div => {
            div.classList.remove('show');
        });
    }
});
