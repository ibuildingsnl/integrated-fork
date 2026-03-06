function initPrimaryChannelSelection(event) {
    const root = event && event.target ? event.target : document;
    const primaryChannel = root.querySelector('select.primary-channel') || document.querySelector('select.primary-channel');

    if (!primaryChannel) {
        return;
    }

    const inputElements = document.querySelectorAll('.brands input.brand-channel-choice[data-can-be-primary="yes"]');
    const showHideMakePrimary = function(input) {
        if (!input.makePrimary) {
            return;
        }
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
        const channelItem = input.closest('li');
        if (!channelItem) {
            return;
        }

        let makePrimary = channelItem.querySelector('.make-primary');
        if (!makePrimary) {
            makePrimary = document.createElement('a');
            makePrimary.href = '#';
            makePrimary.title = 'Make primary';
            makePrimary.innerHTML = '<i class="iconoir-medal-1st"></i>';
            makePrimary.className = 'make-primary';
            channelItem.appendChild(makePrimary);
        }

        input.makePrimary = makePrimary;

        if (makePrimary.dataset.boundMakePrimary !== 'true') {
            makePrimary.addEventListener('click', function(ev) {
                primaryChannel.value = input.value;
                clearPrimaryClass();
                channelItem.classList.add('primary-channel');
                updatePrimarySelectors();
                ev.preventDefault();
            });
            makePrimary.dataset.boundMakePrimary = 'true';
        }

        if (input.dataset.boundMakePrimaryChange !== 'true') {
            input.addEventListener('change', () => showHideMakePrimary(input));
            input.dataset.boundMakePrimaryChange = 'true';
        }
    });

    updatePrimarySelectors();
    clearPrimaryClass();

    if (primaryChannel.value) {
        const primaryInput = document.querySelector(
            'input.brand-channel-choice[data-can-be-primary="yes"][value="'+primaryChannel.value+'"]'
        );
        if (primaryInput && primaryInput.closest('li')) {
            primaryInput.closest('li').classList.add('primary-channel');
        }
    } else {
        for (const input of document.querySelectorAll('input.brand-choice:checked')) {
            const brandContainer = input.closest('.brand-container');
            if (!brandContainer) {
                continue;
            }
            const firstChecked = brandContainer.querySelector('input.brand-channel-choice[data-can-be-primary="yes"]:checked');
            if (firstChecked) {
                primaryChannel.value = firstChecked.value;
                if (firstChecked.closest('li')) {
                    firstChecked.closest('li').classList.add('primary-channel');
                }
                updatePrimarySelectors();
                break;
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', initPrimaryChannelSelection);
document.addEventListener('turbo:load', initPrimaryChannelSelection);
document.addEventListener('turbo:render', initPrimaryChannelSelection);
document.addEventListener('turbo:frame-load', initPrimaryChannelSelection);
document.addEventListener('turbo:frame-render', initPrimaryChannelSelection);
