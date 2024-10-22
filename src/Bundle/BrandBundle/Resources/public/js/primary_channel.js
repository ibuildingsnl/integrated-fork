const primaryChannel = document.querySelector('select.primary-channel');

if (primaryChannel) {
    const inputElements = document.querySelectorAll('.brands input.brand-channel-choice[data-can-be-primary="yes"]');
    const showHideMakePrimary = function(input) {
        input.makePrimary.style.display = input.checked && input.value !== primaryChannel.value ? 'flex' : 'none';
    };
    const updatePrimarySelectors = () => inputElements.forEach(function(input) {
        showHideMakePrimary(input);
    });
    const clearPrimaryClass = () => {
        document.querySelectorAll('.brand-channels li.primary-channel').
            forEach(li => {
                li.classList.remove('primary-channel');
            });
    };

    inputElements.forEach(function(input) {
        const makePrimary = document.createElement('a');
        makePrimary.href = '#';
        makePrimary.title = 'Make primary';
        makePrimary.innerHTML = '<i class="iconoir-medal-1st"></i>';
        makePrimary.className = 'make-primary';
        input.closest('li').appendChild(makePrimary);
        input.makePrimary = makePrimary;
        makePrimary.addEventListener('click', function(ev) {
            primaryChannel.value = input.value;
            clearPrimaryClass(); // Clear any previous primary-channel class
            input.closest('li').classList.add('primary-channel'); // Add the class to the current primary channel's li
            updatePrimarySelectors();
            ev.preventDefault();
        });
        input.addEventListener('change', () => showHideMakePrimary(input));
    });
    updatePrimarySelectors();
    if(primaryChannel.value) {
        const primaryInput = document.querySelector(
            'input.brand-channel-choice[data-can-be-primary="yes"][value="'+primaryChannel.value+'"]'
        );
        if (primaryInput) {
            primaryInput.closest('li').classList.add('primary-channel');
        }
    } else {
        for (const input of document.querySelectorAll('input.brand-choice:checked')) {
            const firstChecked = input.closest('.brand-container')
                .querySelector('input.brand-channel-choice[data-can-be-primary="yes"]:checked');
            if (firstChecked) {
                primaryChannel.value = firstChecked.value;
                firstChecked.closest('li').classList.add('primary-channel');
                updatePrimarySelectors();
                break;
            }
        }
    }
}
