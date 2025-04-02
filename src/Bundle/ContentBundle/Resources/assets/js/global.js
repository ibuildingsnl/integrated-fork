const body = document.body;
const targetClassesClosing = '.close-outside.show';
const openClasses = [
    '.close-outside', //dropdown select
    '.button_toggle_upload_view', //media gallery button
    '.mobile-menu-toggle', //menu when in mobile mode
    '.toggle-filters', //filters when in mobile mode
];
window.iframeWindow = '';
window.popupShown = false;

$(document).mouseup(function(e) {
    if(e.button !== 0) {
        return;
    }

    if (popupShown) {
        const closeOutside = $('.close-outside.show');
        const toggleButton = $('.toggle-button');
        const isTargetCloseOutside = closeOutside.is(e.target) ||
            closeOutside.has(e.target).length > 0;
        const isTargetToggleButton = toggleButton.is(e.target) ||
            toggleButton.has(e.target).length > 0;
        const isTargetInput = $(e.target).hasClass('tt-input') ||
            $(e.target).hasClass('dropdown-menu');
        const clickedOutside = !isTargetCloseOutside && !isTargetToggleButton &&
            !isTargetInput;

        if (clickedOutside) {
            closeOutside.removeClass('show');
            hideDropDownBackGround();
            sendCancelEvent();
            popupShown = false;
        }
    }
});

$(document).keyup(function(e) {
    if (e.key === 'Escape') {
        let closeOutside = $('.close-outside.show');
        closeOutside.removeClass('show');
        hideDropDownBackGround();
        sendCancelEvent();
        popupShown = false;
    }
});

function sendCancelEvent() {
    document.dispatchEvent(new CustomEvent('cancelPopupEvent'));
}

const init = () => {
    const searchFormControl = document.querySelector(
        '.search-form .form-control');
    if (searchFormControl) {
        searchFormControl.addEventListener('focus', showDropDownBackGround);
        searchFormControl.addEventListener('blur', hideDropDownBackGround);
    }

    document.querySelectorAll('a[data-toggle="dropdown"]').
        forEach(el => el.addEventListener('click', toggleDropdown));
    document.querySelectorAll('.toggle-data-target').
        forEach(el => el.addEventListener('click', toggleDataTarget));
    document.querySelectorAll('.menu-label').
        forEach(el => el.addEventListener('click', toggleSidebarElement));
    document.querySelectorAll('.toggle-options-sidebar').
        forEach(el => el.addEventListener('click', toggleOptionsSidebar));

    const listSearchElements = document.querySelectorAll('.list-search');
    listSearchElements.forEach(el => {
        el.addEventListener('change', asideItemsSearch);
        el.addEventListener('keyup', asideItemsSearch);
    });

    const filterElements = document.querySelectorAll('.aside-item-list');
    if (body.classList.contains('integrated_content_content_index')) {
        filterElements.forEach(openSelectedOptions);
    }

    const submitButtons = document.querySelectorAll('button[type=submit]');
    submitButtons.forEach(submitButton => submitButton.addEventListener('click',
        onSubmitButtonClick));

    const asideHolder = document.querySelector('.aside-holder');
    if (asideHolder) asideHolder.addEventListener('click', onAsideHolderClick);

    const editorSection = document.querySelector('section.editor');
    if (editorSection) editorSection.addEventListener('click',
        onEditorSectionClick);

    if (document.body.classList.contains('integrated_content_content_index')) {
        const filterElements = document.querySelectorAll('.aside-item-list');
        filterElements.forEach(openSelectedOptions);
    }
};

function onSubmitButtonClick(e) {
    const form = e.target.closest('form');
    const inputs = form.querySelectorAll('input, textarea');

    inputs.forEach(input => {
        if (input.required && input.value === '') {
            const wrapper = input.closest('.form-item, .form-group');

            if (wrapper) {
                const panel = input.closest('.panel');
                const panelCollapse = input.closest('.panel-collapse');

                panel && panel.classList.add('required', 'in');
                input.closest('.form-item').classList.add('required');
                panelCollapse && panelCollapse.classList.add('in');
            } else {
                const parentList = input.closest(
                    '.aside-item-list, .editor-item-list');
                parentList.parentNode.classList.add('required');
                showElement(parentList);
            }
        }
    });
}

function onAsideHolderClick(event) {
    if (event.target.classList.contains('aside-item-header')) {
        toggleOptionsElement(event.target);
    }
}

function onEditorSectionClick(event) {
    if (event.target.classList.contains('editor-item-header')) {
        toggleOptionsElement(event.target);
    }
}

function showElement(el) {
    el.style.height = '0';
    el.style.display = 'block';

    const height = el.scrollHeight + 'px';
    el.parentNode.classList.add('show');
    el.style.height = height;

    window.setTimeout(() => {
        el.style.height = '';
    }, 350);
}

function hideElement(el) {
    el.style.height = el.scrollHeight + 'px';

    requestAnimationFrame(() => {
        el.style.height = '0';

        window.setTimeout(() => {
            el.classList.remove('show');
            el.parentNode.classList.remove('show');
            el.style.display = '';
        }, 350);
    });
}

