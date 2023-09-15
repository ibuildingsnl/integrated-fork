function showHidePublicationSettingsButton(input) {
    input.openSettings.style.display = input.checked ? 'inline-block' : 'none';
}

function openPublishingSettings(channelId, input) {
    document.querySelectorAll('.publication-settings-aside').forEach(div => {
        div.classList.remove('show');
    });
    document.querySelectorAll('.editor-overlay').forEach(div => {
        div.classList.add('show');
    });
    const settings = document.querySelector('.publication-settings[data-publication-channel="'+channelId+'"]').closest('.publication-settings-aside');
    if (!settings) {
        return;
    }
    settings.classList.add('show') ;
    settings.querySelectorAll('[name*="[startDate]"]').forEach(function (d) {
        d.value = d.value || document.querySelector('[name="integrated_content[publishTime][startDate]"]')?.value;
    });
    settings.querySelectorAll('[name*="[endDate]"]').forEach(function (d) {
        d.value = d.value || document.querySelector('[name="integrated_content[publishTime][endDate]"]')?.value;
    });
    settings.addEventListener('click', function (ev) {
        if (!settings.querySelector('.publication-settings-aside.show').contains(ev.target)) {
            settings.classList.remove('show') ;
        }
        document.querySelectorAll('.editor-overlay').forEach(div => {
            div.classList.remove('show');
        });
    });
    setChannelChoices(settings.querySelector('.settings-apply-to-multiple'));
}

document.querySelectorAll('[data-channel-selector]').forEach(function (input) {
    const settings = document.querySelector(
        '.publication-settings[data-publication-channel="'+input.dataset.channelSelector+'"]'
    ).closest('.publication-settings-element');
    if (!settings) {
        return;
    }

    settings.channelType = input.dataset.channelType;
    if (settings.channelType) {
        const opt = document.createElement('option');
        opt.value = 'type';
        opt.text = 'all ' + settings.channelType + ' channels';
        settings.querySelector('[data-apply-to]')?.append(opt);
    }

    const openSettings = document.createElement('a');
    openSettings.href = '#';
    openSettings.text = '⚙';
    openSettings.className = 'publication-settings-button';
    openSettings.addEventListener('click', function (ev) {
        openPublishingSettings(settings);
        ev.preventDefault();
    });

    input.closest('.checkbox').insertAdjacentElement('afterend', openSettings);
    input.openSettings = openSettings;
    input.addEventListener('change', () => showHidePublicationSettingsButton(input));
    showHidePublicationSettingsButton(input);
});

document.querySelectorAll('.publication-settings-popup').forEach(function (settings) {
    settings.querySelectorAll('[data-apply-to]').forEach(
        (s) => s.addEventListener('change', function (ev) {
            settings.querySelectorAll('.settings-apply-to-multiple').forEach(
                (e) => e.style.display = s.value === 'multiple' ? 'block' : 'none'
            );
        }) || s.dispatchEvent(new Event('change'))
    );
    settings.querySelectorAll('a.btn').forEach(function(a) {
        a.addEventListener('click', function (ev) {
            a.closest('.publication-settings-aside').classList.remove('show');
            document.querySelectorAll('.editor-overlay').forEach(div => {
                div.classList.remove('show');
            });
            ev.preventDefault();
        });
    });
});

function setChannelChoices(element)
{
    // @todo
}
