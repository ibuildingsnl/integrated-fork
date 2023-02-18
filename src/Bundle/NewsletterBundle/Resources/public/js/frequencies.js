function updateScheduleEntry(frequency) {
    frequency.closest('.panel-body').querySelectorAll('[data-if-frequency]').forEach(
        (el) => el.closest('.form-item').style.display = el.getAttribute('data-if-frequency').indexOf(frequency.value) < 0 ? 'none' : ''
    );
}

const scheduler = document.querySelector('.frequency-component');

scheduler.addEventListener('change', function (ev) {
    let target = ev.target;
    while (target) {
        if (target.matches('[id$="_frequency"]')) {
            updateScheduleEntry(target);
        }
        target = target.parentElement;
    }
});
scheduler.querySelector('[data-addfield="collection"]').addEventListener('click', function () {
    const matches = scheduler.querySelectorAll('[id$="_frequency"]');
    updateScheduleEntry(matches[matches.length - 1]);
});
scheduler.querySelectorAll('[id$="_frequency"]').forEach(updateScheduleEntry);
