document.addEventListener("DOMContentLoaded", function() {
    const brandsDiv = document.querySelector('.brands .aside-item-list');
    const htmlContent = `
        <div class="aside-item-search">
            <i class="iconoir-search"></i>
            <input type="text" class="brand-search" placeholder="`+ pubSettings.searchIn + ' ' + pubSettings.brands +`">
        </div>`;

    if (brandsDiv) {
        brandsDiv.insertAdjacentHTML('afterbegin', htmlContent);
    }

    const listSearchElements = document.querySelectorAll('.brand-search');
    listSearchElements.forEach(el => {
        el.addEventListener('change', asideBrandSearch);
        el.addEventListener('keyup', asideBrandSearch);
    });
});

function removeDiacritics(str) {
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

function asideBrandSearch(el) {
    let input, filter, brands, div, a, i, txtValue;
    input = el.target;
    filter = removeDiacritics(input.value).toUpperCase();
    brands = el.target.parentNode.parentNode.querySelector('.aside-item-list-container .brands');
    brandChoices = brands.querySelectorAll('.brand_channel_choice');

    for (const div of brandChoices) {
        const label = div.getElementsByTagName('label')[0];
        const txtValue = label.textContent || label.innerText;
        div.style.display = removeDiacritics(txtValue).toUpperCase().includes(filter) ?
            '' :
            'none';
    }
}

function toggleChannelList(input, toggle) {
    const brandContainer = input.closest('.brand-container');

    if (toggle === false) {
        if (!input.checked) {
            brandContainer.classList.remove('show');
        } else {
            brandContainer.classList.add('show');
        }
    } else {
        brandContainer.classList.toggle('show');
    }
}

document.querySelectorAll('.brands input.brand-choice').forEach((brandCheckbox) => {
    brandCheckbox.addEventListener('change', () => {
        brandCheckbox
        .closest('.brand-container')
        .querySelectorAll('.publication-channel-toggle-button')
        .forEach((brandChannelsToggle) => {
            brandChannelsToggle.style.display = brandCheckbox.checked ? 'flex' : 'none';
        });
    });

    if (brandCheckbox.checked) {
        brandCheckbox
        .closest('.brand-container')
        .querySelectorAll('.publication-channel-toggle-button')
        .forEach((brandChannelsToggle) => {
            brandChannelsToggle.style.display = 'flex';
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.brand-channel-choice');

    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            if (!checkbox.checked) {
                const label = checkbox.closest('.checkbox-container');
                if (label) {
                    label.style.backgroundColor = '';
                    label.style.color = '';
                }
                const checkmark = label.querySelector('.checkmark');
                if (checkmark) {
                    checkmark.style.backgroundColor = '';
                    checkmark.style.borderColor = '';
                }
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.brands input.brand-choice').forEach(function(input) {
        input.addEventListener('change', function() {
            if (input.updating) return;
            input.updating = true;

            const channelsContainer = input.closest('.brand-container').querySelector('.channels');
            const defaultChannelCheckboxes = channelsContainer.querySelectorAll('.brand-channel-choice[data-channel-default="1"]');
            const allChannelCheckboxes = channelsContainer.querySelectorAll('.brand-channel-choice');

            let skipUnchecking = input.checked && defaultChannelCheckboxes.length === 0;

            if (input.checked) {
                defaultChannelCheckboxes.forEach(channel => {
                    channel.checked = true;
                    channel.dispatchEvent(new Event('change', { 'bubbles': true, 'cancelable': true }));
                });
            } else {
                allChannelCheckboxes.forEach(channel => {
                    channel.checked = false;
                    channel.dispatchEvent(new Event('change', { 'bubbles': true, 'cancelable': true }));
                });
            }

            toggleChannelList(input, input.checked);

            setTimeout(() => input.updating = false, 0);

            if (skipUnchecking) {
                input.checked = true;
            }
        });

        const showChannels = document.createElement('a');
        showChannels.href = '#';
        showChannels.title = 'Toggle Channels';
        showChannels.innerHTML = '<i class="iconoir-nav-arrow-down"></i>';
        showChannels.className = 'publication-channel-toggle-button';
        showChannels.addEventListener('click', function(ev) {
            toggleChannelList(input, true);
            ev.preventDefault();
        });

        input.closest('.checkbox').insertAdjacentElement('afterend', showChannels);
        input.showChannels = showChannels;
    });

    document.querySelectorAll('.brand-container').forEach(container => {
        container.addEventListener('change', function(event) {
            if (event.target.classList.contains('brand-channel-choice') && !event.target.updating) {
                const anyChildChecked = [...container.querySelectorAll('.brand-channel-choice')].some(checkbox => checkbox.checked);
                const parentCheckbox = container.querySelector('.brand-choice');

                if (!anyChildChecked && parentCheckbox && parentCheckbox.updating !== true) {
                    parentCheckbox.checked = false;
                    parentCheckbox.dispatchEvent(new Event('change', { 'bubbles': true, 'cancelable': true }));
                }
            }
        });
    });
});
