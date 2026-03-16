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
const CONTENT_NAVIGATOR_OPEN_FACETS_KEY = 'contentNavigator.openFacets.v1';
const CONTENT_NAVIGATOR_ASIDE_SCROLL_KEY = 'contentNavigator.asideScrollTop.v1';
const FLASH_MESSAGES_PERSIST_KEY = 'integrated.flashMessages.persist.v1';
const SIDEBAR_MENU_SCROLL_KEY = 'integrated.sidebarMenu.scrollTop.v2';
const SIDEBAR_MENU_OPEN_ITEMS_KEY = 'integrated.sidebarMenu.openItems.v2';
const OPTIONS_SIDEBAR_HIDDEN_KEY = 'integrated.optionsSidebar.hidden.v1';
const listSearchDebounceTimers = new WeakMap();

function normalizeSidebarHref(href) {
    if (!href) {
        return '';
    }

    try {
        const url = new URL(href, window.location.origin);
        return `${url.pathname}${url.search}`.trim();
    } catch (error) {
        return String(href).trim();
    }
}

function buildSidebarMenuPersistenceKey(wrapper, index) {
    const title = wrapper.querySelector('.menu-label .title');
    const titleKey = title ? title.textContent.trim() : '';
    const firstLink = wrapper.querySelector('.sub-menu-children a[href]');
    const hrefKey = normalizeSidebarHref(firstLink ? firstLink.getAttribute('href') : '');
    const base = hrefKey || titleKey || 'menu-item';

    return `${index}:${base}`;
}

function ensureSidebarMenuPersistenceKeys(root = document) {
    const rootNode = (root && typeof root.querySelector === 'function')
        ? root
        : ((root && root.target && typeof root.target.querySelector === 'function') ? root.target : document);

    rootNode.querySelectorAll('.sidebar-menu-wrapper .sidebar-sub-menu').forEach((wrapper, index) => {
        if (!wrapper.dataset.sidebarMenuKey) {
            wrapper.dataset.sidebarMenuKey = buildSidebarMenuPersistenceKey(wrapper, index);
        }
    });
}

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
        if (!searchFormControl.dataset.boundFocus) {
            searchFormControl.addEventListener('focus', showDropDownBackGround);
            searchFormControl.addEventListener('blur', hideDropDownBackGround);
            searchFormControl.dataset.boundFocus = 'true';
        }
    }

    document.querySelectorAll('a[data-toggle="dropdown"]').
        forEach(el => {
            if (el.dataset.boundToggleDropdown) return;
            el.addEventListener('click', toggleDropdown);
            el.dataset.boundToggleDropdown = 'true';
        });
    document.querySelectorAll('.toggle-data-target').
        forEach(el => {
            if (el.dataset.boundToggleDataTarget) return;
            el.addEventListener('click', toggleDataTarget);
            el.dataset.boundToggleDataTarget = 'true';
        });
    document.querySelectorAll('.menu-label').
        forEach(el => {
            if (el.dataset.boundToggleSidebar) return;
            el.addEventListener('click', toggleSidebarElement);
            el.dataset.boundToggleSidebar = 'true';
        });
    document.querySelectorAll('.toggle-options-sidebar').
        forEach(el => {
            if (el.dataset.boundToggleOptions) return;
            el.addEventListener('click', toggleOptionsSidebar);
            el.dataset.boundToggleOptions = 'true';
        });

    const listSearchElements = document.querySelectorAll('.list-search');
    listSearchElements.forEach(el => {
        if (el.dataset.boundListSearch) return;
        el.addEventListener('input', debouncedAsideItemsSearch);
        el.addEventListener('change', asideItemsSearch);
        el.dataset.boundListSearch = 'true';
    });

    const submitButtons = document.querySelectorAll('button[type=submit]');
    submitButtons.forEach(submitButton => {
        if (submitButton.dataset.boundSubmitClick) return;
        submitButton.addEventListener('click', onSubmitButtonClick);
        submitButton.dataset.boundSubmitClick = 'true';
    });

    const asideHolder = document.querySelector('.aside-holder');
    if (asideHolder && !asideHolder.dataset.boundAsideHolder) {
        asideHolder.addEventListener('click', onAsideHolderClick);
        asideHolder.addEventListener('keydown', onAsideHolderKeyDown);
        asideHolder.dataset.boundAsideHolder = 'true';
    }

    const editorSection = document.querySelector('section.editor');
    if (editorSection && !editorSection.dataset.boundEditorSection) {
        editorSection.addEventListener('click', onEditorSectionClick);
        editorSection.dataset.boundEditorSection = 'true';
    }

    // Keep content navigator facet groups in their default collapsed state.
    // Do not auto-open groups with checked filters on load.
    if (!document.body.classList.contains('integrated_content_content_index')) {
        const filterElements = document.querySelectorAll('.aside-item-list');
        filterElements.forEach(openSelectedOptions);
    }

    syncContentWrapperToolbarState();
};

