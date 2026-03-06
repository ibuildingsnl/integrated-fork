(() => {
const formActions = document.querySelector('.form-actions-extra');
const popup = document.querySelector('.publishActions');
const publishActions = Array.from(document.querySelectorAll('.global-publication-settings'))
.map((e) => e.dataset.channelType)
.filter((v, i, a) => a.indexOf(v) === i);

if (
    formActions &&
    popup &&
    publishActions.length > 0 &&
    document.body.classList.contains('integrated_content_content_edit')
) {
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
        const item = document.createElement('li');
        menu.appendChild(item);
        const a = document.createElement('a');
        a.className = 'popup-link';
        a.dataset.channelType = action;
        a.innerText = 'Publish to ' + action;
        a.addEventListener('click', function () {

            const channelTypeContainer = popup.querySelector(`.global-publication-settings[data-channel-type="${action}"]`).parentNode;
            const channelTypeForm = channelTypeContainer.querySelector('.global-publication-settings');
            const channels = channelTypeContainer.querySelector('.integrated_channel_choice select.select2');

            channelTypeContainer.classList.add('show');

            apply = channelTypeContainer.querySelector('a.apply');

            apply.addEventListener('click', function (ev) {
                ev.preventDefault();
                // Apply choices
                const pubInputSelector = 'input,select,textarea';
                const data = {};
                channelTypeForm.querySelectorAll(pubInputSelector).forEach(function (input, i) {
                    data[i] = input.value;
                });

                const sourceImageContainer = channelTypeForm.querySelector('.mediagallery_selector .selected_images');

                if (sourceImageContainer) {
                    const imageInfos = Array.from(sourceImageContainer.children).map(li => ({
                        src: li.querySelector('img').src,
                        id: li.id
                    }));
                }
                document.querySelectorAll('.publication-settings[data-channel-type="' + action + '"]').forEach(function (container) {
                    if (!popup.querySelector('[data-apply-channels] option:checked[value="'+container.dataset.publicationChannel+'"]')) {
                        return;
                    }
                    container.querySelectorAll(pubInputSelector).forEach(function (input, i) {
                        input.value = data[i];
                    });

                    if (sourceImageContainer) {
                        const targetImageContainer = container.querySelector('.mediagallery_selector .selected_images');
                        const targetHiddenInput = container.querySelector('.mediagallery_selector .mediagallery_selector_input');

                        targetImageContainer.innerHTML = '';

                        targetHiddenInput.value = '';

                        imageInfos.forEach(info => {
                            const li = document.createElement('li');
                            li.id = info.id;
                            li.className = 'media-item';
                            li.innerHTML = `<div class="media-preview"><div class="thumbnail relative"><div class="centered"><img src="${info.src}"></div><a class="remove_link remove"><i class="iconoir-xmark"></i></a></div></div>`;
                            targetImageContainer.appendChild(li);

                            if (targetHiddenInput.value) {
                                targetHiddenInput.value += ',' + info.id;
                            } else {
                                targetHiddenInput.value = info.id;
                            }
                        });
                    }
                    //Check the selected Channel under Brands
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
                channelTypeContainer.classList.remove('show');

                while (channels.options.length > 0) {
                    channels.remove(0);
                }

                var applyPublishSettingsEvent = new CustomEvent('applyPublishSettingsEvent');

                window.dispatchEvent(applyPublishSettingsEvent);
            });

            //Add cancel Button
            const cancel = channelTypeContainer.querySelector('a.cancel');

            cancel.addEventListener('click', function (ev) {
                ev.preventDefault();
                popup.classList.remove('show');
                channelTypeContainer.classList.remove('show');

                while (channels.options.length > 0) {
                    channels.remove(0);
                }
            });

            triggerSelect2();

            document.querySelectorAll('input[data-channel-type="'+action+'"]').forEach(function (input) {
                    let channel = input.getAttribute('data-channel-selector');
                    let pubStatus = document.querySelector('[data-publication-channel="'+channel+'"]').getAttribute('data-publication-status');

                    if (pubStatus === 'success') return;

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

        });
        item.appendChild(a);

    });
    // close on outside click
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.publishable') &&
            !e.target.closest('.popup-link') &&
            !e.target.closest('.select2-selection__choice') &&
            !e.target.closest('.select2-results__option')) {
            popup.classList.remove('show');

            const channelTypeContainer = document.querySelector('.publication-settings-global .publishable.show');
            if (channelTypeContainer) {
                const channels = channelTypeContainer.querySelector('.integrated_channel_choice select');

                while (channels.options.length > 0) {
                    channels.remove(0);
                }

                channelTypeContainer.classList.remove('show');
            }
        }
    });
}
})();

function triggerSelect2() {
    $('select.select2').each(function() {
        if ($(this).hasClass('select2-hidden-accessible')) {
            // Select2 has been initialized
        } else {
            $(this).select2();
        }
    });
}
