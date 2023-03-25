const body = document.body;
const targetClassesClosing = '.close-outside.show';
const openClasses = [
    '.close-outside', //dropdown select
    '.button_toggle_upload_view', //media gallery button
    '.mobile-menu-toggle', //menu when in mobile mode
    '.toggle-filters', //filters when in mobile mode
];
let popupShown = false;

$(document).mouseup(function (e) {
    const closeOutside = $('.close-outside.show');
    const toggleButton = $('.toggle-button');
    const isTargetCloseOutside = closeOutside.is(e.target) || closeOutside.has(e.target).length > 0;
    const isTargetToggleButton = toggleButton.is(e.target) || toggleButton.has(e.target).length > 0;
    const isTargetInput = $(e.target).hasClass('tt-input') || $(e.target).hasClass('dropdown-menu');
    const clickedOutside = !isTargetCloseOutside && !isTargetToggleButton && !isTargetInput;

    if (clickedOutside) {
        closeOutside.removeClass('show');
        hideDropDownBackGround();
        popupShown = false;
    }
});

$(document).keyup(function (e) {
    if (e.key === 'Escape') {
        const closeOutside = $('.close-outside.show');
        if (closeOutside.is('#upload_container')) {
            closeOutside.removeClass('close-outside');
        }
        closeOutside.removeClass('show');
        hideDropDownBackGround();
        popupShown = false;
    }
});

function addPopupEventListeners() {
    $(document).on('mouseup', openClasses.join(','), function (event) {
        event.stopPropagation();
        openPopup();
    });
}

addPopupEventListeners();

function openPopup() {
    if (!popupShown) {
        enableClosingOfPopup();
        popupShown = true;
    }
}

function enableClosingOfPopup() {
    $(document).on('mouseup', handlePopupClose);
    $(document).on('keyup', handleKeyPress);
}

function handlePopupClose(event) {
    const closeOutside = $(targetClassesClosing);
    const clickedOutside = !closeOutside.is(event.target) && closeOutside.has(event.target).length === 0;

    if (clickedOutside) {
        event.stopPropagation();
        closeOutside.removeClass('show');
        disableEventListeners();
        hideDropDownBackGround();
        sendCancelEvent();
        popupShown = false;
    }
}

function disableEventListeners() {
    $(document).off('mouseup', handlePopupClose);
    $(document).off('keyup', handleKeyPress);
}

function handleKeyPress(event) {
    if (event.key === 'Escape') {
        handlePopupClose(event);
    }
}

function sendCancelEvent() {
    document.dispatchEvent(new CustomEvent("cancelPopupEvent"));
}

function hideDropDownBackGround() {
    const bg = document.getElementById('dropDownBackground');
    if (bg) {
        bg.classList.remove('show');
    }
}

const init = () => {
    const searchFormControl = document.querySelector('.search-form .form-control');
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

    document.addEventListener('DOMContentLoaded', hideButtonIfNoOptions);

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
                const parentList = input.closest('.aside-item-list, .editor-item-list');
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
    const hideToggleIfEmpty = (
        toggleSelector, containerSelector, minChildren = 0) => {
        const toggle = document.querySelector(toggleSelector);
        const container = document.querySelector(containerSelector);

        if (container && container.childNodes.length <= minChildren) {
            toggle.classList.add('hidden');
        }
    };

    hideToggleIfEmpty('.toggle-settings', '.aside-options');
    hideToggleIfEmpty('.form-actions-extra-toggle', '.form-actions-extra', 1);
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
    const options = el.parentNode.querySelector('.aside-item-list') ||
        el.parentNode.querySelector('.editor-item-list');
    if (options) {
        toggleElement(options);
    }
}

function toggleSidebarElement(event) {
    event.preventDefault();
    const menu = event.currentTarget.parentNode.querySelector(
        '.sub-menu-children');
    if (menu) {
        toggleElement(menu);
    }
}

function asideItemsSearch(event) {
    const input = event.target;
    const filter = input.value.toUpperCase();
    const ul = input.parentNode.parentNode.querySelector(
        '.aside-item-list .aside-item-list-container > ul');
    const li = ul.getElementsByTagName('li');

    for (const item of li) {
        const label = item.getElementsByTagName('label')[0];
        const txtValue = label.textContent || label.innerText;
        item.style.display = txtValue.toUpperCase().includes(filter) ?
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
    const menuItemDropDownUnderlay = document.querySelector(
        '#dropdown_overlay');
    menuItemDropDownUnderlay.classList.toggle('hide', hidden);
}

function hideDropDownBackGround() {
    toggleDropDownBackGround(true);
}

function showDropDownBackGround() {
    toggleDropDownBackGround(false);
}

// Initialization
document.addEventListener('DOMContentLoaded', init);
