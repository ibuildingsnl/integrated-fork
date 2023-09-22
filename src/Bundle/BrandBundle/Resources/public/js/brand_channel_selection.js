document.addEventListener("DOMContentLoaded", function() {
    const brandsDiv = document.querySelector('.brands .aside-item-list');
    const htmlContent = `
        <div class="aside-item-search">
            <i class="iconoir-search"></i>
            <input type="text" class="brand-search" placeholder="Search in Brands">
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

function openChannelList(input) {
    const brandContainer = input.closest('.brand-container');

    brandContainer.querySelectorAll('.brand-channels')
    .forEach((brandChannels) => {
        brandChannels.style.display = brandChannels.style.display === 'block' ? 'none' : 'block';
    });

    brandContainer.classList.toggle('show');
}

document.querySelectorAll('.brands input.brand-choice').forEach(function (input) {

    const showChannels = document.createElement('a');
    showChannels.href = '#';
    showChannels.title = 'Toggle Channels';
    showChannels.innerHTML = '<i class="iconoir-nav-arrow-down"></i>';
    showChannels.className = 'publication-channel-toggle-button';
    showChannels.addEventListener('click', function (ev) {
        openChannelList(input);
        ev.preventDefault();
    });
    input.closest('.checkbox').insertAdjacentElement('afterend', showChannels);
    input.showChannels = showChannels;

    input.addEventListener('change', function() {
        openChannelList(input);
    })

});

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
        brandCheckbox.dispatchEvent(new Event('change'));
    }
});
