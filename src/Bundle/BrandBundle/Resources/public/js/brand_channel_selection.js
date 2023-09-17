document.querySelectorAll('.brands input.brand-choice').forEach(
    (brandCheckbox) => brandCheckbox.addEventListener('change', () => brandCheckbox
        .closest('.brand-container')
        .querySelectorAll('.publication-channel-toggle-button')
        .forEach((brandChannelsToggle) => brandChannelsToggle.style.display = brandCheckbox.checked ? 'flex' : 'none')
    ) || brandCheckbox.dispatchEvent(new Event('change'))
);

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

});

