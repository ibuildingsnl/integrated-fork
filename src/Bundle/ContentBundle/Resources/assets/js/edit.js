import './main';
import './collection';
import './handlebars.helpers';
import './relation';
import './used_by';
import './unlock_article';
import './taxonomy_category';

function initializePage() {
    bindPreventEnterOnContentForm();
    initBrandChannelChoiceHandlers();
    initSubscriptionActionEditors();
    prepDateTimeFields();
    setupCharacterCounters();
    updatePublicationsAndChannels();
    setPublicationDateTimes();

    if (document.body.dataset.boundOkDateUpdate !== 'true') {
        document.addEventListener('click', function(e) {
            if (!e.target.classList.contains('ok-date')) return;
            updatePublicationsAndChannels();
        });
        document.body.dataset.boundOkDateUpdate = 'true';
    }
}

function runInitializePageSafely() {
    try {
        initializePage();
    } catch (error) {
        console.error('Failed to initialize content editor interactions', error);
    }
}

function scheduleInitializePage() {
    runInitializePageSafely();
    window.requestAnimationFrame(runInitializePageSafely);
    window.setTimeout(runInitializePageSafely, 120);
}

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
            dateText.textContent = dateSelection.getAttribute('data-set-date-text') || 'Set Date/Time';
            dateSelection.parentNode.insertBefore(dateText, dateSelection);
        } else {
            dateText = dateSelection.parentNode.querySelector('.date-text');
        }

        // Adjust styles as needed
        dateSelection.style.paddingTop = '.5rem';

        okButton = createOrFindButton(dateSelection, 'ok-date', 'OK');
        setToNowButton = createOrFindButton(dateSelection, 'set-now-date', 'Set to Now');
        clearButton = createOrFindButton(dateSelection, 'clear-date', 'Clear');

        dateText.onclick = () => toggleDateSelection(true, dateSelection, dateText);
        okButton.onclick = () => toggleDateSelection(false, dateSelection, dateText);
        clearButton.onclick = () => clearDateTimeFields(dateSelection);
        setToNowButton.onclick = () => setDateTimeToNow(dateSelection);

        toggleDateSelection(false, dateSelection, dateText);

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

function bindPreventEnterOnContentForm() {
    $('form[name="integrated_content"]').off('keyup.preventEnter keypress.preventEnter').on('keyup.preventEnter keypress.preventEnter', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });
}

function initSubscriptionActionEditors() {
    document.querySelectorAll('.js-subscription-action-editor').forEach(function(root) {
        if (!root || root.dataset.subscriptionActionEditorInitialized === 'true') {
            syncSubscriptionActionEditor(root);
            return;
        }

        var typeSelect = root.querySelector('.js-subscription-action-type');
        if (typeSelect) {
            typeSelect.addEventListener('change', function() {
                syncSubscriptionActionEditor(root);
            });
        }

        root.dataset.subscriptionActionEditorInitialized = 'true';
        syncSubscriptionActionEditor(root);
    });

    if (document.body.dataset.subscriptionActionEditorCollectionBound !== 'true') {
        document.addEventListener('click', function(event) {
            var target = event.target;
            if (!(target instanceof HTMLElement) || target.getAttribute('data-addfield') !== 'collection') {
                return;
            }

            window.setTimeout(function() {
                initSubscriptionActionEditors();
            }, 0);
        });

        document.body.dataset.subscriptionActionEditorCollectionBound = 'true';
    }
}