function toggleElement(el) {
    if (el.parentNode.classList.contains('show') ||
        el.classList.contains('show')) {
        hideElement(el);
    } else {
        showElement(el);
    }
}

function hideButtonIfNoOptions() {
    const optionsToggle = document.querySelector('.toggle-settings');
    const optionsDiv = document.querySelector('.aside-options');
    if (optionsDiv && (optionsDiv.childNodes.length === 0)) {
        optionsToggle.classList.add('hidden');
    }
    const actionsToggle = document.querySelector('.form-actions-extra-toggle');
    const actionsDiv = document.querySelector('.form-actions-extra');
    if (actionsDiv && (actionsDiv.childNodes.length < 2)) {
        actionsToggle.classList.add('hidden');
    }
}

function toggleOptionsSidebar() {
    const viewportWidth = window.innerWidth;
    const optionsMenu = document.querySelector('.aside-options');
    const shouldShow = (viewportWidth < 1025 &&
            !optionsMenu.classList.contains('show')) ||
        document.body.classList.contains('hide-options');

    document.body.classList.toggle('show-options', shouldShow);
    optionsMenu.classList.toggle('show', shouldShow);

    document.body.classList.toggle('hide-options', !shouldShow);
    optionsMenu.classList.toggle('hide', !shouldShow);
}

function toggleDataTarget(el) {
    const targetElement = document.querySelector(
        '.' + el.target.getAttribute('data-target'));
    const shouldShow = !targetElement.classList.contains('show');
    targetElement.classList.toggle('show', shouldShow);

    if (el.target.getAttribute('data-underlay') !== 'no') {
        shouldShow ? showDropDownBackGround() : hideDropDownBackGround();
    }
}

function toggleDropdown(el) {
    popupShown = true;
    const dropdown = el.target.closest('.dropdown');
    const shouldShow = !dropdown.classList.contains('show');

    if (shouldShow) {
        const dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(d => d.classList.remove('show'));
    }

    dropdown.classList.toggle('show', shouldShow);

    if (dropdown.getAttribute('data-underlay') !== 'no') {
        shouldShow ? showDropDownBackGround() : hideDropDownBackGround();
    }
}

function toggleOptionsElement(el) {
    popupShown = true;
    const options = el.parentNode.querySelector('.aside-item-list') ||
        el.parentNode.querySelector('.editor-item-list');
    if (options) {
        toggleElement(options);
    }
}

function toggleSidebarElement(event) {
    popupShown = true;
    event.preventDefault();
    const menu = event.currentTarget.parentNode.querySelector(
        '.sub-menu-children');
    if (menu) {
        toggleElement(menu);
    }
}

function removeDiacritics(str) {
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

function asideItemsSearch(el) {
    let input, filter, ul, li, a, i, txtValue;
    input = el.target;
    filter = removeDiacritics(input.value).toUpperCase();
    ul = el.target.parentNode.parentNode.querySelector(
        '.aside-item-list .aside-item-list-container ul');
    li = ul.getElementsByTagName('li');

    for (const item of li) {
        const label = item.getElementsByTagName('label')[0];
        const txtValue = label.textContent || label.innerText;
        item.style.display = removeDiacritics(txtValue).toUpperCase().includes(filter) ?
            '' :
            'none';
    }
}

function openSelectedOptions(elem) {
    const textinputs = elem.querySelectorAll('input[type=checkbox]');
    const hasCheckedInputs = Array.from(textinputs).
        some(input => input.checked);

    if (!elem.parentNode.classList.contains('show') && hasCheckedInputs) {
        showElement(elem);
    }
}

function toggleDropDownBackGround(hidden) {
    let menuItemDropDownUnderlay = document.querySelector('#dropdown_overlay');
    let taxonomyDropDownUnderlays = document.querySelectorAll('.taxonomy_backdrop');
    document.querySelector('body').classList.remove('popup-open');
    menuItemDropDownUnderlay.classList.toggle('hide', hidden);
    menuItemDropDownUnderlay.innerHTML = '';

    taxonomyDropDownUnderlays.forEach(taxonomyDropDownUnderlay => {
        // Toggle 'hide' only if it does not have the 'hide' class
        if (!taxonomyDropDownUnderlay.classList.contains('hide')) {
            taxonomyDropDownUnderlay.classList.toggle('hide', hidden);
        }
    });
}

function isElement(o) {
    return (
        typeof HTMLElement === 'object' ? o instanceof HTMLElement : //DOM2
            o && typeof o === 'object' && o.nodeType === 1 &&
            typeof o.nodeName === 'string'
    );
}

function hideDropDownBackGround() {
    popupShown = false;
    toggleDropDownBackGround(true);
}

function showDropDownBackGround() {
    popupShown = true;
    toggleDropDownBackGround(false);
}

document.addEventListener('DOMContentLoaded', function() {
    var dismissibleAlerts = document.querySelectorAll('.alert-dismissible');

    setTimeout(function() {
        dismissibleAlerts.forEach(function(alert) {
            alert.remove();
        });
    }, 10000);
});

// Initialization

document.addEventListener('DOMContentLoaded', hideButtonIfNoOptions);
document.addEventListener('DOMContentLoaded', init);
