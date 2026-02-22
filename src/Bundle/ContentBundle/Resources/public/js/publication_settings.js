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

    settings.querySelectorAll('.date-text').forEach(function(d) {
        d.style.display = 'flex';
    })

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

    var openPublishSettingsEvent = new CustomEvent('openPublishSettingsEvent');

    window.dispatchEvent(openPublishSettingsEvent);
}

function initPublicationChannelSettingsButtons() {
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
        }

        let openSettings = input.closest('.checkbox')?.parentNode?.querySelector('.publication-settings-button');
        if (!openSettings) {
            openSettings = document.createElement('a');
            openSettings.href = '#';
            openSettings.title = 'Publication settings';
            openSettings.innerHTML = '<i class="iconoir-settings"></i>';
            openSettings.className = 'publication-settings-button';
            input.closest('.checkbox').insertAdjacentElement('afterend', openSettings);
        }

        if (openSettings.dataset.boundPublicationSettingsButton !== 'true') {
            openSettings.addEventListener('click', function (ev) {
                openPublishingSettings(input.dataset.channelSelector, input);
                ev.preventDefault();
                ev.stopPropagation();
            });
            openSettings.dataset.boundPublicationSettingsButton = 'true';
        }

        input.openSettings = openSettings;
        if (input.dataset.boundPublicationSettingsChange !== 'true') {
            input.addEventListener('change', () => showHidePublicationSettingsButton(input));
            input.dataset.boundPublicationSettingsChange = 'true';
        }
        showHidePublicationSettingsButton(input);
    });
}