function syncSubscriptionActionEditor(root) {
    if (!(root instanceof HTMLElement)) {
        return;
    }

    var typeSelect = root.querySelector('.js-subscription-action-type');
    if (!(typeSelect instanceof HTMLSelectElement)) {
        return;
    }

    var currentType = String(typeSelect.value || '').trim();
    if (!currentType) {
        currentType = String(typeSelect.dataset.subscriptionActionDefault || 'form').trim();
        typeSelect.value = currentType;
    }

    resolveSubscriptionActionRows(root, '.js-subscription-action-row--iframe, .js-subscription-action-row--form').forEach(function(row) {
        if (!(row instanceof HTMLElement)) {
            return;
        }

        var showForIframe = row.classList.contains('js-subscription-action-row--iframe');
        var shouldShow = showForIframe ? currentType === 'iframe' : currentType === 'form';
        row.classList.toggle('is-hidden', !shouldShow);
        row.style.display = shouldShow ? '' : 'none';

        row.querySelectorAll('.js-subscription-action-row--iframe, .js-subscription-action-row--form').forEach(function(marker) {
            if (!(marker instanceof HTMLElement) || marker === row) {
                return;
            }

            marker.style.display = shouldShow ? '' : 'none';
        });
    });
}

function resolveSubscriptionActionRows(root, selector) {
    var rows = [];
    var seen = new Set();

    root.querySelectorAll(selector).forEach(function(marker) {
        if (!(marker instanceof HTMLElement)) {
            return;
        }

        var row = marker.closest('.form-item') || marker;
        if (!(row instanceof HTMLElement) || seen.has(row)) {
            return;
        }

        if (marker.classList.contains('js-subscription-action-row--iframe')) {
            row.classList.add('js-subscription-action-row--iframe');
        }

        if (marker.classList.contains('js-subscription-action-row--form')) {
            row.classList.add('js-subscription-action-row--form');
        }

        seen.add(row);
        rows.push(row);
    });

    return rows;
}

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
        if (textarea.dataset.boundCharCounterInput !== 'true') {
            textarea.addEventListener('input', updateCounter);
            textarea.dataset.boundCharCounterInput = 'true';
        }
    });
}

