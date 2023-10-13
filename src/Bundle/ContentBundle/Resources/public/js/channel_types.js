function showHideExclusiveElements()
{
    const type = document.querySelector('#channel_type')?.value;
    document.querySelectorAll('[data-exclusive-to]').forEach(function (e) {
        e.style.display = e.dataset.exclusiveTo === type ? 'inherit' : 'none';
    })
}
showHideExclusiveElements();
document.querySelector('#channel_type')?.addEventListener('change', showHideExclusiveElements);
