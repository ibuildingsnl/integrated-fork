function removeDiacritics(str) {
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

function asideBrandSearch(el) {
    let input, filter, brands;
    input = el.target;
    filter = removeDiacritics(input.value).toUpperCase();
    brands = el.target.parentNode.parentNode.querySelector('.aside-item-list-container .brands');
    if (!brands) {
        return;
    }
    const brandChoices = brands.querySelectorAll('.brand-container');

    for (const div of brandChoices) {
        const label = div.getElementsByTagName('label')[0];
        if (!label) {
            continue;
        }
        const txtValue = label.textContent || label.innerText;
        div.style.display = removeDiacritics(txtValue).toUpperCase().includes(filter) ?
            '' :
            'none';
    }
}

function toggleChannelList(input, toggle) {
    const brandContainer = input.closest('.brand-container');
    if (!brandContainer) {
        return;
    }

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

function initBrandSearch() {
    const brandsDiv = document.querySelector('.brands .aside-item-list');
    if (typeof pubSettings === 'undefined' || !brandsDiv) {
        return;
    }

    if (!brandsDiv.querySelector('.aside-item-search .brand-search')) {
        const htmlContent = `
        <div class="aside-item-search">
            <i class="iconoir-search"></i>
            <input type="text" class="brand-search" placeholder="`+ pubSettings.searchIn + ' ' + pubSettings.brands +`">
        </div>`;
        brandsDiv.insertAdjacentHTML('afterbegin', htmlContent);
    }

    document.querySelectorAll('.brand-search').forEach((el) => {
        if (el.dataset.boundBrandSearch === 'true') {
            return;
        }

        el.addEventListener('input', asideBrandSearch);
        el.addEventListener('change', asideBrandSearch);
        el.addEventListener('keyup', asideBrandSearch);
        el.dataset.boundBrandSearch = 'true';
    });
}

function initBrandChannelCheckboxes(root = document) {
    root.querySelectorAll('.brand-channel-choice').forEach((checkbox) => {
        if (checkbox.dataset.boundBrandChannelReset === 'true') {
            return;
        }

        checkbox.addEventListener('change', function() {
            const label = checkbox.closest('.checkbox-container');
            if (!checkbox.checked) {
                if (label) {
                    label.style.backgroundColor = '';
                    label.style.color = '';
                }
                const checkmark = label ? label.querySelector('.checkmark') : null;
                if (checkmark) {
                    checkmark.style.backgroundColor = '';
                    checkmark.style.borderColor = '';
                }
            }
        });

        checkbox.dataset.boundBrandChannelReset = 'true';
    });
}

function initBrandChannelParents(root = document) {
    root.querySelectorAll('.brands input.brand-choice').forEach((brandCheckbox) => {
        const brandContainer = brandCheckbox.closest('.brand-container');
        const checkboxWrapper = brandCheckbox.closest('.checkbox');
        if (!brandContainer || !checkboxWrapper) {
            return;
        }

        let showChannels = brandContainer.querySelector('.publication-channel-toggle-button');
        if (!showChannels) {
            showChannels = document.createElement('a');
            showChannels.href = '#';
            showChannels.title = 'Toggle Channels';
            showChannels.innerHTML = '<i class="iconoir-nav-arrow-down"></i>';
            showChannels.className = 'publication-channel-toggle-button';
            checkboxWrapper.insertAdjacentElement('afterend', showChannels);
        }

        if (showChannels.dataset.boundBrandToggleClick !== 'true') {
            showChannels.addEventListener('click', function(ev) {
                toggleChannelList(brandCheckbox, true);
                showChannels.setAttribute('aria-expanded', brandContainer.classList.contains('show') ? 'true' : 'false');
                ev.preventDefault();
            });
            showChannels.dataset.boundBrandToggleClick = 'true';
        }

        brandCheckbox.showChannels = showChannels;
        showChannels.setAttribute('aria-expanded', brandContainer.classList.contains('show') ? 'true' : 'false');
        showChannels.style.display = brandCheckbox.checked ? 'flex' : 'none';

        if (brandCheckbox.dataset.boundBrandToggleVisibility !== 'true') {
            brandCheckbox.addEventListener('change', () => {
                brandCheckbox
                    .closest('.brand-container')
                    .querySelectorAll('.publication-channel-toggle-button')
                    .forEach((brandChannelsToggle) => {
                        brandChannelsToggle.style.display = brandCheckbox.checked ? 'flex' : 'none';
                    });
            });
            brandCheckbox.dataset.boundBrandToggleVisibility = 'true';
        }

        if (brandCheckbox.dataset.boundBrandToggleState !== 'true') {
            brandCheckbox.addEventListener('change', function() {
                if (brandCheckbox.updating) return;
                brandCheckbox.updating = true;

                const channelsContainer = brandContainer.querySelector('.channels');
                if (!channelsContainer) {
                    toggleChannelList(brandCheckbox, brandCheckbox.checked);
                    setTimeout(() => brandCheckbox.updating = false, 0);
                    return;
                }

                const defaultChannelCheckboxes = channelsContainer.querySelectorAll('.brand-channel-choice[data-channel-default="1"]');
                const allChannelCheckboxes = channelsContainer.querySelectorAll('.brand-channel-choice');
                const skipUnchecking = brandCheckbox.checked && defaultChannelCheckboxes.length === 0;

                if (brandCheckbox.checked) {
                    defaultChannelCheckboxes.forEach((channel) => {
                        channel.checked = true;
                        channel.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
                    });
                } else {
                    allChannelCheckboxes.forEach((channel) => {
                        channel.checked = false;
                        channel.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
                    });
                }

                toggleChannelList(brandCheckbox, brandCheckbox.checked);
                showChannels.setAttribute('aria-expanded', brandContainer.classList.contains('show') ? 'true' : 'false');

                setTimeout(() => brandCheckbox.updating = false, 0);

                if (brandCheckbox.checked && skipUnchecking) {
                    brandCheckbox.checked = true;
                }
            });
            brandCheckbox.dataset.boundBrandToggleState = 'true';
        }
    });

    root.querySelectorAll('.brand-container').forEach((container) => {
        if (container.dataset.boundBrandContainerSync !== 'true') {
            container.addEventListener('change', function(event) {
                if (event.target.classList.contains('brand-channel-choice') && !event.target.updating) {
                    const anyChildChecked = [...container.querySelectorAll('.brand-channel-choice')].some((checkbox) => checkbox.checked);
                    const parentCheckbox = container.querySelector('.brand-choice');

                    if (!anyChildChecked && parentCheckbox && parentCheckbox.updating !== true) {
                        parentCheckbox.checked = false;
                        parentCheckbox.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
                    }
                }
            });
            container.dataset.boundBrandContainerSync = 'true';
        }
    });
}

function initBrandChannelSelection(event) {
    const root = event && event.target ? event.target : document;
    initBrandSearch();
    initBrandChannelCheckboxes(root);
    initBrandChannelParents(root);
}

document.addEventListener('DOMContentLoaded', initBrandChannelSelection);
document.addEventListener('turbo:load', initBrandChannelSelection);
document.addEventListener('turbo:render', initBrandChannelSelection);
document.addEventListener('turbo:frame-load', initBrandChannelSelection);
document.addEventListener('turbo:frame-render', initBrandChannelSelection);
