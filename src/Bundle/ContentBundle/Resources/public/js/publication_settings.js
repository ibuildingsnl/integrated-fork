function showHidePublicationSettingsButton(input) {
    input.openSettings.style.display = input.checked ? 'inline-block' : 'none';
}

function openPublishingSettings(channelId) {
    const settings = document.querySelector('.publication-settings[data-publication-channel="'+channelId+'"]').closest('.publication-settings-hidden');
    if (!settings) {
        return;
    }
    settings.className = 'publication-settings-display';
    settings.querySelectorAll('[name*="[startDate]"]').forEach(function (d) {
        d.value = d.value || document.querySelector('[name="integrated_content[publishTime][startDate]"]')?.value;
    });
    settings.querySelectorAll('[name*="[endDate]"]').forEach(function (d) {
        d.value = d.value || document.querySelector('[name="integrated_content[publishTime][endDate]"]')?.value;
    });
    settings.addEventListener('click', function (ev) {
        if (!settings.querySelector('.publication-settings-popup').contains(ev.target)) {
            settings.className = 'publication-settings-hidden';
        }
    });
}

document.querySelectorAll('[data-channel-selector]').forEach(function (input) {
    const openSettings = document.createElement('a');
    openSettings.href = '#';
    openSettings.text = '⚙';
    openSettings.className = 'publication-settings-button';
    openSettings.addEventListener('click', function (ev) {
        openPublishingSettings(input.dataset.channelSelector);
        ev.preventDefault();
    });
    input.closest('.checkbox').insertAdjacentElement('afterend', openSettings);
    input.openSettings = openSettings;
    input.addEventListener('change', () => showHidePublicationSettingsButton(input));
    showHidePublicationSettingsButton(input);
});

document.querySelectorAll('.publication-settings').forEach(function (settings) {
    settings.parentElement.querySelectorAll('a.btn').forEach(function(a) {
        a.addEventListener('click', function (ev) {
            a.closest('.publication-settings-display').className = 'publication-settings-hidden';
            ev.preventDefault();
        });
    });
});
