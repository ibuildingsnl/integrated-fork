const channelTypeElement = document.querySelector('[id$="channel_type"]');
function showHideExclusiveElements()
{
    const type = channelTypeElement?.value;
    document.querySelectorAll('[data-exclusive-to]').forEach(function (e) {
        e.style.display = e.dataset.exclusiveTo === type ? 'inherit' : 'none';
    })
}
showHideExclusiveElements();
channelTypeElement?.addEventListener('change', showHideExclusiveElements);