function updatePublicationsAndChannels() {
    // Assuming there could be multiple publication settings containers, loop through each
    document.querySelectorAll('.publication-settings[data-publication-channel]').forEach(pubSettings => {
        let pubStatus = pubSettings.getAttribute('data-publication-status');
        if (pubStatus === 'success') return;

        let publicationSettingsContainer = document.querySelector('#integrated_content_publications');
        let publicationListContainer = document.querySelector('.aside-item-wrapper.publications .aside-item-list-container .publication-list');
        if (!publicationSettingsContainer || !publicationListContainer) return;

        let publicationChannel = pubSettings.getAttribute('data-publication-channel');
        let checkbox = document.querySelector(`#integrated_content_brands [data-channel-selector="${publicationChannel}"]`);

        if (!checkbox) return;

        let brandChannel = checkbox.closest('.brand-channels');
        if (!brandChannel) return;

        let brandContainer = brandChannel.closest('.brand-container');
        if (!brandContainer) return;

        let brandInput = brandContainer.querySelector('input.brand-choice');
        if (!brandInput) return;

        if (!brandInput.checked) return;

        let checkedChannels = Array.from(brandChannel.querySelectorAll('.brand-channels [type="checkbox"]:checked')).map(el => el.getAttribute('data-channel-selector'));

        if (checkedChannels.length === 0) return;

        let dateInput, timeInput;
        let fallbackDateInput = document.querySelector('#integrated_content_publishTime input[type="date"]');
        let fallbackTimeInput = document.querySelector('#integrated_content_publishTime input[type="time"]');
        if (!fallbackDateInput || !fallbackTimeInput) return;

        let pubTimeInput, pubDateInput;
        if (pubSettings) {
            const publicationDateInput = pubSettings.querySelector('input[type="date"]');
            const publicationTimeInput = pubSettings.querySelector('input[type="time"]');
            if (!publicationDateInput || !publicationTimeInput) return;

            pubDateInput = publicationDateInput.value || fallbackDateInput.value;
            pubTimeInput = publicationTimeInput.value || fallbackTimeInput.value;
        }

        updatePublication(pubDateInput, pubTimeInput, publicationChannel, publicationListContainer)

        let websiteChannel = checkedChannels.find(channel => document.querySelector(`[data-channel-selector="${channel}"][data-channel-type="Website"]`));

        if (websiteChannel) {
            let websiteElement = document.querySelector(`[data-publication-channel="${websiteChannel}"][data-channel-type="Website"]`);
            if (!websiteElement) return;

            const websiteDateInput = websiteElement.querySelector('input[type="date"]');
            const websiteTimeInput = websiteElement.querySelector('input[type="time"]');
            if (!websiteDateInput || !websiteTimeInput) return;

            dateInput = websiteDateInput.value || fallbackDateInput.value;
            timeInput = websiteTimeInput.value || fallbackTimeInput.value;
        } else {
            dateInput = fallbackDateInput.value;
            timeInput = fallbackTimeInput.value;
        }

        if (!(dateInput && timeInput)) return;

        let websiteDateTime = new Date(`${dateInput}T${timeInput}`);

        checkedChannels.forEach(channel => {
            let channelElement = publicationSettingsContainer.querySelector(`[data-publication-channel="${channel}"]:not([data-channel-type="Website"])`);
            if (!channelElement) return;

            let pubStatus = channelElement.getAttribute('data-publication-status');
            if (pubStatus === 'success') return;

            const channelDateField = channelElement.querySelector('input[type="date"]');
            const channelTimeField = channelElement.querySelector('input[type="time"]');
            if (!channelDateField || !channelTimeField) return;

            let channelDateInput = channelDateField.value;
            let channelTimeInput = channelTimeField.value;

            const channelCheckbox = document.querySelector(`#integrated_content_brands [data-channel-selector="${channel}"]`);
            if (!channelCheckbox) return;

            let channelCheckboxParent = channelCheckbox.closest('.checkbox-container');

            if (!(channelDateInput && channelTimeInput)) return;
            let channelDateTime = new Date(`${channelDateInput}T${channelTimeInput}`);

            let dateTextElement = channelElement.querySelector('.startDate .date-text');

            if (!dateTextElement) return;

            dateTextElement.style.color = channelDateTime >= websiteDateTime ? '' : 'red';
            updateChannelStyles(channelCheckboxParent, channelDateTime >= websiteDateTime, channelElement, dateTextElement);

            let channelPublicationToUpdate = publicationListContainer.querySelector(`a[data-channel="${channel}"]`);

            if (channelPublicationToUpdate) {
                updatePublicationStyles(channelPublicationToUpdate, channelDateTime >= websiteDateTime);
            }
        });
    });
}

function updatePublication(pubDateInput, pubTimeInput, publicationChannel, publicationListContainer) {
    if (!pubDateInput || !pubTimeInput || !publicationListContainer) return;

    let formattedDate = pubDateInput.split('-').reverse().join('-');
    let selectedDateTime = new Date(`${pubDateInput}T${pubTimeInput}`);
    let now = new Date();

    let publicationToUpdate = publicationListContainer.querySelector(`a[data-channel="${publicationChannel}"]`);

    if (publicationToUpdate) {
        let publishTimeSpan = publicationToUpdate.querySelector('.publish-time');
        if (publishTimeSpan) {
            publishTimeSpan.textContent = `${formattedDate} ${pubTimeInput}`;
        }

        if (selectedDateTime > now) {
            publicationToUpdate.classList.remove('published');
            publicationToUpdate.classList.add('planned');
        } else {
            publicationToUpdate.classList.remove('planned');
            publicationToUpdate.classList.add('published');
        }
    }

    sortPublications(publicationListContainer);
}

function updateChannelStyles(channelCheckboxParent, isDateValid, channelElement, dateTextElement) {
    if (!channelCheckboxParent) return;

    let checkmark = channelCheckboxParent.querySelector('.checkmark');
    if (!checkmark) return;

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
        messageSpan.textContent = "This date/time is not set or is earlier than the website's publication date/time.";
        dateTextElement.insertAdjacentElement('afterend', messageSpan);
    }
}

