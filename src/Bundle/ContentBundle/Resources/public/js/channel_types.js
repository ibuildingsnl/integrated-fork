function getChannelTypeElement() {
    const candidates = Array.from(document.querySelectorAll('[id$="_channel_type"]'));

    if (!candidates.length) {
        return null;
    }

    const visible = candidates.find(function (element) {
        return element.offsetParent !== null;
    });

    return visible || candidates[0];
}

function showHideExclusiveElements() {
    const channelTypeElement = getChannelTypeElement();

    if (!channelTypeElement) {
        return;
    }

    const type = channelTypeElement.value;
    const form = channelTypeElement.closest('form') || document;

    form.querySelectorAll('[data-exclusive-to]').forEach(function (element) {
        element.style.display = element.dataset.exclusiveTo === type ? '' : 'none';
    });
}

function bindChannelTypeListener() {
    const channelTypeElement = getChannelTypeElement();

    if (!channelTypeElement || channelTypeElement.dataset.channelTypeExclusiveBound === '1') {
        return;
    }

    channelTypeElement.addEventListener('change', showHideExclusiveElements);
    channelTypeElement.dataset.channelTypeExclusiveBound = '1';
}

function initializeChannelTypeExclusives() {
    bindChannelTypeListener();
    showHideExclusiveElements();
}

document.addEventListener('DOMContentLoaded', initializeChannelTypeExclusives);
document.addEventListener('turbo:load', initializeChannelTypeExclusives);
initializeChannelTypeExclusives();
