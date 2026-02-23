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
        let publicationChannel = pubSettings.getAttribute('data-publication-channel');
        let checkbox = document.querySelector(`#integrated_content_brands [data-channel-selector="${publicationChannel}"]`);

        if (!checkbox) return;

        let brandChannel = checkbox.closest('.brand-channels');
        let brandInput = brandChannel.closest('.brand-container').querySelector('input.brand-choice');

        if (!brandInput.checked) return;

        let checkedChannels = Array.from(brandChannel.querySelectorAll('.brand-channels [type="checkbox"]:checked')).map(el => el.getAttribute('data-channel-selector'));

        if (!checkedChannels.length > 0) return;

        let dateInput, timeInput;
        let fallbackDateInput = document.querySelector('#integrated_content_publishTime input[type="date"]');
        let fallbackTimeInput = document.querySelector('#integrated_content_publishTime input[type="time"]');

        let pubTimeInput, pubDateInput;
        if (pubSettings) {
            pubDateInput = pubSettings.querySelector('input[type="date"]').value ||
                fallbackDateInput.value;
            pubTimeInput = pubSettings.querySelector('input[type="time"]').value ||
                fallbackTimeInput.value;
        }

        updatePublication(pubDateInput, pubTimeInput, publicationChannel, publicationListContainer)

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

        checkedChannels.forEach(channel => {
            let channelElement = publicationSettingsContainer.querySelector(`[data-publication-channel="${channel}"]:not([data-channel-type="Website"])`);
            if (!channelElement) return;

            let pubStatus = channelElement.getAttribute('data-publication-status');
            if (pubStatus === 'success') return;

            let channelDateInput = channelElement.querySelector('input[type="date"]').value;
            let channelTimeInput = channelElement.querySelector('input[type="time"]').value;
            let channelCheckboxParent = document.querySelector(`#integrated_content_brands [data-channel-selector="${channel}"]`).closest('.checkbox-container');

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

            let channelSettings = document.querySelector(`[data-publication-channel="${channelSelector}"]`);
            let pubStatus = channelSettings.getAttribute('data-publication-status');
            if (pubStatus === 'success') return;

            let publicationListContainer = document.querySelector('.aside-item-wrapper.publications .aside-item-list-container .publication-list');

            let dateInput, timeInput;
            let fallbackDateInput = document.querySelector('#integrated_content_publishTime input[type="date"]');
            let fallbackTimeInput = document.querySelector('#integrated_content_publishTime input[type="time"]');

            if (channelSettings) {
                dateInput = channelSettings.querySelector('input[type="date"]').value || fallbackDateInput.value;
                timeInput = channelSettings.querySelector('input[type="time"]').value || fallbackTimeInput.value;
            } else {
                dateInput = fallbackDateInput.value;
                timeInput = fallbackTimeInput.value;
            }

            if (checkbox.checked) {
                let publicationItem = createPublicationItem(channelName, channelIcon, dateInput, timeInput, channelSelector);
                publicationListContainer.appendChild(publicationItem);
            } else {
                let itemToRemove = publicationListContainer.querySelector(`[data-channel="${channelSelector}"]`);
                if (itemToRemove) {
                    publicationListContainer.removeChild(itemToRemove);
                }
            }

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

function setPublicationDateTimes(){
    if (document.body.dataset.boundSetPublicationDateTimes !== 'true') {
        document.addEventListener('click', function(e) {
            const dateTextButton = e.target.closest('#integrated_content_publishTime .startDate .date-text');
            if (!dateTextButton) {
                return;
            }

            const dateInput = document.querySelector('#integrated_content_publishTime_startDate_date');
            const timeInput = document.querySelector('#integrated_content_publishTime_startDate_time');
            const publishTimeRoot = document.querySelector('#integrated_content_publishTime');
            if (!dateInput || !timeInput || !publishTimeRoot) {
                return;
            }

            publishTimeRoot.dataset.prevDate = dateInput.value || '';
            publishTimeRoot.dataset.prevTime = timeInput.value || '';
        });

        document.addEventListener('click', function(e) {
            const okDateButton = e.target.closest('#integrated_content_publishTime .startDate .ok-date');
            if (!okDateButton) {
                return;
            }

            const dateInput = document.querySelector('#integrated_content_publishTime_startDate_date');
            const timeInput = document.querySelector('#integrated_content_publishTime_startDate_time');
            const publishTimeRoot = document.querySelector('#integrated_content_publishTime');
            if (!dateInput || !timeInput || !publishTimeRoot) {
                return;
            }

            const prevDate = publishTimeRoot.dataset.prevDate || '';
            const prevTime = publishTimeRoot.dataset.prevTime || '';
            const newDate = dateInput.value || '';
            const newTime = timeInput.value || '';
            if (!newDate || !newTime) {
                return;
            }

            const prevFormattedDateTime = `${prevDate} ${prevTime}`;
            const newFormattedDateTime = `${newDate.split('-').reverse().join('-')} ${newTime}`;

            document.querySelectorAll('.publication-settings').forEach(setting => {
                const settingDateText = setting.querySelector('.date-text');
                const settingDateInput = setting.querySelector('input[type="date"]');
                const settingTimeInput = setting.querySelector('input[type="time"]');
                if (!settingDateInput || !settingTimeInput || !settingDateText) {
                    return;
                }

                const currentDateTime = `${settingDateInput.value} ${settingTimeInput.value}`;
                if (currentDateTime === prevFormattedDateTime) {
                    settingDateText.textContent = newFormattedDateTime;
                    settingDateInput.value = newDate;
                    settingTimeInput.value = newTime;
                }
            });

            updatePublicationsAndChannels();
            prepDateTimeFields();
        });

        document.body.dataset.boundSetPublicationDateTimes = 'true';
    }
}

function ensurePublicationExists(channelId) {
    const publicationListContainer = document.querySelector('.aside-item-wrapper.publications .aside-item-list-container .publication-list');
    if (!publicationListContainer.querySelector(`a[data-channel="${channelId}"]`)) {
        let checkbox = document.querySelector(`[data-channel-selector="${channelId}"]`);
        let channelName = checkbox.getAttribute('data-channel-name');
        let channelIcon = checkbox.getAttribute('data-channel-type-icon');
        let channelSelector = checkbox.getAttribute('data-channel-selector');

        let channelSettings = document.querySelector(`[data-publication-channel="${channelId}"]`);

        let dateInput, timeInput;
        let fallbackDateInput = document.querySelector('#integrated_content_publishTime input[type="date"]');
        let fallbackTimeInput = document.querySelector('#integrated_content_publishTime input[type="time"]');

        if (channelSettings) {
            dateInput = channelSettings.querySelector('input[type="date"]').value || fallbackDateInput.value;
            timeInput = channelSettings.querySelector('input[type="time"]').value || fallbackTimeInput.value;
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

document.addEventListener('DOMContentLoaded', initializePage);
document.addEventListener('turbo:load', initializePage);
document.addEventListener('turbo:render', initializePage);

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