function onSubmitButtonClick(e) {
    const form = e.target.closest('form') || e.target.form;
    if (!form) {
        return;
    }

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
    const asideHeader = event.target.closest('.aside-item-header');
    if (asideHeader) {
        toggleOptionsElement(asideHeader);
    }
}

function onAsideHolderKeyDown(event) {
    const asideHeader = event.target.closest('.aside-item-header');
    if (!asideHeader) {
        return;
    }

    if (event.key !== 'Enter' && event.key !== ' ') {
        return;
    }

    event.preventDefault();
    toggleOptionsElement(asideHeader);
}

function onEditorSectionClick(event) {
    const editorHeader = event.target.closest('.editor-item-header');
    if (editorHeader) {
        toggleOptionsElement(editorHeader);
    }
}

function syncContentWrapperToolbarState() {
    const pageBody = document.body;
    if (!pageBody) {
        return;
    }

    const contentWrapper = document.querySelector('#wrapper-holder > .content-wrapper');
    if (!contentWrapper) {
        pageBody.classList.remove('has-content-toolbar');

        return;
    }

    const toolbars = contentWrapper.querySelectorAll('#toolbar');
    const hasSupportedToolbar = Array.from(toolbars).some((toolbar) => {
        return !toolbar.closest('.style-guide-preview-surface');
    });

    pageBody.classList.toggle('has-content-toolbar', hasSupportedToolbar);
}

function showElement(el) {
    el.style.height = '0';
    const isSidebarSubMenu = el.classList.contains('sub-menu-children');

    if (!isSidebarSubMenu) {
        el.style.display = 'block';
    }

    if (isSidebarSubMenu) {
        el.parentNode.classList.add('show');
    }

    const height = el.scrollHeight + 'px';
    if (!isSidebarSubMenu) {
        el.parentNode.classList.add('show');
    }
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
        optionsToggle && optionsToggle.classList.add('hidden');
    }
    const actionsToggle = document.querySelector('.form-actions-extra-toggle');
    const actionsDiv = document.querySelector('.form-actions-extra');
    if (actionsDiv && (actionsDiv.childNodes.length < 2)) {
        actionsToggle && actionsToggle.classList.add('hidden');
    }
}

function toggleOptionsSidebar() {
    const viewportWidth = window.innerWidth;
    const optionsMenu = document.querySelector('.aside-options');
    if (!optionsMenu) {
        return;
    }
    const shouldShow = (viewportWidth < 1025 &&
            !optionsMenu.classList.contains('show')) ||
        document.body.classList.contains('hide-options');

    applyOptionsSidebarState(optionsMenu, shouldShow);
    persistOptionsSidebarState(shouldShow);
}

function applyOptionsSidebarState(optionsMenu, shouldShow) {
    if (!optionsMenu) {
        return;
    }

    document.body.classList.toggle('show-options', shouldShow);
    optionsMenu.classList.toggle('show', shouldShow);

    document.body.classList.toggle('hide-options', !shouldShow);
    optionsMenu.classList.toggle('hide', !shouldShow);
}

