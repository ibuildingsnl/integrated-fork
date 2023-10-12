function showHideExclusiveElements()
{
    const type = document.querySelector('#channel_type')?.value;
    document.querySelectorAll('[data-exclusive-to]').forEach(function (e) {
        // alert(e.dataset.exclusiveTo + ' ?== ' + type);
        e.style.display = e.dataset.exclusiveTo === type ? 'inherit' : 'none';
    })
}
showHideExclusiveElements();
