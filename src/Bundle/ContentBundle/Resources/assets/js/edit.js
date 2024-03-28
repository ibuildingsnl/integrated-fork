import './main';
import './collection';
import './handlebars.helpers';
import './relation';
import './used_by';
import './unlock_article';
import './taxonomy_category';

function initializePage() {
    prepDateTimeFields();
    setupCharacterCounters();
}

function prepDateTimeFields() {
    const dateSelections = document.querySelectorAll('.tailwind-datetime');

    dateSelections.forEach(function(dateSelection) {
        let dateText = '';
        let okButton = '';
        let setToNowButton = '';
        let clearButton = '';

        if (!dateSelection.parentNode.querySelector('.date-text')) {
            dateText = document.createElement('div');
            dateText.className = 'date-text';
            dateText.style.display = 'block';
            dateText.textContent = dateSelection.getAttribute('data-set-date-text') || 'Set Date/Time';
            dateSelection.parentNode.insertBefore(dateText, dateSelection);
        } else {
            dateText = dateSelection.parentNode.querySelector('.date-text');
        }

        dateSelection.style.paddingTop = '.5rem';

        okButton = createOrFindButton(dateSelection, 'ok-date', 'OK');
        setToNowButton = createOrFindButton(dateSelection, 'set-now-date', 'Set to Now');
        clearButton = createOrFindButton(dateSelection, 'clear-date', 'Clear');

        dateText.onclick = () => toggleDateSelection(true, dateSelection, dateText);
        okButton.onclick = () => toggleDateSelection(false, dateSelection, dateText);
        clearButton.onclick = () => clearDateTimeFields(dateSelection);
        setToNowButton.onclick = () => setDateTimeToNow(dateSelection);

        dateSelection.style.display = 'none';

        updateDateText(dateSelection, dateText);
    });

}

function createOrFindButton(parent, className, text) {
    let button = parent.querySelector('.' + className);
    if (!button) {
        button = document.createElement('span');
        button.className = className;
        button.textContent = text;
        parent.appendChild(button);
    }
    return button;
}

function setDateTimeToNow(dateSelection) {
    const now = new Date();
    const dateInput = dateSelection.querySelector('input[type="date"]');
    const timeInput = dateSelection.querySelector('input[type="time"]');

    if (dateInput && timeInput) {
        dateInput.value = now.toISOString().split('T')[0]; // YYYY-MM-DD format
        timeInput.value = now.toTimeString().split(' ')[0].substring(0, 5); // HH:MM format
    }
}


function clearDateTimeFields(dateSelection) {
    const dateInput = dateSelection.querySelector('input[type="date"]');
    const timeInput = dateSelection.querySelector('input[type="time"]');
    if (dateInput) dateInput.value = '';
    if (timeInput) timeInput.value = '';
}

function toggleDateSelection(showSelection, dateSelection, dateText) {
    if (showSelection) {
        dateSelection.style.display = 'flex';
        dateText.style.display = 'none';
        dateText.style.color = '';
        let warning = dateText.parentNode.querySelector('.date-warning-message');
        if (warning) {
            warning.remove();
        }
    } else {
        dateSelection.style.display = 'none';
        dateText.style.display = 'flex';
        updateDateText(dateSelection, dateText);
    }
}

$('form[name="integrated_content"]').on('keyup keypress', function(e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) {
        e.preventDefault();
        return false;
    }
});

function updateDateText(dateSelection, dateText) {
    const dateInput = dateSelection.querySelector('input[type="date"]');
    const timeInput = dateSelection.querySelector('input[type="time"]');

    const dateValue = dateInput ? dateInput.value : ''; // YYYY-MM-DD
    const timeValue = timeInput ? timeInput.value : ''; // HH:MM

    let displayText = '';
    if (dateValue && timeValue) {
        const [year, month, day] = dateValue.split('-');
        displayText = `${day}-${month}-${year} ${timeValue}`;
    } else if (dateValue) {
        const [year, month, day] = dateValue.split('-');
        displayText = `${day}-${month}-${year}`;
    } else if (timeValue) {
        displayText = timeValue;
    } else {
        displayText = dateSelection.getAttribute('data-set-date-text') ||
            'Set Date/Time';
    }

    dateText.textContent = displayText;
}

