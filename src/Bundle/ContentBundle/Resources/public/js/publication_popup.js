const formActions = document.querySelector('.form-actions-extra');

const popup = document.createElement('div');
popup.className = 'category_wrapper close-outside';
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
            popup.appendChild(settings);
            popup.querySelectorAll('[id]').forEach(function (e) {
                e.id += '_popup'; // prevent duplicate ids
            });
            popup.querySelector('label').className = 'hidden';
            const channels = document.createElement('select');
            channels.className = 'select2';
            channels.multiple = true;
            channels.dataset.applyChannels = true;
            popup.appendChild(channels);

            const a = document.createElement('a');
            a.href = '#';
            a.className = 'btn btn-green';
            a.innerText = 'Apply choices';
            a.addEventListener('click', function (ev) {
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
                    const brand = input.closest('.brand-container');
                    if (brand) {
                        brand.querySelector('input.brand-choice').checked = true;
                    }
                });
                popup.classList.remove('show');
                ev.preventDefault();
            });
            popup.appendChild(a);

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
        });
        item.appendChild(a);
    });
}
