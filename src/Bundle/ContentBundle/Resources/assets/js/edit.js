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

        if (!dateSelection.parentNode.querySelector('.date-text')) {
            dateText = document.createElement('div');
            dateText.className = 'date-text';
            dateText.style.display = 'block';
            if (dateSelection.closest('.datetime')?.getAttribute('data-set-date-text')) {
                dateText.textContent = dateSelection.closest('.datetime').getAttribute('data-set-date-text');
            } else {
                dateText.textContent = dateSelection.closest('.aside-item-wrapper').getAttribute('data-set-date-text');
            }
            dateSelection.parentNode.insertBefore(dateText, dateSelection);
        } else {
            dateText = dateSelection.parentNode.querySelector('.date-text');
            dateText.style.display = 'block';
        }

        dateSelection.style.paddingTop = '.5rem';

        if (!dateSelection.querySelector('.ok-date')) {
            okButton = document.createElement('span');
            okButton.className = 'ok-date';
            okButton.textContent = 'OK';

            dateSelection.appendChild(okButton);
        } else {
            okButton = dateSelection.querySelector('.ok-date');
        }


        if (!dateSelection.querySelector('.set-now-date')) {
            setToNowButton = document.createElement('span');
            setToNowButton.className = 'set-now-date';
            setToNowButton.textContent = 'Set to Now';

            dateSelection.appendChild(setToNowButton);
        } else {
            setToNowButton = dateSelection.querySelector('.set-now-date');
        }

        if (!dateSelection.querySelector('.clear-date')) {
            clearButton = document.createElement('span');
            clearButton.className = 'clear-date';
            clearButton.textContent = 'Reset';

            dateSelection.appendChild(clearButton);
        } else {
            clearButton = dateSelection.querySelector('.clear-date');
        }

        dateText.onclick = function() {
            toggleDateSelection(true, dateSelection, dateText);
        };

        okButton.onclick = function() {
            toggleDateSelection(false, dateSelection, dateText);
        };

        clearButton.onclick = function() {
            clearDateTimeFields(dateSelection);
            updateDateText(dateSelection, dateText);
        };

        setToNowButton.onclick = function() {
            const now = new Date();
            const dayElement = dateSelection.querySelector(
                '[id$="_date_day"]')
                ? dateSelection.querySelector('[id$="_date_day"]')
                : dateSelection.querySelector('[id$="_date_day_popup"]');
            const monthElement = dateSelection.querySelector(
                '[id$="_date_month"]')
                ? dateSelection.querySelector('[id$="_date_month"]')
                : dateSelection.querySelector('[id$="_date_month_popup"]');
            const yearElement = dateSelection.querySelector(
                '[id$="_date_year"]')
                ? dateSelection.querySelector('[id$="_date_year"]')
                : dateSelection.querySelector('[id$="_date_year_popup"]');
            const hourElement = dateSelection.querySelector(
                '[id$="_time_hour"]')
                ? dateSelection.querySelector('[id$="_time_hour"]')
                : dateSelection.querySelector('[id$="_time_hour_popup"]');
            const minuteElement = dateSelection.querySelector(
                '[id$="_time_minute"]')
                ? dateSelection.querySelector('[id$="_time_minute"]')
                : dateSelection.querySelector('[id$="_time_minute_popup"]');

            if (dayElement && monthElement && yearElement && hourElement &&
                minuteElement) {
                dayElement.value = now.getDate().toString();
                monthElement.value = (now.getMonth() + 1).toString(); // Months are 0-indexed
                yearElement.value = now.getFullYear().toString();
                hourElement.value = now.getHours().toString();
                minuteElement.value = now.getMinutes().toString();
            }

            updateDateText(dateSelection, dateText);
        };

        dateSelection.style.display = 'none';

        updateDateText(dateSelection, dateText);
    });
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
    const dayElement = dateSelection.querySelector('[id$="_date_day"]')
        ? dateSelection.querySelector('[id$="_date_day"]')
        : dateSelection.querySelector('[id$="_date_day_popup"]');
    const monthElement = dateSelection.querySelector('[id$="_date_month"]')
        ? dateSelection.querySelector('[id$="_date_month"]')
        : dateSelection.querySelector('[id$="_date_month_popup"]');
    const yearElement = dateSelection.querySelector('[id$="_date_year"]')
        ? dateSelection.querySelector('[id$="_date_year"]')
        : dateSelection.querySelector('[id$="_date_year_popup"]');
    const hourElement = dateSelection.querySelector('[id$="_time_hour"]')
        ? dateSelection.querySelector('[id$="_time_hour"]')
        : dateSelection.querySelector('[id$="_time_hour_popup"]');
    const minuteElement = dateSelection.querySelector('[id$="_time_minute"]')
        ? dateSelection.querySelector('[id$="_time_minute"]')
        : dateSelection.querySelector('[id$="_time_minute_popup"]');

    const day = dayElement ? dayElement.value : '';
    const month = monthElement ? monthElement.value : '';
    const year = yearElement ? yearElement.value : '';

    let hour = '';

    if (hourElement) {
        hour = hourElement.value;
        if (hour.length === 1) {
            hour = '0' + hour;
        }
    }

    let minute = '';

    if (minuteElement) {
        minute = minuteElement.value;
        if (minute.length === 1) {
            minute = '0' + minute;
        }
    }

    const existingClearButton = dateText.nextSibling;
    if (existingClearButton && existingClearButton.className === 'clear-date') {
        existingClearButton.remove();
    }

    if (day && month && year) {
        dateText.textContent = `${day}-${month}-${year} ${hour}:${minute}`;
    } else {
        if (dateSelection.closest('.datetime')?.getAttribute('data-set-date-text')) {
            dateText.textContent = dateSelection.closest('.datetime').getAttribute('data-set-date-text');
        } else {
            dateText.textContent = dateSelection.closest('.aside-item-wrapper').getAttribute('data-set-date-text');
        }
    }
}

function clearDateTimeFields(dateSelection) {
    const dayElement = dateSelection.querySelector('[id$="_date_day"]')
        ? dateSelection.querySelector('[id$="_date_day"]')
        : dateSelection.querySelector('[id$="_date_day_popup"]');
    const monthElement = dateSelection.querySelector('[id$="_date_month"]')
        ? dateSelection.querySelector('[id$="_date_month"]')
        : dateSelection.querySelector('[id$="_date_month_popup"]');
    const yearElement = dateSelection.querySelector('[id$="_date_year"]')
        ? dateSelection.querySelector('[id$="_date_year"]')
        : dateSelection.querySelector('[id$="_date_year_popup"]');
    const hourElement = dateSelection.querySelector('[id$="_time_hour"]')
        ? dateSelection.querySelector('[id$="_time_hour"]')
        : dateSelection.querySelector('[id$="_time_hour_popup"]');
    const minuteElement = dateSelection.querySelector('[id$="_time_minute"]')
        ? dateSelection.querySelector('[id$="_time_minute"]')
        : dateSelection.querySelector('[id$="_time_minute_popup"]');

    if (dayElement) dayElement.value = '';
    if (monthElement) monthElement.value = '';
    if (yearElement) yearElement.value = '';
    if (hourElement) hourElement.value = '';
    if (minuteElement) minuteElement.value = '';
}

window.addEventListener('openPublishSettingsEvent', function(e) {
    prepDateTimeFields();
});