// Save & exit publication settings
function initPublicationSettingsPopupButtons() {
    document.querySelectorAll('.publication-settings-popup').forEach(function (settings) {
        settings.querySelectorAll('a.btn').forEach(function(a) {
            if (a.dataset.boundPublicationSettingsSave === 'true') {
                return;
            }

            a.addEventListener('click', function (ev) {
                let channelId = settings.querySelector('.publication-settings').getAttribute('data-publication-channel');
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
                    document.querySelectorAll('.publication-settings[data-channel-type="' + settings.querySelector('[data-channel-type]')?.dataset.channelType + '"]').forEach(function (container) {
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
                    document.querySelectorAll('.publication-settings[data-channel-type="' + settings.querySelector('[data-channel-type]')?.dataset.channelType + '"]'
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
                var event = new CustomEvent("ensurePublicationEvent", { detail: { channelId: channelId } });
                document.dispatchEvent(event);

                ev.preventDefault();
            });

            a.dataset.boundPublicationSettingsSave = 'true';
        });
    });
}

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

function setupTextareaCopyFeature() {
    const editorID = 'integrated_content_content'; // Adjust accordingly
    const settingsDivs = document.querySelectorAll('.publication-settings, .publication-settings-global .publishable');

    settingsDivs.forEach(div => {
        const textarea = div.querySelector('textarea');

        if (textarea) {
            const copyIntroButton = createOrFindButton(textarea, 'copy-intro-btn', 'Copy Intro');

            if (copyIntroButton.dataset.boundCopyIntro !== 'true') {
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
                copyIntroButton.dataset.boundCopyIntro = 'true';
            }
        }
    });
}

function createOrFindButton(input, className, text) {
    let button = input.parentNode.querySelector('.' + className);
    if (!button) {
        button = document.createElement('span');
        button.className = className;
        button.textContent = text;
        input.insertAdjacentElement('afterend', button);
    }
    return button;
}

function initPublicationDelete() {
    const icons = document.querySelectorAll('.publication-item .icon.iconoir-xmark');

    icons.forEach(function(icon) {
        if (icon.dataset.boundPublicationDelete === 'true') {
            return;
        }
        icon.addEventListener('click', function() {
            const channelSelector = icon.closest('.publication-item').getAttribute('data-channel');

            const checkbox = document.querySelector(`input[type="checkbox"][data-channel-selector="${channelSelector}"]`);

            if (checkbox) {
                checkbox.checked = false;
                var event = new Event('change', { 'bubbles': true, 'cancelable': true });
                checkbox.dispatchEvent(event);
            }

            const publicationItem = icon.closest('.publication-item');
            if (publicationItem) {
                publicationItem.remove();
            }
        });
        icon.dataset.boundPublicationDelete = 'true';
    });
}

function initPublicationItems() {
    const publicationItems = document.querySelectorAll('.aside-item-wrapper.publications .publication-item');

    publicationItems.forEach(item => {
        if (item.dataset.listenerAdded !== "true") {
            item.addEventListener('click', function() {
                const channel = item.getAttribute('data-channel');
                if (channel) {
                    openPublishingSettings(channel);
                }
            });

            item.dataset.listenerAdded = "true";
        }
    });
}

function initNewPublicationCheckboxes() {
    const newPublicationCheckboxes = document.querySelectorAll('.new-publication-check input[type="checkbox"]');
    let savedData = {};
    let savedImages = {};

    newPublicationCheckboxes.forEach((checkbox, index) => {
        if (checkbox.dataset.boundNewPublicationCheckbox === 'true') {
            return;
        }
        checkbox.addEventListener('change', function () {
            const publicationSettingsAside = checkbox.closest('.publication-settings-aside');
            const publicationSettings = publicationSettingsAside.querySelector('.publication-settings');
            const fields = publicationSettingsAside.querySelectorAll('.aside-item-list .form-control:not([data-apply-to])');
            const imageContainer = publicationSettingsAside.querySelector('.mediagallery_selector .selected_images');
            const hiddenImageInput = publicationSettingsAside.querySelector('.mediagallery_selector .mediagallery_selector_input');
            const channelId = publicationSettings.getAttribute('data-publication-channel');
            const publicationListContainer = document.querySelector('.aside-item-wrapper.publications .aside-item-list-container .publication-list');
            let publication = publicationListContainer.querySelector(`a[data-channel="${channelId}"]`);

            savedData[index] = savedData[index] || {};
            savedImages[index] = savedImages[index] || [];

            if (this.checked) {
                publicationSettingsAside.classList.remove('no-edit');
                publicationSettings.setAttribute('data-publication-status', '');
                // Save current values and clear fields
                fields.forEach(field => {
                    const key = field.name || field.id;
                    savedData[index][key] = field.value;
                    field.value = '';
                });

                if (imageContainer) {
                    savedImages[index] = Array.from(imageContainer.querySelectorAll('li')).map(li => ({
                        id: li.id,
                        src: li.querySelector('img').src
                    }));

                    imageContainer.innerHTML = '';
                    hiddenImageInput.value = '';
                }
            } else {
                publicationSettingsAside.classList.add('no-edit');
                publicationSettings.setAttribute('data-publication-status', 'success');
                if (publication) {
                    publication.remove();
                }

                fields.forEach(field => {
                    const key = field.name || field.id;
                    if (savedData[index].hasOwnProperty(key)) {
                        field.value = savedData[index][key];
                    }
                });

                savedData[index] = {};

                if (imageContainer) {
                    imageContainer.innerHTML = '';
                    hiddenImageInput.value = '';
                }

                if (imageContainer && savedImages[index].length > 0) {

                    savedImages[index].forEach(info => {
                        const li = document.createElement('li');
                        li.id = info.id;
                        li.className = 'media-item';
                        li.innerHTML = `<div class="media-preview"><div class="thumbnail relative"><div class="centered"><img src="${info.src}"></div><a class="remove_link remove"><i class="iconoir-xmark"></i></a></div></div>`;
                        imageContainer.appendChild(li);
                    });

                    hiddenImageInput.value = savedImages[index].map(info => info.id).join(',');
                    savedImages[index] = [];
                }
            }

            var applyPublishSettingsEvent = new CustomEvent('applyPublishSettingsEvent');

            window.dispatchEvent(applyPublishSettingsEvent);

        });
        checkbox.dataset.boundNewPublicationCheckbox = 'true';
    });
}

function initPublicationTextareaEnterStop() {
    document.querySelectorAll('textarea.form-control:not(.title_tinymce_input)').forEach(function(textarea) {
        if (textarea.dataset.boundStopEnterKey === 'true') {
            return;
        }
        textarea.addEventListener('keypress', function(e) {
            var key = e.keyCode || e.which;

            // If the user has pressed enter
            if (key === 13) {
                e.stopPropagation();
            }
        });
        textarea.dataset.boundStopEnterKey = 'true';
    });
}

window.addEventListener('updatePublicationItems', function(e) {
    initPublicationItems();
});

window.addEventListener('updatePublicationItems', function(e) {
    initPublicationDelete();
});

function initPublicationSettingsPage() {
    initPublicationChannelSettingsButtons();
    initPublicationSettingsPopupButtons();
    setupTextareaCopyFeature();
    initPublicationItems();
    initPublicationDelete();
    initNewPublicationCheckboxes();
    initPublicationTextareaEnterStop();
}

document.addEventListener('DOMContentLoaded', initPublicationSettingsPage);
document.addEventListener('turbo:load', initPublicationSettingsPage);
document.addEventListener('turbo:render', initPublicationSettingsPage);