function updatePublicationStyles(publicationToUpdate, isDateValid) {
    if (isDateValid) {
        if (publicationToUpdate.classList.contains('published') || publicationToUpdate.classList.contains('planned')) {
            publicationToUpdate.classList.remove('date-invalid');
        }
        let existingMessage = publicationToUpdate.querySelector('.publication-info');
        if (existingMessage) {
            existingMessage.remove()
        }
    } else {
        publicationToUpdate.classList.add('date-invalid');
        let publicationTitle = publicationToUpdate.querySelector('.publication-item-content');
        let existingMessage = publicationToUpdate.querySelector('.publication-info');
        if (existingMessage) return;

        let messageSpan = document.createElement('p');
        messageSpan.classList.add('publication-info');
        messageSpan.textContent = "This date/time is not set or is earlier than the website's publication date/time.";
        publicationTitle.insertAdjacentElement('afterend', messageSpan);
    }
}

function initBrandChannelChoiceHandlers() {
    document.querySelectorAll('.brand-channel-choice').forEach(checkbox => {
        if (checkbox.dataset.boundBrandChannelChoice !== 'true') {
        checkbox.addEventListener('change', function() {
            let channelName = checkbox.getAttribute('data-channel-name');
            let channelIcon = checkbox.getAttribute('data-channel-type-icon');
            let channelSelector = checkbox.getAttribute('data-channel-selector');

            let publicationListContainer = document.querySelector('.aside-item-wrapper.publications .aside-item-list-container .publication-list');
            if (!publicationListContainer) return;

            if (!checkbox.checked) {
                let itemToRemove = publicationListContainer.querySelector(`[data-channel="${channelSelector}"]`);
                if (itemToRemove) {
                    publicationListContainer.removeChild(itemToRemove);
                }

                updatePublicationCount();
                updatePublicationsAndChannels();
                sortPublications(publicationListContainer);

                var removePublicationItemEvent = new CustomEvent('updatePublicationItems');
                window.dispatchEvent(removePublicationItemEvent);
                return;
            }

            let channelSettings = document.querySelector(`[data-publication-channel="${channelSelector}"]`);
            if (!channelSettings) return;

            let pubStatus = channelSettings.getAttribute('data-publication-status');
            if (pubStatus === 'success') return;

            let dateInput, timeInput;
            let fallbackDateInput = document.querySelector('#integrated_content_publishTime input[type="date"]');
            let fallbackTimeInput = document.querySelector('#integrated_content_publishTime input[type="time"]');
            if (!fallbackDateInput || !fallbackTimeInput) return;

            const channelDateInput = channelSettings.querySelector('input[type="date"]');
            const channelTimeInput = channelSettings.querySelector('input[type="time"]');
            if (!channelDateInput || !channelTimeInput) return;

            dateInput = channelDateInput.value || fallbackDateInput.value;
            timeInput = channelTimeInput.value || fallbackTimeInput.value;

            let publicationItem = createPublicationItem(channelName, channelIcon, dateInput, timeInput, channelSelector);
            publicationListContainer.appendChild(publicationItem);

            updatePublicationCount();
            updatePublicationsAndChannels();
            sortPublications(publicationListContainer);

            var updatePublicationItems = new CustomEvent('updatePublicationItems');

            window.dispatchEvent(updatePublicationItems);
        });
            checkbox.dataset.boundBrandChannelChoice = 'true';
        }
    });
}

function createPublicationItem(channelName, channelIcon, date, time, dataChannel) {
    const formattedDate = date.split('-').reverse().join('-');
    const isFutureDate = new Date(`${date}T${time}`) > new Date() || !date;
    const publicationItem = document.createElement('a');
    publicationItem.className = `publication-item ${isFutureDate ? 'planned' : 'published'}`;
    publicationItem.setAttribute('data-channel', dataChannel);
    publicationItem.innerHTML = `
        <div class="publication-wrap">
            <div class="publication-item-heading">
                <div class="heading-left">
                    <i class="icon iconoir-${channelIcon}" title="${channelName}"></i>
                    <span class="publish-time">${formattedDate} ${time}</span>
                </div>
                <div class="heading-right">
                    <i class="icon iconoir-xmark" title="remove"></i>
                </div>
            </div>
            <div class="publication-item-content">
                <b>${channelName}</b><br>
            </div>
        </div>`;

    return publicationItem;
}

