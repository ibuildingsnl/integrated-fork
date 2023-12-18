const formActions = document.querySelector('.form-actions-extra');

const popup = document.createElement('div');
popup.className = 'publishActions ';
popup.innerText = 'Hello World';
document.body.appendChild(popup);

const publishActions = Array.from(document.querySelectorAll('.publication-settings'))
    .map((e) => e.dataset.channelType)
    .filter((v, i, a) => a.indexOf(v) === i);

if (publishActions.length > 0) {
    // add top-bar menu item
    const publish = document.createElement('div');
    publish.className = 'publish';
    publish.innerHTML = '<ul class="options-no-bg">\n' +
        '    <li class="dropdown close-outside" data-underlay="no">\n' +
        '        <a data-toggle="dropdown" role="button" class="btn btn-dropdown">\n' +
        '            <span>Publish</span>\n' +
        '            <i class="iconoir-nav-arrow-down"></i>\n' +
        '        </a>\n' +
        '        <ul aria-labelledby="drop_2" role="menu" class="dropdown-menu alt" id="menu_publish">\n' +
        '        </ul>\n' +
        '    </li>\n' +
        '</ul>';
    formActions.prepend(publish);

    // add publishing options to newly added dropdown menu
    const menu = document.querySelector('#menu_publish');
    publishActions.forEach(function (action) {
        if (action === 'Newsletter' || action === 'Website') {
            // @todo make into a setting on channel type instead of hardcoded in js
            return;
        }
        const item = document.createElement('li');
        menu.appendChild(item);
        const a = document.createElement('a');
        a.className = 'popup-link';
        a.innerText = 'Publish to ' + action;
        a.addEventListener('click', function () {
            // open popup
            const settings = document.querySelector('.publication-settings[data-channel-type="'+action+'"]').cloneNode(true);
            popup.innerHTML = '';
            const settingsContainer = document.createElement('div');
            settingsContainer.classList = 'close-outside publishable display';
            settingsContainer.appendChild(settings);
            popup.appendChild(settingsContainer);
            popup.querySelectorAll('[id]').forEach(function (e) {
                e.id += '_popup'; // prevent duplicate ids
            });
            popup.querySelector('label').className = 'hidden';
            const channelContainer = document.createElement('div');
            channelContainer.classList = 'form-item w-full flex flex-wrap channels integrated_channel_choice';

            // Create label element
            const label = document.createElement('label');
            label.className = 'control-label w-full md:w-3/12 xl:w-2/12 3xl:w-1/12';
            label.textContent = 'Kanalen';

            // Create div element
            const widgetDiv = document.createElement('div');
            widgetDiv.className = 'w-full md:w-9/12 xl:w-10/12 3xl:w-11/12 widget';

            // Create select element
            const channels = document.createElement('select');
            channels.className = 'select2';
            channels.multiple = true;
            channels.dataset.applyChannels = true;

            const publishHeader = document.createElement('h3')
            publishHeader.innerText = 'Publish to ' + action

            // Append elements
            channelContainer.appendChild(label);
            channelContainer.appendChild(widgetDiv);
            widgetDiv.appendChild(channels);
            settingsContainer.prepend(channelContainer);
            settingsContainer.prepend(publishHeader);

            const buttonContainer = document.createElement('div');
            buttonContainer.className = 'flex justify-between mt-8';

            //Add Apply button
            const apply = document.createElement('a');
            apply.href = '#';
            apply.className = 'apply btn btn-green';
            apply.innerText = 'Apply';
            apply.addEventListener('click', function (ev) {
                // Apply choices
                const pubInputSelector = 'input,select,textarea';
                const data = {};
                settings.querySelectorAll(pubInputSelector).forEach(function (input, i) {
                    data[i] = input.value;
                });
                document.querySelectorAll(
                    '.publication-settings[data-channel-type="' + action + '"]'
                ).forEach(function (container) {
                    if (!popup.querySelector('[data-apply-channels] option:checked[value="'+container.dataset.publicationChannel+'"]')) {
                        return;
                    }
                    container.querySelectorAll(pubInputSelector).forEach(function (input, i) {
                        input.value = data[i];
                    });

                    const input = document.querySelector('input[data-channel-selector="'+container.dataset.publicationChannel+'"]');
                    input.checked = true;
                    var event = new Event('change', { 'bubbles': true, 'cancelable': true });
                    input.dispatchEvent(event);

                    const brand = input.closest('.brand-container');
                    if (brand) {
                        brand.querySelector('input.brand-choice').checked = true;

                        brand.querySelectorAll('.publication-channel-toggle-button')
                        .forEach((brandChannelsToggle) => {
                            brandChannelsToggle.style.display = input.checked ? 'flex' : 'none';
                        });
                    }

                });
                popup.classList.remove('show');
                ev.preventDefault();
            });

            //Add cancel Button
            const cancel = document.createElement('a');
            cancel.href = '#';
            cancel.className = 'cancel btn btn-white';
            cancel.innerText = 'Cancel';
            cancel.addEventListener('click', function (ev) {
                ev.preventDefault();
                popup.classList.remove('show');
            });

            buttonContainer.appendChild(apply);
            buttonContainer.appendChild(cancel);
            settingsContainer.appendChild(buttonContainer);

            triggerSelect2();

            document.querySelectorAll('input[data-channel-type="'+action+'"]').forEach(function (input) {
                const brand = input.closest('.brand-container');
                let selected = input.checked;
                if (brand && selected) {
                    selected = brand.querySelector('input.brand-choice').checked;
                }
                const option = document.createElement('option');
                option.value = input.value;
                option.text = input.dataset.channelName;
                option.selected = selected;
                channels.append(option);
            });
            popup.classList.add('show');

            var openPublishSettingsEvent = new CustomEvent('openPublishSettingsEvent', settings);

            window.dispatchEvent(openPublishSettingsEvent);

        });
        item.appendChild(a);

    });
    // close on outside click
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.publishable') && !e.target.closest('.popup-link') && !e.target.closest('.select2-selection__choice')) {
            popup.classList.remove('show');
        }
    });
}

function triggerSelect2() {
    $('select.select2').each(function() {
        if ($(this).hasClass('select2-hidden-accessible')) {
            // Select2 has been initialized
        } else {
            $(this).select2();
        }
    });
}
