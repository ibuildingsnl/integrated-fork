$(document).mouseup(function(e) {
    let closeOutside = $('.close-outside');
    let toggleButton = $('.toggle-button');

    if (((!closeOutside.is(e.target) && closeOutside.has(e.target).length ===
                0) &&
            (!toggleButton.is(e.target) && toggleButton.has(e.target).length ===
                0)) &&
        !event.srcElement.classList.contains('tt-input') &&
        !event.srcElement.classList.contains('dropdown-menu')
    ) {
        closeOutside.removeClass('show');
        hideDropDownBackGround();
    }
    sendCancelEvent()
});

$(document).keyup(function(e) {
    if (e.key === 'Escape') { // escape key maps to keycode `27`
        let closeOutside = $('.close-outside');
        if (closeOutside.is('#upload_container')) {
            closeOutside.removeClass('close-outside');
        }
        closeOutside.removeClass('show');
        hideDropDownBackGround();
    }
    sendCancelEvent()
});

function sendCancelEvent() {
    document.dispatchEvent(new CustomEvent("cancelEvent", { "detail": "Example of an event" }));
}

$('.search-form .form-control').focus(function() {
    showDropDownBackGround();
});

$('.search-form .form-control').blur(function() {
    hideDropDownBackGround();
});

document.querySelectorAll('a[data-toggle="dropdown"]').forEach(function(el) {
    el.addEventListener('click', toggleDropdown, false);
});

document.querySelectorAll('.toggle-data-target').forEach(function(el) {
    el.addEventListener('click', toggleDataTarget, false);
});

document.querySelectorAll('.menu-label').forEach(function(el) {
    el.addEventListener('click', toggleSidebarElement, false);
});

document.querySelectorAll('.toggle-options-sidebar').forEach(function(el) {
    el.addEventListener('click', toggleOptionsSidebar, false);
});

document.querySelectorAll('.list-search').forEach(function(el) {
    el.addEventListener('change', asideItemsSearch, false);
    el.addEventListener('keyup', asideItemsSearch, false);
});

document.addEventListener('DOMContentLoaded', hideButtonIfNoOptions);

function showElement(el) {
    const getHeight = function() {
        el.style.display = 'block';
        var height = el.scrollHeight + 'px';
        el.style.display = '';
        return height;
    };

    const height = getHeight();
    el.parentNode.classList.add('show');
    el.style.height = height;

    window.setTimeout(function() {
        el.style.height = '';
    }, 350);
}

function hideElement(el) {
    el.style.height = el.scrollHeight + 'px';

    window.setTimeout(function() {
        el.style.height = '0';
    }, 1);

    window.setTimeout(function() {
        el.classList.remove('show');
        el.parentNode.classList.remove('show');
    }, 350);

}

function toggleElement(el) {
    if (el.parentNode.classList.contains('show') ||
        el.classList.contains('show')) {
        hideElement(el);
        return;
    }
    showElement(el);
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
    if ((viewportWidth < 1025 && !optionsMenu.classList.contains('show')) ||
        document.body.classList.contains('hide-options')) {
        document.body.classList.add('show-options');
        optionsMenu.classList.add('show');

        document.body.classList.remove('hide-options');
        optionsMenu.classList.remove('hide');
    } else {
        document.body.classList.remove('show-options');
        optionsMenu.classList.remove('show');

        document.body.classList.add('hide-options');
        optionsMenu.classList.add('hide');
    }
}

function toggleDataTarget(el) {
    const targetElement = document.querySelector(
        '.' + el.target.getAttribute('data-target'));
    if (!targetElement.classList.contains('show')) {
        targetElement.classList.add('show');
        if (el.target.getAttribute('data-underlay') !== 'no') {
            showDropDownBackGround();
        }
    } else {
        targetElement.classList.remove('show');
        if (el.target.getAttribute('data-underlay') !== 'no') {
            hideDropDownBackGround();
        }
    }
}

function toggleDropdown(el) {
    const dropdown = el.target.closest('.dropdown');
    if (!dropdown.classList.contains('show')) {
        const dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('show');
        });
        dropdown.classList.add('show');
        if (dropdown.getAttribute('data-underlay') !== 'no') {
            showDropDownBackGround();
        }
    } else {
        dropdown.classList.remove('show');
        if (dropdown.getAttribute('data-underlay') !== 'no') {
            hideDropDownBackGround();
        }
    }
}

function toggleOptionsElement(el) {
    let options = el.parentNode.querySelector('.aside-item-list');
    if (!options) {
        options = el.parentNode.querySelector('.editor-item-list');
    }

    if (!options) return;

    toggleElement(options);
}

function toggleSidebarElement(el) {
    const menu = this.parentNode.querySelector('.sub-menu-children');

    el.preventDefault();

    if (!menu) return;

    toggleElement(menu);
}

function asideItemsSearch(el) {
    let input, filter, ul, li, a, i, txtValue;
    input = el.target;
    filter = input.value.toUpperCase();
    ul = el.target.parentNode.parentNode.querySelector(
        '.aside-item-list .aside-item-list-container ul');
    li = ul.getElementsByTagName('li');

    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName('label')[0];
        txtValue = a.textContent || a.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = '';
        } else {
            li[i].style.display = 'none';
        }
    }
}

function hideDropDownBackGround() {
    const menuItemDropDownUnderlay = document.querySelector(
        '#dropdown_overlay');
    if (!menuItemDropDownUnderlay.classList.contains('hide')) {
        menuItemDropDownUnderlay.classList.add('hide');
    }
}

function showDropDownBackGround() {
    const menuItemDropDownUnderlay = document.querySelector(
        '#dropdown_overlay');
    if (menuItemDropDownUnderlay.classList.contains('hide')) {
        menuItemDropDownUnderlay.classList.remove('hide');
    }
}

function isElement(o) {
    return (
        typeof HTMLElement === 'object' ? o instanceof HTMLElement : //DOM2
            o && typeof o === 'object' && o !== null && o.nodeType === 1 &&
            typeof o.nodeName === 'string'
    );
}

if (document.querySelector('.aside-holder') !== null) {
    document.querySelector('.aside-holder').
        addEventListener('click', function(event) {
            if (event.target.classList.contains('aside-item-header')) {
                toggleOptionsElement(event.target);
            }
        });
}
if (document.querySelector('section.editor') !== null) {
    document.querySelector('section.editor').
        addEventListener('click', function(event) {
            if (event.target.classList.contains('editor-item-header')) {
                toggleOptionsElement(event.target);
            }
        });
}

$(document).ready(function() {
    if ($("body[class$='content_index']")) {
        var filterElements = document.getElementsByClassName('aside-item-list');

        for (var ii = 0; ii < filterElements.length; ii++) {
            openSelectedOptions(filterElements[ii]);
        }

        function openSelectedOptions(elem) {
            var textinputs = elem.querySelectorAll('input[type=checkbox]');
            if (!elem.parentNode.classList.contains('show')) {
                var empty = [].filter.call(textinputs, function(elem) {
                    return !elem.checked;
                });
                if (textinputs.length != empty.length) {
                    showElement(elem);
                }
            }
        }
    }
});