function persistOptionsSidebarState(isVisible) {
    if (typeof window.sessionStorage === 'undefined') {
        return;
    }

    window.sessionStorage.setItem(OPTIONS_SIDEBAR_HIDDEN_KEY, isVisible ? '0' : '1');
}

function persistCurrentOptionsSidebarState() {
    const optionsMenu = document.querySelector('.aside-options');
    if (!optionsMenu) {
        return;
    }

    const bodyHidden = document.body.classList.contains('hide-options');
    const classHidden = optionsMenu.classList.contains('hide');
    const computedStyle = window.getComputedStyle(optionsMenu);
    const computedHidden = computedStyle.display === 'none' ||
        computedStyle.visibility === 'hidden';
    const isVisible = !(bodyHidden || classHidden || computedHidden);

    persistOptionsSidebarState(isVisible);
}

function getPersistedOptionsSidebarHiddenState() {
    if (typeof window.sessionStorage === 'undefined') {
        return null;
    }

    const stored = window.sessionStorage.getItem(OPTIONS_SIDEBAR_HIDDEN_KEY);
    if (stored !== '0' && stored !== '1') {
        return null;
    }

    return stored === '1';
}

function restorePersistedOptionsSidebarState(root = document) {
    const rootNode = (root && typeof root.querySelector === 'function')
        ? root
        : ((root && root.target && typeof root.target.querySelector === 'function') ? root.target : document);

    const optionsMenu = rootNode.querySelector('.aside-options') ||
        document.querySelector('.aside-options');
    if (!optionsMenu) {
        return;
    }

    const isHidden = getPersistedOptionsSidebarHiddenState();
    if (isHidden === null) {
        return;
    }

    applyOptionsSidebarState(optionsMenu, !isHidden);
}

function scheduleRestorePersistedOptionsSidebarState(root = document) {
    restorePersistedOptionsSidebarState(root);

    requestAnimationFrame(() => {
        restorePersistedOptionsSidebarState(root);
    });

    window.setTimeout(() => {
        restorePersistedOptionsSidebarState(root);
    }, 50);
}

function bindOptionsSidebarStateTracking() {
    if (document.body && document.body.dataset.boundOptionsSidebarStateTracking) {
        return;
    }

    document.addEventListener('click', (event) => {
        const toggleButton = event.target.closest('.toggle-options-sidebar');
        if (!toggleButton) {
            return;
        }

        window.setTimeout(() => {
            persistCurrentOptionsSidebarState();
        }, 0);
    }, true);

    const observer = new MutationObserver(() => {
        persistCurrentOptionsSidebarState();
    });
    observer.observe(document.body, {
        attributes: true,
        subtree: true,
        attributeFilter: ['class', 'style'],
    });

    if (document.body) {
        document.body.dataset.boundOptionsSidebarStateTracking = 'true';
    }
}

function toggleDataTarget(el) {
    const toggleElement = el.currentTarget || el.target.closest('.toggle-data-target');
    if (!toggleElement) {
        return;
    }

    const targetElement = document.querySelector(
        '.' + toggleElement.getAttribute('data-target'));
    if (!targetElement) {
        return;
    }
    const shouldShow = !targetElement.classList.contains('show');
    targetElement.classList.toggle('show', shouldShow);

    if (toggleElement.getAttribute('data-underlay') !== 'no') {
        shouldShow ? showDropDownBackGround() : hideDropDownBackGround();
    }
}

function toggleDropdown(el) {
    if (el && typeof el.preventDefault === 'function') {
        el.preventDefault();
    }

    popupShown = true;
    const toggle = el.currentTarget || el.target.closest('a[data-toggle="dropdown"]');
    const dropdown = toggle ? toggle.closest('.dropdown') : null;
    if (!dropdown) {
        return;
    }
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
        if (el.classList.contains('aside-item-header')) {
            el.setAttribute('aria-expanded', options.parentNode.classList.contains('show') ? 'true' : 'false');
        }
        if (document.body.classList.contains('integrated_content_content_index')) {
            persistFacetState(options.parentNode, options.parentNode.classList.contains('show'));
        }
    }
}

