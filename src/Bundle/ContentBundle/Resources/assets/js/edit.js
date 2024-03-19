import './main';
import './collection';
import './handlebars.helpers';
import './relation';
import './used_by';
import './unlock_article';
import './taxonomy_category';

window.onload = function() {
    prepDateTimeFields();
};

function prepDateTimeFields() {
    const dateSelections = document.querySelectorAll('.tailwind-datetime');

    dateSelections.forEach(function(dateSelection) {
        let dateText = '';
        let okButton = '';
        let setToNowButton = '';
        let clearButton = '';

        // Create or find the date text display element
        if (!dateSelection.parentNode.querySelector('.date-text')) {
            dateText = document.createElement('div');
            dateText.className = 'date-text';
            dateText.style.display = 'block';
            // Set default text based on parent attribute or fallback
            dateText.textContent = dateSelection.getAttribute('data-set-date-text') || 'Set Date/Time';
            dateSelection.parentNode.insertBefore(dateText, dateSelection);
        } else {
            dateText = dateSelection.parentNode.querySelector('.date-text');
        }

        // Adjust styles as needed
        dateSelection.style.paddingTop = '.5rem';

        // Create or find the "OK" button
        okButton = createOrFindButton(dateSelection, 'ok-date', 'OK');

        // Create or find the "Set to Now" button
        setToNowButton = createOrFindButton(dateSelection, 'set-now-date', 'Set to Now');

        // Create or find the "Clear" button
        clearButton = createOrFindButton(dateSelection, 'clear-date', 'Clear');

        // Event handlers
        dateText.onclick = () => toggleDateSelection(true, dateSelection, dateText);
        okButton.onclick = () => toggleDateSelection(false, dateSelection, dateText);
        clearButton.onclick = () => clearDateTimeFields(dateSelection);
        setToNowButton.onclick = () => setDateTimeToNow(dateSelection);

        // Initially hide the date selection UI
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

    // Extract the date and time values
    const dateValue = dateInput ? dateInput.value : ''; // YYYY-MM-DD
    const timeValue = timeInput ? timeInput.value : ''; // HH:MM

    // Format display text
    let displayText = '';
    if (dateValue && timeValue) {
        // Format for display: DD-MM-YYYY HH:MM
        const [year, month, day] = dateValue.split('-');
        displayText = `${day}-${month}-${year} ${timeValue}`;
    } else if (dateValue) {
        // If only date is set
        const [year, month, day] = dateValue.split('-');
        displayText = `${day}-${month}-${year}`;
    } else if (timeValue) {
        // If only time is set
        displayText = timeValue;
    } else {
        // Default text if neither is set
        displayText = dateSelection.getAttribute('data-set-date-text') || 'Set Date/Time';
    }

    // Update the text display element
    dateText.textContent = displayText;
}

window.addEventListener('openPublishSettingsEvent', function(e) {
    prepDateTimeFields();
});

document.addEventListener('DOMContentLoaded', function () {
    const textareas = document.querySelectorAll('textarea[data-maxchars]');

    textareas.forEach(textarea => {
        const counterSpan = document.createElement('span');
        counterSpan.style.fontWeight = 'bold';
        textarea.parentNode.insertBefore(counterSpan, textarea.nextSibling);

        const updateCounter = () => {
            const maxChars = parseInt(textarea.getAttribute('data-maxchars'), 10);
            let currentLength = textarea.value.length;

            if (currentLength > maxChars) {
                textarea.value = textarea.value.substr(0, maxChars);
                currentLength = maxChars; // Correct the length if trimmed
            }

            counterSpan.textContent = `${currentLength} of ${maxChars} characters used`;
        };

        updateCounter();
        textarea.addEventListener('input', updateCounter);
    });
});
