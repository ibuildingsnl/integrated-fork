function initBrandChannelAddToggle() {
    var chooseChannel = document.querySelector('[data-choose-channel]');
    if (!chooseChannel) {
        return;
    }

    var setDisplayFor = function (suffix, display) {
        document.querySelectorAll('[data-channel-' + suffix + ']').forEach(function (element) {
            element.style.display = display;
        });
    };

    var addOrChooseChannel = function () {
        if (chooseChannel.checked) {
            setDisplayFor('choose', 'block');
            setDisplayFor('new', 'none');
        } else {
            setDisplayFor('choose', 'none');
            setDisplayFor('new', 'block');
        }
    };

    if (chooseChannel.dataset.channelAddToggleBound !== 'true') {
        chooseChannel.addEventListener('change', addOrChooseChannel);
        chooseChannel.dataset.channelAddToggleBound = 'true';
    }

    addOrChooseChannel();
}

document.addEventListener('DOMContentLoaded', initBrandChannelAddToggle);
document.addEventListener('turbo:load', initBrandChannelAddToggle);