function toggleSidebarElement(event) {
    popupShown = true;
    event.preventDefault();
    const wrapper = event.currentTarget.parentNode;
    const menu = wrapper.querySelector('.sub-menu-children');
    if (menu) {
        const willOpen = !(wrapper.classList.contains('show') || menu.classList.contains('show'));
        toggleElement(menu);
        persistSidebarMenuState(wrapper, willOpen);
    }
}

function removeDiacritics(str) {
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

function asideItemsSearch(el) {
    let input, filter, ul, li;
    input = el.target;
    filter = removeDiacritics(input.value).toUpperCase();
    ul = el.target.parentNode.parentNode.querySelector(
        '.aside-item-list .aside-item-list-container ul');
    if (!ul) {
        return;
    }
    li = ul.getElementsByTagName('li');

    for (const item of li) {
        const label = item.getElementsByTagName('label')[0];
        const txtValue = label.textContent || label.innerText;
        item.style.display = removeDiacritics(txtValue).toUpperCase().includes(filter) ?
            '' :
            'none';
    }
}

function debouncedAsideItemsSearch(event) {
    const input = event.target;
    const existing = listSearchDebounceTimers.get(input);
    if (existing) {
        clearTimeout(existing);
    }

    const timer = setTimeout(() => asideItemsSearch(event), 150);
    listSearchDebounceTimers.set(input, timer);
}

function openSelectedOptions(elem) {
    const textinputs = elem.querySelectorAll('input[type=checkbox]');
    const hasCheckedInputs = Array.from(textinputs).
        some(input => input.checked);

    if (!elem.parentNode.classList.contains('show') && hasCheckedInputs) {
        showElement(elem);
    }
}

function getPersistedOpenFacets() {
    let persisted = [];

    try {
        persisted = JSON.parse(sessionStorage.getItem(CONTENT_NAVIGATOR_OPEN_FACETS_KEY) || '[]');
    } catch (error) {
        persisted = [];
    }

    return new Set(Array.isArray(persisted) ? persisted : []);
}

function savePersistedOpenFacets(facets) {
    sessionStorage.setItem(CONTENT_NAVIGATOR_OPEN_FACETS_KEY, JSON.stringify(Array.from(facets)));
}

function getFacetPersistenceKey(wrapper) {
    if (!wrapper) {
        return '';
    }

    const title = wrapper.querySelector('.aside-item-title');
    return title ? title.textContent.trim() : '';
}

function persistFacetState(wrapper, isOpen) {
    const key = getFacetPersistenceKey(wrapper);
    if (!key) {
        return;
    }

    const facets = getPersistedOpenFacets();
    if (isOpen) {
        facets.add(key);
    } else {
        facets.delete(key);
    }

    savePersistedOpenFacets(facets);
}

function restorePersistedFacetState() {
    if (!document.body.classList.contains('integrated_content_content_index')) {
        return;
    }

    applyPersistedFacetState(document, true);
}

function restoreAsideScrollPosition(root = document) {
    if (!document.body.classList.contains('integrated_content_content_index')) {
        return;
    }

    const rootNode = (root && typeof root.querySelector === 'function')
        ? root
        : ((root && root.target && typeof root.target.querySelector === 'function') ? root.target : document);

    const asideHolder = rootNode.querySelector('.aside-holder');
    if (!asideHolder) {
        return;
    }

    const stored = sessionStorage.getItem(CONTENT_NAVIGATOR_ASIDE_SCROLL_KEY);
    const scrollTop = Number.parseInt(stored || '0', 10);
    if (!Number.isNaN(scrollTop) && scrollTop > 0) {
        asideHolder.scrollTop = scrollTop;
    }
}

function restoreSidebarMenuScrollPosition(root = document) {
    const rootNode = (root && typeof root.querySelector === 'function')
        ? root
        : ((root && root.target && typeof root.target.querySelector === 'function') ? root.target : document);

    const sidebarMenu = rootNode.querySelector('.sidebar-menu');
    if (!sidebarMenu) {
        return;
    }

    const stored = sessionStorage.getItem(SIDEBAR_MENU_SCROLL_KEY);
    const scrollTop = Number.parseInt(stored || '0', 10);
    if (!Number.isNaN(scrollTop) && scrollTop > 0) {
        sidebarMenu.scrollTop = scrollTop;
    }
}

function persistSidebarMenuScrollPosition(root = document) {
    const rootNode = (root && typeof root.querySelector === 'function')
        ? root
        : ((root && root.target && typeof root.target.querySelector === 'function') ? root.target : document);

    const sidebarMenu = rootNode.querySelector('.sidebar-menu');
    if (!sidebarMenu) {
        return;
    }

    sessionStorage.setItem(SIDEBAR_MENU_SCROLL_KEY, String(sidebarMenu.scrollTop));
}

function getPersistedOpenSidebarMenuItems() {
    if (typeof window.sessionStorage === 'undefined') {
        return null;
    }

    const raw = sessionStorage.getItem(SIDEBAR_MENU_OPEN_ITEMS_KEY);
    if (raw === null) {
        return null;
    }

    let persisted = [];

    try {
        persisted = JSON.parse(raw);
    } catch (error) {
        persisted = [];
    }

    return new Set(Array.isArray(persisted) ? persisted : []);
}

function savePersistedOpenSidebarMenuItems(items) {
    sessionStorage.setItem(SIDEBAR_MENU_OPEN_ITEMS_KEY, JSON.stringify(Array.from(items)));
}

function getSidebarMenuPersistenceKey(wrapper) {
    if (!wrapper) {
        return '';
    }

    if (!wrapper.dataset.sidebarMenuKey) {
        const wrappers = document.querySelectorAll('.sidebar-menu-wrapper .sidebar-sub-menu');
        const index = Array.from(wrappers).indexOf(wrapper);
        wrapper.dataset.sidebarMenuKey = buildSidebarMenuPersistenceKey(wrapper, index >= 0 ? index : 0);
    }

    return wrapper.dataset.sidebarMenuKey || '';
}

function persistSidebarMenuState(wrapper, isOpen) {
    const key = getSidebarMenuPersistenceKey(wrapper);
    if (!key) {
        return;
    }

    const items = getPersistedOpenSidebarMenuItems() || new Set();
    if (isOpen) {
        items.add(key);
    } else {
        items.delete(key);
    }
    savePersistedOpenSidebarMenuItems(items);
}

function applyPersistedSidebarMenuState(root = document) {
    const items = getPersistedOpenSidebarMenuItems();
    if (items === null) {
        return;
    }

    ensureSidebarMenuPersistenceKeys(root);

    root.querySelectorAll('.sidebar-menu-wrapper .sidebar-sub-menu').forEach((wrapper) => {
        const key = getSidebarMenuPersistenceKey(wrapper);
        const list = wrapper.querySelector('.sub-menu-children');
        if (!key || !list) {
            return;
        }

        const shouldOpen = items.has(key);
        wrapper.classList.toggle('show', shouldOpen);
        list.classList.toggle('show', shouldOpen);
        list.style.height = '';
    });
}

function restorePersistedSidebarMenuState(root = document) {
    const rootNode = (root && typeof root.querySelector === 'function')
        ? root
        : ((root && root.target && typeof root.target.querySelector === 'function') ? root.target : document);

    ensureSidebarMenuPersistenceKeys(rootNode);
    const persistedItems = getPersistedOpenSidebarMenuItems();
    if (persistedItems === null) {
        applyDefaultSidebarMenuState(rootNode);
        return;
    }

    applyPersistedSidebarMenuState(rootNode);
}

function applyDefaultSidebarMenuState(root = document) {
    const wrappers = Array.from(root.querySelectorAll('.sidebar-menu-wrapper .sidebar-sub-menu'));
    if (wrappers.length === 0) {
        return;
    }

    const hasOpenWrapper = wrappers.some((wrapper) => wrapper.classList.contains('show'));
    if (hasOpenWrapper) {
        return;
    }

    const contentWrapper = wrappers.find((wrapper) => wrapper.dataset.sidebarDefault === 'content');
    if (!contentWrapper) {
        return;
    }

    const list = contentWrapper.querySelector('.sub-menu-children');
    if (!list) {
        return;
    }

    contentWrapper.classList.add('show');
    list.classList.add('show');
    list.style.height = '';
}

function announceContentNavigatorResults(root = document) {
    const rootNode = (root && typeof root.querySelector === 'function')
        ? root
        : ((root && root.target && typeof root.target.querySelector === 'function') ? root.target : document);

    const frame = rootNode.id === 'content-navigator' ? rootNode : rootNode.querySelector('#content-navigator');
    if (!frame) {
        return;
    }

    const liveRegion = frame.querySelector('#content-navigator-live-region');
    if (!liveRegion) {
        return;
    }

    let resultCount = Number.parseInt(frame.dataset.resultCount || '0', 10);
    if (Number.isNaN(resultCount)) {
        resultCount = 0;
    }

    const resultText = resultCount === 1 ? '1 result loaded.' : `${resultCount} results loaded.`;
    liveRegion.textContent = resultText;
}

function applyPersistedFacetState(root, animate) {
    const facets = getPersistedOpenFacets();
    root.querySelectorAll('.aside-holder .aside-item-wrapper').forEach((wrapper) => {
        const key = getFacetPersistenceKey(wrapper);
        const list = wrapper.querySelector('.aside-item-list');
        const header = wrapper.querySelector('.aside-item-header');
        if (!key || !list) {
            return;
        }

        if (facets.has(key) && !wrapper.classList.contains('show')) {
            if (animate) {
                showElement(list);
            } else {
                wrapper.classList.add('show');
                list.classList.add('show');
                list.style.display = 'block';
                list.style.height = '';
            }

            if (header) {
                header.setAttribute('aria-expanded', 'true');
            }
        } else if (header) {
            header.setAttribute('aria-expanded', wrapper.classList.contains('show') ? 'true' : 'false');
        }
    });
}

function bindFacetPersistenceOnFilterChange() {
    if (!document.body.classList.contains('integrated_content_content_index')) {
        return;
    }

    const asideHolder = document.querySelector('.aside-holder');
    if (!asideHolder || asideHolder.dataset.boundFacetPersistenceChange) {
        return;
    }

    asideHolder.addEventListener('change', (event) => {
        const input = event.target;
        if (!input.matches('.aside-item-list input[type=checkbox]')) {
            return;
        }

        const wrapper = input.closest('.aside-item-wrapper');
        if (wrapper) {
            persistFacetState(wrapper, true);
        }
    });
    asideHolder.dataset.boundFacetPersistenceChange = 'true';
}

function toggleDropDownBackGround(hidden) {
    let menuItemDropDownUnderlay = document.querySelector('#dropdown_overlay');
    let taxonomyDropDownUnderlays = document.querySelectorAll('.taxonomy_backdrop');
    document.querySelector('body').classList.remove('popup-open');
    if (!menuItemDropDownUnderlay) {
        return;
    }
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

function resetPopupState() {
    document.querySelectorAll('.close-outside.show').forEach((el) => {
        el.classList.remove('show');
    });
    hideDropDownBackGround();
}

function initDismissibleAlerts() {
    var dismissibleAlerts = document.querySelectorAll('.alert-dismissible');

    setTimeout(function() {
        dismissibleAlerts.forEach(function(alert) {
            if (!(alert instanceof HTMLElement)) {
                return;
            }

            var role = String(alert.getAttribute('role') || '').toLowerCase();
            if (role === 'alert' || alert.classList.contains('alert-danger') || alert.getAttribute('data-persist') === '1') {
                return;
            }

            alert.remove();
        });
    }, 10000);
}

function clearBoundInitializationFlags() {
    const selectors = [
        '[data-bound-focus]',
        '[data-bound-toggle-dropdown]',
        '[data-bound-toggle-data-target]',
        '[data-bound-toggle-sidebar]',
        '[data-bound-toggle-options]',
        '[data-bound-list-search]',
        '[data-bound-submit-click]',
        '[data-bound-aside-holder]',
        '[data-bound-editor-section]',
        '[data-bound-facet-persistence-change]',
    ];

    document.querySelectorAll(selectors.join(',')).forEach((element) => {
        element.removeAttribute('data-bound-focus');
        element.removeAttribute('data-bound-toggle-dropdown');
        element.removeAttribute('data-bound-toggle-data-target');
        element.removeAttribute('data-bound-toggle-sidebar');
        element.removeAttribute('data-bound-toggle-options');
        element.removeAttribute('data-bound-list-search');
        element.removeAttribute('data-bound-submit-click');
        element.removeAttribute('data-bound-aside-holder');
        element.removeAttribute('data-bound-editor-section');
        element.removeAttribute('data-bound-facet-persistence-change');
    });
}

function flashMessageSignature(node) {
    if (!(node instanceof HTMLElement)) {
        return '';
    }

    const className = node.className || '';
    const text = node.textContent ? node.textContent.replace(/\s+/g, ' ').trim() : '';

    return `${className}::${text}`;
}

function stashFlashMessagesForNextVisit() {
    if (typeof window.sessionStorage === 'undefined') {
        return;
    }

    const flashContainer = document.getElementById('flash-messages');
    if (!(flashContainer instanceof HTMLElement)) {
        window.sessionStorage.removeItem(FLASH_MESSAGES_PERSIST_KEY);

        return;
    }

    const alerts = Array.from(flashContainer.children).filter((child) => {
        return child instanceof HTMLElement && child.classList.contains('alert');
    });
    const payload = alerts.map((alert) => alert.outerHTML);

    if (payload.length === 0) {
        window.sessionStorage.removeItem(FLASH_MESSAGES_PERSIST_KEY);

        return;
    }

    window.sessionStorage.setItem(FLASH_MESSAGES_PERSIST_KEY, JSON.stringify(payload));
}

function restoreFlashMessagesFromPreviousVisit() {
    if (typeof window.sessionStorage === 'undefined') {
        return;
    }

    const flashContainer = document.getElementById('flash-messages');
    if (!(flashContainer instanceof HTMLElement)) {
        window.sessionStorage.removeItem(FLASH_MESSAGES_PERSIST_KEY);

        return;
    }

    const rawPayload = window.sessionStorage.getItem(FLASH_MESSAGES_PERSIST_KEY);
    window.sessionStorage.removeItem(FLASH_MESSAGES_PERSIST_KEY);
    if (!rawPayload) {
        return;
    }

    let payload = null;
    try {
        payload = JSON.parse(rawPayload);
    } catch (error) {
        return;
    }

    if (!Array.isArray(payload)) {
        return;
    }

    payload.forEach((html) => {
        if (typeof html !== 'string' || html.trim() === '') {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;

        const alert = wrapper.firstElementChild;
        if (!(alert instanceof HTMLElement) || !alert.classList.contains('alert')) {
            return;
        }

        const signature = flashMessageSignature(alert);
        const exists = Array.from(flashContainer.children).some((child) => {
            return flashMessageSignature(child) === signature;
        });
        if (exists) {
            return;
        }

        flashContainer.insertBefore(alert, flashContainer.firstChild);
    });
}

// Initialization

document.addEventListener('DOMContentLoaded', restoreFlashMessagesFromPreviousVisit);
document.addEventListener('DOMContentLoaded', initDismissibleAlerts);
document.addEventListener('DOMContentLoaded', hideButtonIfNoOptions);
document.addEventListener('DOMContentLoaded', init);
document.addEventListener('DOMContentLoaded', restorePersistedFacetState);
document.addEventListener('DOMContentLoaded', restoreAsideScrollPosition);
document.addEventListener('DOMContentLoaded', restoreSidebarMenuScrollPosition);
document.addEventListener('DOMContentLoaded', restorePersistedSidebarMenuState);
document.addEventListener('DOMContentLoaded', scheduleRestorePersistedOptionsSidebarState);
document.addEventListener('DOMContentLoaded', announceContentNavigatorResults);
document.addEventListener('DOMContentLoaded', bindFacetPersistenceOnFilterChange);
document.addEventListener('DOMContentLoaded', bindOptionsSidebarStateTracking);
document.addEventListener('turbo:load', restoreFlashMessagesFromPreviousVisit);
document.addEventListener('turbo:load', initDismissibleAlerts);
document.addEventListener('turbo:load', hideButtonIfNoOptions);
document.addEventListener('turbo:load', init);
document.addEventListener('turbo:load', restorePersistedFacetState);
document.addEventListener('turbo:load', restoreAsideScrollPosition);
document.addEventListener('turbo:load', restoreSidebarMenuScrollPosition);
document.addEventListener('turbo:load', restorePersistedSidebarMenuState);
document.addEventListener('turbo:load', scheduleRestorePersistedOptionsSidebarState);
document.addEventListener('turbo:load', announceContentNavigatorResults);
document.addEventListener('turbo:load', bindFacetPersistenceOnFilterChange);
document.addEventListener('turbo:load', bindOptionsSidebarStateTracking);
document.addEventListener('turbo:render', hideButtonIfNoOptions);
document.addEventListener('turbo:render', init);
document.addEventListener('turbo:render', restorePersistedFacetState);
document.addEventListener('turbo:render', restoreAsideScrollPosition);
document.addEventListener('turbo:render', restoreSidebarMenuScrollPosition);
document.addEventListener('turbo:render', restorePersistedSidebarMenuState);
document.addEventListener('turbo:render', scheduleRestorePersistedOptionsSidebarState);
document.addEventListener('turbo:render', announceContentNavigatorResults);
document.addEventListener('turbo:render', bindFacetPersistenceOnFilterChange);
document.addEventListener('turbo:render', bindOptionsSidebarStateTracking);
document.addEventListener('turbo:frame-load', scheduleRestorePersistedOptionsSidebarState);
document.addEventListener('turbo:before-frame-render', (event) => {
    if (!event.target || event.target.id !== 'content-navigator') {
        scheduleRestorePersistedOptionsSidebarState(event.target || document);

        return;
    }

    const currentAsideHolder = event.target.querySelector('.aside-holder');
    if (currentAsideHolder) {
        sessionStorage.setItem(CONTENT_NAVIGATOR_ASIDE_SCROLL_KEY, String(currentAsideHolder.scrollTop));
    }

    const nextFrame = event.detail && event.detail.newFrame ? event.detail.newFrame : null;
    if (!nextFrame) {
        return;
    }

    applyPersistedFacetState(nextFrame, false);
    restoreAsideScrollPosition(nextFrame);
    announceContentNavigatorResults(nextFrame);
});
document.addEventListener('turbo:submit-start', resetPopupState);
document.addEventListener('turbo:before-render', resetPopupState);
document.addEventListener('turbo:before-render', persistCurrentOptionsSidebarState);
document.addEventListener('turbo:before-render', persistSidebarMenuScrollPosition);
document.addEventListener('turbo:load', () => {
    if (window.registerIntegratedHandlebarsHelpers) {
        window.registerIntegratedHandlebarsHelpers();
    }
});
document.addEventListener('turbo:render', () => {
    if (window.registerIntegratedHandlebarsHelpers) {
        window.registerIntegratedHandlebarsHelpers();
    }
});
document.addEventListener('turbo:before-cache', () => {
    persistCurrentOptionsSidebarState();
    persistSidebarMenuScrollPosition();
    clearBoundInitializationFlags();
    stashFlashMessagesForNextVisit();

    const flashContainer = document.getElementById('flash-messages');
    if (flashContainer) {
        flashContainer.innerHTML = '';
    }
});

window.addEventListener('beforeunload', persistSidebarMenuScrollPosition);
window.addEventListener('beforeunload', persistCurrentOptionsSidebarState);
