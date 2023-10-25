function showHidePublicationSettingsButton(input) {
    input.openSettings.style.display = input.checked ? 'flex' : 'none';
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
}

// Add publication settings buttons
document.querySelectorAll('[data-channel-selector]').forEach(function (input) {
    const settings = document.querySelector(
        '.publication-settings[data-publication-channel="'+input.dataset.channelSelector+'"]'
    );
    if (!settings) {
        return;
    }

    if (settings.dataset.channelType) {
        const allOption = document.createElement('option');
        allOption.value = 'type';
        allOption.text = 'all ' + settings.dataset.channelType + ' channels';
        const someOption = document.createElement('option');
        someOption.value = 'choose';
        someOption.text = 'specific ' + settings.dataset.channelType + ' channels';
        const settingsContainer = settings.closest('.publication-settings-aside');
        const applyToSelect = settingsContainer.querySelector('[data-apply-to]');
        applyToSelect?.append(someOption, allOption);
        applyToSelect?.addEventListener('click', function () {
            showHideChannelSelect(settingsContainer);
        });
        showHideChannelSelect(settingsContainer);
    }

    // const type = settings.dataset.channelType;
    // document.querySelectorAll('.publication-settings[data-channel-type="'+type+'"]').forEach(function (other) {
    //     const opt = document.createElement('option');
    //     opt.value = other.dataset.publicationChannel;
    //     opt.text = other.dataset.channelName;
    //     opt.selected = (other === settings);
    //     settings.closest('.publication-settings-aside').querySelector('[data-apply-channels]')?.append(opt);
    //     // alert(type + ' / ' + settings.dataset.channelName + ' / ' + other.dataset.channelName);
    // });

    const openSettings = document.createElement('a');
    openSettings.href = '#';
    openSettings.title = 'Publication settings';
    openSettings.innerHTML = '<i class="iconoir-settings"></i>';
    openSettings.className = 'publication-settings-button';
    openSettings.addEventListener('click', function (ev) {
        openPublishingSettings(input.dataset.channelSelector, input);
        ev.preventDefault();
        ev.stopPropagation();
    });

    input.closest('.checkbox').insertAdjacentElement('afterend', openSettings);
    input.openSettings = openSettings;
    input.addEventListener('change', () => showHidePublicationSettingsButton(input));
    showHidePublicationSettingsButton(input);
});

function showHideChannelSelect(container) {
    const channelSelect = container.querySelector('.settings-channels-choice');
    if (container.querySelector('select[data-apply-to]').value === 'choose') {
        channelSelect.classList.remove('hidden');
    } else {
        channelSelect.classList.add('hidden');
    }
}

// Save & exit publication settings
document.querySelectorAll('.publication-settings-popup').forEach(function (settings) {
    settings.querySelectorAll('a.btn').forEach(function(a) {
        a.addEventListener('click', function (ev) {
            a.closest('.publication-settings-aside').classList.remove('show');
            document.querySelectorAll('.editor-overlay').forEach(div => {
                div.classList.remove('show');
            });
            if (settings.querySelector('[data-apply-to]')?.value === 'type') {
                // apply to all of this type
                const pubInputSelector = 'input,select,textarea';
                const data = {};
                settings.querySelectorAll(pubInputSelector).forEach(function (input, i) {
                    data[i] = input.value;
                });
                document.querySelectorAll(
                    '.publication-settings[data-channel-type="' +
                    settings.querySelector('[data-channel-type]')?.dataset.channelType +
                    '"]'
                ).forEach(function (container) {
                    if (settings.contains(container)) {
                        return;
                    }
                    container.querySelectorAll(pubInputSelector).forEach(function (input, i) {
                        input.value = data[i];
                    });
                });
            }
            ev.preventDefault();
        });
    });
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
    if (!ev.target.closest('.publication-settings-aside') && !ev.target.closest('.aside-holder')) {
        document.querySelectorAll('.publication-settings-aside.show').forEach(div => {
            div.classList.remove('show');
        });
        document.querySelectorAll('.editor-overlay.show').forEach(div => {
            div.classList.remove('show');
        });
    }
});

