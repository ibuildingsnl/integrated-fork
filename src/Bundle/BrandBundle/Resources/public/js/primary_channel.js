const primaryChannel = document.querySelector('.primary-channel');

if (primaryChannel) {
    const inputElements = document.querySelectorAll('.brands input.brand-channel-choice[data-can-be-primary="yes"]');
    const showHideMakePrimary = function (input) {
        input.makePrimary.style.display = input.checked && input.value !== primaryChannel.value ? 'inline-block' : 'none';
    };
    const updatePrimarySelectors = ()  => inputElements.forEach(function (input) {
        showHideMakePrimary(input);
    });
    inputElements.forEach(function(input) {
        const makePrimary = document.createElement('a');
        makePrimary.href = '#';
        makePrimary.text = primaryChannel.dataset.makePrimaryText;
        makePrimary.className = 'make-primary';
        input.closest('li').appendChild(makePrimary);
        input.makePrimary = makePrimary;
        makePrimary.addEventListener('click', function (ev) {
            primaryChannel.value = input.value;
            updatePrimarySelectors();
            ev.preventDefault();
        });
        input.addEventListener('change', () => showHideMakePrimary(input));
    });
    updatePrimarySelectors();
    const firstChoice = document.querySelector('.brands input.brand-channel-choice[data-can-be-primary="yes"]:not(:disabled)');
    if (firstChoice) {
        primaryChannel.value = firstChoice.value;
        updatePrimarySelectors();
    }
}