function setupCharacterCounters() {
    const textareas = document.querySelectorAll('textarea[data-maxchars]');

    textareas.forEach(textarea => {
        let counterSpan = textarea.parentNode.querySelector('.char-counter');

        if (!counterSpan) {
            counterSpan = document.createElement('span');
            counterSpan.className = 'char-counter';
            counterSpan.style.fontWeight = 'bold';
            textarea.parentNode.insertBefore(counterSpan, textarea.nextSibling);
        }

        const updateCounter = () => {
            const maxChars = parseInt(textarea.getAttribute('data-maxchars'), 10);
            let currentLength = textarea.value.length;

            if (currentLength > maxChars) {
                textarea.value = textarea.value.substr(0, maxChars);
                currentLength = maxChars;
            }

            counterSpan.textContent = `${currentLength} of ${maxChars} characters used`;
        };

        updateCounter(); // Initial update
        textarea.addEventListener('input', updateCounter);
    });
}

document.addEventListener('DOMContentLoaded', initializePage);

document.addEventListener('click', function(e) {
    if (!e.target.classList.contains('ok-date')) return;

    let parent = e.target.closest('.publication-settings');
    if (!parent || !parent.getAttribute('data-publication-channel')) return;

    let publicationChannel = parent.getAttribute('data-publication-channel');
    let checkbox = document.querySelector(`#integrated_content_brands [data-channel-selector="${publicationChannel}"]`);
    if (!checkbox) return;

    let brandChannel = checkbox.closest('.brand-channels');
    let checkedChannels = Array.from(brandChannel.querySelectorAll('.brand-channels [type="checkbox"]:checked')).map(el => el.getAttribute('data-channel-selector'));

    let dateInput, timeInput;
    let fallbackDateInput = document.querySelector('#integrated_content_publishTime input[type="date"]');
    let fallbackTimeInput = document.querySelector('#integrated_content_publishTime input[type="time"]');

    let websiteChannel = checkedChannels.find(channel => document.querySelector(`[data-channel-selector="${channel}"][data-channel-type="Website"]`));

    if (websiteChannel) {
        let websiteElement = document.querySelector(`[data-publication-channel="${websiteChannel}"][data-channel-type="Website"]`);

        dateInput = websiteElement.querySelector('input[type="date"]').value || fallbackDateInput.value;
        timeInput = websiteElement.querySelector('input[type="time"]').value || fallbackTimeInput.value;
    } else {
        dateInput = fallbackDateInput.value;
        timeInput = fallbackTimeInput.value;
    }

    if (!(dateInput && timeInput)) return;
    let websiteDateTime = new Date(`${dateInput}T${timeInput}`);

    let publicationSettingsContainer = document.querySelector('#integrated_content_publications');

    checkedChannels.forEach(channel => {
        let channelElement = publicationSettingsContainer.querySelector(`[data-publication-channel="${channel}"]:not([data-channel-type="Website"])`);
        if (!channelElement) return;

        let channelDateInput = channelElement.querySelector('input[type="date"]');
        let channelTimeInput = channelElement.querySelector('input[type="time"]');
        let channelCheckboxParent = document.querySelector(`#integrated_content_brands [data-channel-selector="${channel}"]`).closest('.checkbox-container');

        if (!(channelDateInput && channelTimeInput)) return;
        let channelDateTime = new Date(`${channelDateInput.value}T${channelTimeInput.value}`);

        let dateTextElement = channelElement.querySelector('.date-text');
        if (!dateTextElement) return;

        dateTextElement.style.color = channelDateTime >= websiteDateTime ? '' : 'red';
        updateChannelStyles(channelCheckboxParent, channelDateTime >= websiteDateTime, channelElement, dateTextElement);
    });
});

function updateChannelStyles(channelCheckboxParent, isDateValid, channelElement, dateTextElement) {
    let checkmark = channelCheckboxParent.querySelector('.checkmark');
    if (isDateValid) {
        channelCheckboxParent.style.backgroundColor = '';
        channelCheckboxParent.style.color = '';
        checkmark.style.backgroundColor = '';
        checkmark.style.borderColor = '';
    } else {
        channelCheckboxParent.style.backgroundColor = '#ffc9cd';
        channelCheckboxParent.style.color = 'red';
        checkmark.style.backgroundColor = 'red';
        checkmark.style.borderColor = 'red';

        let existingMessage = channelElement.querySelector('.date-warning-message');
        if (existingMessage) return;

        let messageSpan = document.createElement('p');
        messageSpan.classList.add('date-warning-message', 'publication-info');
        messageSpan.textContent = "This date/time is earlier than the website's publication date/time.";
        dateTextElement.insertAdjacentElement('afterend', messageSpan);
    }
}

window.addEventListener('applyPublishSettingsEvent', function(e) {
    prepDateTimeFields();
});

window.addEventListener('openPublishSettingsEvent', function(e) {
    prepDateTimeFields();
});