function sortPublications(container) {
    let publications = Array.from(container.querySelectorAll('.publication-item'));

    function parseDateFromDateTimeString(dateTimeStr) {
        if (!dateTimeStr) return new Date(0);
        let [datePart, timePart] = dateTimeStr.split(' ');
        let [day, month, year] = datePart.split('-').map(num => parseInt(num, 10));
        let [hours, minutes] = timePart.split(':').map(num => parseInt(num, 10));

        return new Date(year, month - 1, day, hours, minutes);
    }

    publications.sort((a, b) => {
        let dateA = parseDateFromDateTimeString(a.querySelector('.publish-time').textContent || '');
        let dateB = parseDateFromDateTimeString(b.querySelector('.publish-time').textContent || '');
        return dateA - dateB;
    });

    publications.forEach(publication => {
        container.appendChild(publication); // Simply append to the container
    });
}

function updatePublicationCount() {
    const publicationItems = document.querySelectorAll('.aside-item-list-container .publication-item');
    const pubCountSpan = document.querySelector('.aside-item-wrapper.publications .pub-count');
    if (pubCountSpan) {
        pubCountSpan.textContent = `(${publicationItems.length})`;
    }
}

function capturePublishTimeSnapshot(boundary) {
    const publishTimeRoot = document.querySelector('#integrated_content_publishTime');
    const dateInput = document.querySelector(`#integrated_content_publishTime_${boundary}_date`);
    const timeInput = document.querySelector(`#integrated_content_publishTime_${boundary}_time`);
    if (!publishTimeRoot || !dateInput || !timeInput) {
        return null;
    }

    publishTimeRoot.dataset[`prev${boundary}Date`] = dateInput.value || '';
    publishTimeRoot.dataset[`prev${boundary}Time`] = timeInput.value || '';

    return {
        publishTimeRoot,
        dateInput,
        timeInput,
    };
}

function syncPublicationDateTimeFields(boundary) {
    const publishTimeRoot = document.querySelector('#integrated_content_publishTime');
    const dateInput = document.querySelector(`#integrated_content_publishTime_${boundary}_date`);
    const timeInput = document.querySelector(`#integrated_content_publishTime_${boundary}_time`);
    if (!publishTimeRoot || !dateInput || !timeInput) {
        return;
    }

    const prevDate = publishTimeRoot.dataset[`prev${boundary}Date`] || '';
    const prevTime = publishTimeRoot.dataset[`prev${boundary}Time`] || '';
    const newDate = dateInput.value || '';
    const newTime = timeInput.value || '';
    if (!prevDate || !prevTime || !newDate || !newTime) {
        return;
    }

    const prevDateTime = `${prevDate} ${prevTime}`;
    const newDateText = `${newDate.split('-').reverse().join('-')} ${newTime}`;

    document.querySelectorAll('.publication-settings').forEach(function(setting) {
        const settingDateInput = setting.querySelector(`input[name*="[settings][time][${boundary}][date]"]`);
        const settingTimeInput = setting.querySelector(`input[name*="[settings][time][${boundary}][time]"]`);
        const settingDateText = setting.querySelector(`.${boundary} .date-text`);
        if (!settingDateInput || !settingTimeInput || !settingDateText) {
            return;
        }

        const currentDateTime = `${settingDateInput.value} ${settingTimeInput.value}`;
        if (currentDateTime !== prevDateTime) {
            return;
        }

        settingDateInput.value = newDate;
        settingTimeInput.value = newTime;
        settingDateText.textContent = newDateText;
    });
}

