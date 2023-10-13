const channelTypeElement = document.querySelector('[name$="[channel][type]"]');
function showHideExclusiveElements()
{
    const type = channelTypeElement?.value;
    document.querySelectorAll('[data-exclusive-to]').forEach(function (e) {
        e.style.display = e.dataset.exclusiveTo === type ? 'inherit' : 'none';
    })
}
showHideExclusiveElements();
channelTypeElement?.addEventListener('change', showHideExclusiveElements);
