(function () {
    function createOrFindButton(parent, className, text) {
        var button = parent.querySelector('.' + className);
        if (!button) {
            button = document.createElement('span');
            button.className = className;
            button.textContent = text;
            parent.appendChild(button);
        }

        return button;
    }

    function updateDateText(dateSelection, dateText) {
        var dateInput = dateSelection.querySelector('input[type="date"]');
        var timeInput = dateSelection.querySelector('input[type="time"]');

        var dateValue = dateInput ? dateInput.value : '';
        var timeValue = timeInput ? timeInput.value : '';

        var displayText = '';
        if (dateValue && timeValue) {
            var dateParts = dateValue.split('-');
            displayText = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0] + ' ' + timeValue;
        } else if (dateValue) {
            var dateOnlyParts = dateValue.split('-');
            displayText = dateOnlyParts[2] + '-' + dateOnlyParts[1] + '-' + dateOnlyParts[0];
        } else if (timeValue) {
            displayText = timeValue;
        } else {
            displayText = dateSelection.getAttribute('data-set-date-text') || 'Set Date/Time';
        }

        dateText.textContent = displayText;
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

    function clearDateTimeFields(dateSelection, dateText) {
        var dateInput = dateSelection.querySelector('input[type="date"]');
        var timeInput = dateSelection.querySelector('input[type="time"]');

        if (dateInput) {
            dateInput.value = '';
        }
        if (timeInput) {
            timeInput.value = '';
        }

        updateDateText(dateSelection, dateText);
    }

    function setDateTimeToNow(dateSelection, dateText) {
        var now = new Date();
        var dateInput = dateSelection.querySelector('input[type="date"]');
        var timeInput = dateSelection.querySelector('input[type="time"]');
        var year = String(now.getFullYear());
        var month = String(now.getMonth() + 1).padStart(2, '0');
        var day = String(now.getDate()).padStart(2, '0');
        var hours = String(now.getHours()).padStart(2, '0');
        var minutes = String(now.getMinutes()).padStart(2, '0');

        if (dateInput) {
            dateInput.value = year + '-' + month + '-' + day;
        }
        if (timeInput) {
            timeInput.value = hours + ':' + minutes;
        }

        updateDateText(dateSelection, dateText);
    }

    function initializeDateSelection(dateSelection) {
        var parent = dateSelection.parentNode;
        if (!parent) {
            return;
        }

        var dateText = parent.querySelector('.date-text');
        if (!dateText) {
            dateText = document.createElement('div');
            dateText.className = 'date-text';
            dateText.style.display = 'block';
            dateText.textContent = dateSelection.getAttribute('data-set-date-text') || 'Set Date/Time';
            parent.insertBefore(dateText, dateSelection);
        }

        var okButton = createOrFindButton(dateSelection, 'ok-date', 'OK');
        var setNowButton = createOrFindButton(dateSelection, 'set-now-date', 'Set to Now');
        var clearButton = createOrFindButton(dateSelection, 'clear-date', 'Clear');

        if (dateSelection.dataset.bulkPublishWindowBound !== '1') {
            dateText.addEventListener('click', function () {
                toggleDateSelection(true, dateSelection, dateText);
            });

            okButton.addEventListener('click', function () {
                toggleDateSelection(false, dateSelection, dateText);
            });

            setNowButton.addEventListener('click', function () {
                setDateTimeToNow(dateSelection, dateText);
            });

            clearButton.addEventListener('click', function () {
                clearDateTimeFields(dateSelection, dateText);
            });

            dateSelection.querySelectorAll('input[type="date"], input[type="time"]').forEach(function (input) {
                input.addEventListener('change', function () {
                    updateDateText(dateSelection, dateText);
                });
            });

            dateSelection.dataset.bulkPublishWindowBound = '1';
        }

        toggleDateSelection(false, dateSelection, dateText);
    }

    function initBulkPublishWindow() {
        document.querySelectorAll(
            '#integrated_content_bulk_configure_actions .tailwind-datetime, ' +
            '#integrated_content_bulk_confirm_actions .tailwind-datetime, ' +
            '#integrated_content_bulk_comfirm_actions .tailwind-datetime'
        ).forEach(initializeDateSelection);
    }

    document.addEventListener('DOMContentLoaded', initBulkPublishWindow);
    document.addEventListener('turbo:load', initBulkPublishWindow);
})();