function setPublicationDateTimes(){
    if (document.body.dataset.boundSetPublicationDateTimes !== 'true') {
        document.addEventListener('click', function(e) {
            const dateTextButton = e.target.closest('#integrated_content_publishTime .startDate .date-text');
            if (!dateTextButton) {
                return;
            }

            if (!capturePublishTimeSnapshot('startDate')) {
                return;
            }
        });

        document.addEventListener('click', function(e) {
            const okDateButton = e.target.closest('#integrated_content_publishTime .startDate .ok-date');
            if (!okDateButton) {
                return;
            }

            syncPublicationDateTimeFields('startDate');
            updatePublicationsAndChannels();
            prepDateTimeFields();
        });

        document.addEventListener('click', function(e) {
            const dateTextButton = e.target.closest('#integrated_content_publishTime .endDate .date-text');
            if (!dateTextButton) {
                return;
            }

            if (!capturePublishTimeSnapshot('endDate')) {
                return;
            }
        });

        document.addEventListener('click', function(e) {
            const okDateButton = e.target.closest('#integrated_content_publishTime .endDate .ok-date');
            if (!okDateButton) {
                return;
            }

            syncPublicationDateTimeFields('endDate');
            updatePublicationsAndChannels();
            prepDateTimeFields();
        });

        document.body.dataset.boundSetPublicationDateTimes = 'true';
    }
}

function ensurePublicationExists(channelId) {
    const publicationListContainer = document.querySelector('.aside-item-wrapper.publications .aside-item-list-container .publication-list');
    if (!publicationListContainer) return;

    if (!publicationListContainer.querySelector(`a[data-channel="${channelId}"]`)) {
        let checkbox = document.querySelector(`[data-channel-selector="${channelId}"]`);
        if (!checkbox) return;

        let channelName = checkbox.getAttribute('data-channel-name');
        let channelIcon = checkbox.getAttribute('data-channel-type-icon');
        let channelSelector = checkbox.getAttribute('data-channel-selector');

        let channelSettings = document.querySelector(`[data-publication-channel="${channelId}"]`);

        let dateInput, timeInput;
        let fallbackDateInput = document.querySelector('#integrated_content_publishTime input[type="date"]');
        let fallbackTimeInput = document.querySelector('#integrated_content_publishTime input[type="time"]');
        if (!fallbackDateInput || !fallbackTimeInput) return;

        if (channelSettings) {
            const channelDateInput = channelSettings.querySelector('input[type="date"]');
            const channelTimeInput = channelSettings.querySelector('input[type="time"]');
            if (!channelDateInput || !channelTimeInput) return;

            dateInput = channelDateInput.value || fallbackDateInput.value;
            timeInput = channelTimeInput.value || fallbackTimeInput.value;
        } else {
            dateInput = fallbackDateInput.value;
            timeInput = fallbackTimeInput.value;
        }

        const publicationItem = createPublicationItem(channelName, channelIcon, dateInput, timeInput, channelSelector);
        publicationListContainer.appendChild(publicationItem);

        updatePublicationCount();
        updatePublicationsAndChannels();
        sortPublications(publicationListContainer);

        var updatePublicationItems = new CustomEvent('updatePublicationItems');

        window.dispatchEvent(updatePublicationItems);
    }
}

document.addEventListener('DOMContentLoaded', scheduleInitializePage);
window.addEventListener('load', scheduleInitializePage);
document.addEventListener('turbo:load', scheduleInitializePage);
document.addEventListener('turbo:render', scheduleInitializePage);
document.addEventListener('turbo:frame-load', scheduleInitializePage);
document.addEventListener('turbo:frame-render', scheduleInitializePage);

document.addEventListener("ensurePublicationEvent", function(e) {
    var channelId = e.detail.channelId; // Access channelId from the event detail
    ensurePublicationExists(channelId); // Call your function with channelId
});

window.addEventListener('applyPublishSettingsEvent', function(e) {
    prepDateTimeFields();
    setupCharacterCounters();
});

window.addEventListener('openPublishSettingsEvent', function(e) {
    prepDateTimeFields();
});
