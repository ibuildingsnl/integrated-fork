function showHidePublicationSettingsButton(input) {
    input.openSettings.style.display = input.checked ? 'flex' : 'none';
}

function openPublishingSettings(channelId, input) {
    const settings = document.querySelector('.publication-settings[data-publication-channel="'+channelId+'"]').closest('.publication-settings-aside');

    if (settings.classList.contains('show')) {
        settings.classList.remove('show');

        document.querySelectorAll('.editor-overlay').forEach(div => {
            div.classList.remove('show');
        });

        return;
    }

    document.querySelectorAll('.publication-settings-aside').forEach(div => {
        div.classList.remove('show');
    });
    document.querySelectorAll('.editor-overlay').forEach(div => {
        div.classList.add('show');
    });

    if (!settings) {
        return;
    }

    settings.classList.add('show');

    settings.querySelectorAll('[name*="[startDate][date]"], [name*="[startDate][time]"]').forEach(function(d) {
        const isDateInput = d.name.includes('[date]');
        const fieldNamePart = isDateInput ? '[date]' : '[time]';
        const correspondingName = `integrated_content[publishTime][startDate]${fieldNamePart}`;
        const correspondingValue = document.querySelector(`[name="${correspondingName}"]`)?.value;
        d.value = d.value || correspondingValue;
    });

    settings.querySelectorAll('[name*="[endDate][date]"], [name*="[endDate][time]"]').forEach(function(d) {
        const isDateInput = d.name.includes('[date]');
        const fieldNamePart = isDateInput ? '[date]' : '[time]';
        const correspondingName = `integrated_content[publishTime][endDate]${fieldNamePart}`;
        const correspondingValue = document.querySelector(`[name="${correspondingName}"]`)?.value;
        d.value = d.value || correspondingValue;
    });

    var openPublishSettingsEvent = new CustomEvent('openPublishSettingsEvent', settings);

    window.dispatchEvent(openPublishSettingsEvent);
}

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
        allOption.text = pubSettings.applyToAll + ' ' + settings.dataset.channelType + ' ' + pubSettings.channels;
        const someOption = document.createElement('option');
        someOption.value = 'choose';
        someOption.text = pubSettings.applyToSpecific + ' ' + settings.dataset.channelType + ' ' + pubSettings.channels;
        const settingsContainer = settings.closest('.publication-settings-aside');
        const applyToSelect = settingsContainer.querySelector('[data-apply-to]');
        applyToSelect?.append(someOption, allOption);
        applyToSelect?.addEventListener('change', function () {
            showHideChannelSelect(settingsContainer, settings.dataset.channelType);
        });
        showHideChannelSelect(settingsContainer, settings.dataset.channelType);
    }

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

function showHideChannelSelect(container, type) {
    const applyToChannelsSelect = container.querySelector('select[data-apply-channels]');
    const channelSelectContainer = container.querySelector('.settings-channels-choice');
    applyToChannelsSelect.innerHTML = '';
    if (container.querySelector('select[data-apply-to]').value !== 'choose') {
        channelSelectContainer.classList.add('hidden');
        return;
    }
    channelSelectContainer.classList.remove('hidden');
    document.querySelectorAll('input[data-channel-type="'+type+'"]').forEach(function (input) {
        if (!input.checked) {
            return;
        }
        const option = document.createElement('option');
        option.value = input.value;
        option.text = input.dataset.channelName;
        applyToChannelsSelect.append(option);
    });
}

// Save & exit publication settings
document.querySelectorAll('.publication-settings-popup').forEach(function (settings) {
    settings.querySelectorAll('a.btn').forEach(function(a) {
        a.addEventListener('click', function (ev) {
            a.closest('.publication-settings-aside').classList.remove('show');
            document.querySelectorAll('.editor-overlay').forEach(div => {
                div.classList.remove('show');
            });
            const applyType = settings.querySelector('[data-apply-to]')?.value;
            if (applyType === 'type') {
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
            } else if (applyType === 'choose') {
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
                    if (!document.querySelector('[data-apply-channels] option:checked[value="'+container.dataset.publicationChannel+'"]')) {
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

document.addEventListener('click', function (ev) {
    if (document.querySelectorAll('.publication-settings-aside.show')) {
        if (!ev.target.closest('.publication-settings-aside') && !ev.target.closest('.aside-holder') && !ev.target.closest('#toolbar') && !ev.target.closest('.navbar') && !ev.target.closest('.remove_link')) {
            document.querySelectorAll('.publication-settings-aside.show').forEach(div => {
                div.classList.remove('show');
            });
            document.querySelectorAll('.editor-overlay.show').forEach(div => {
                div.classList.remove('show');
            });
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const editorID = 'integrated_content_content'; // Adjust accordingly
    const settingsDivs = document.querySelectorAll('.publication-settings-aside');

    settingsDivs.forEach(div => {
        const textarea = div.querySelector('textarea');

        if (textarea) {
            const copyIntroButton = createOrFindButton(textarea, 'copy-intro-btn', 'Copy Intro');

            copyIntroButton.addEventListener('click', function() {
                if (tinymce.get(editorID)) {
                    const content = tinymce.get(editorID).getContent();
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = content;
                    let textContent = tempDiv.textContent ||
                        tempDiv.innerText || '';

                    const maxChars = parseInt(textarea.getAttribute('data-maxchars'), 10);
                    if (maxChars > 0) {
                        if (textContent.length > maxChars) {
                            textContent = textContent.substr(0, maxChars);
                        }
                    }

                    textarea.value = textContent;
                }
            });
        }
    });
});

function createOrFindButton(parent, className, text) {
    let button = parent.querySelector('.' + className);
    if (!button) {
        button = document.createElement('span');
        button.className = className;
        button.textContent = text;
        parent.insertAdjacentElement('afterend', button);
    }
    return button;
}
