$('.button_enable_grid_view').bind('click', function() {
    enable_grid_view();
});

$('.button_enable_list_view').bind('click', function() {
    enable_list_view();
});

$('.uppy-close').bind('click', function() {
    closeUppyWithRefresh();
});

function closeUppyWithRefresh() {
    $('#upload_container').removeClass('show');
    $('#dropdown_overlay').addClass('hide');
    window.location.reload();
}

$('.button_toggle_upload_view').bind('click', function() {
    document.querySelector('#upload_container').dataset.customContenttype = '';
    toggle_upload_view();
});

$('.button_toggle_upload_view_with_contenttype').bind('click', function(event) {
    document.querySelector(
        '#upload_container').dataset.customContenttype = event.target.dataset.id;
    toggle_upload_view(event.target.dataset.id);
});

$('.input_aside_folder_search').change(function() {
    asideFolderSearch(this);
}).keyup(function() {
    asideFolderSearch(this);
});

$('.media-edit').on('click', function(event) {
    handleMediaClick(event);
});

$('.media-item').on('click', function(event) {
    if (bulkSelectionEnabled === true) {
        if ($(event.target).hasClass('media-edit')) {
            return;
        }
        handleBulkItemClick(event);
    }
});

$('.close-media-edit-form').on('click', function(event) {
    handleMediaEditClose(event);
});

const modi = {
    'select_one': {
        bulkSelectionEnabled: true,
        showSelectButton: true,
        showBulkSelectionButton: false,
        selectOnlyOneEnabled: true,
    },
    'select_multiple': {
        bulkSelectionEnabled: true,
        showSelectButton: true,
        showBulkSelectionButton: false,
        selectOnlyOneEnabled: false,
    },
    'media_gallery': {
        bulkSelectionEnabled: false,
        showSelectButton: false,
        showBulkSelectionButton: true,
        selectOnlyOneEnabled: false,
    },
};

//BULKSELECTION FUNCTIONALITY
let bulkSelectionEnabled = modi[selected_modus].bulkSelectionEnabled;
let showSelectButton = modi[selected_modus].showSelectButton;
let bulkSelection = []; //this keeps track which items are selected
let latestBulkSelectionItemClicked = null; //so we can handle a shift click with a from - to
let draggingAmountOfItems = 1;

window.toggle_upload_view = function(customContentType) {
    $('#upload_container').addClass('show').addClass('close-outside');
    window.popupShown = true;
};

window.disable_x = function(view) {
    $('.toggle_view.' + view).removeClass('active');
    $('.media-item').removeClass(view);
};

window.enable_x = function(view) {
    $('.toggle_view.' + view).addClass('active');
    $('.media-item').addClass(view);
};

window.enable_grid_view = function() {
    disable_x('list');
    enable_x('grid');
    localStorage.setItem('mediagallery_view', 'grid');
};
window.enable_list_view = function() {
    disable_x('grid');
    enable_x('list');
    localStorage.setItem('mediagallery_view', 'list');
};

$('.uppy-close').on('click', function() {
    $('#upload_container').removeClass('show').removeClass('close-outside');
    window.popupShown = true;
});

$("#confirm_delete").on("click", async function() {
    await confirmBulkDelete()
})

$(".single_delete").on("click", async function(event) {
    await singleDelete(event)
})

$("#bulkselection_delete").on("click", async function() {
    await askForConfirmation()
})

$("#cancel_delete").on("click", async function() {
    await hideBulkdeletionPopup()
})

$("#bulkselection").on("click", async function () {
    if (bulkSelectionEnabled) {
        await disableBulkSelection();
    } else {
        await enableBulkSelection();
    }
    bulkSelectionEnabled = !bulkSelectionEnabled;
});

function handleUserChoice() {
    if (localStorage.getItem('mediagallery_view') !== null) {
        window['enable_' + localStorage.getItem('mediagallery_view') +
        '_view']();
    }
}

function handleMediaClick(event) {
    $('.media-gallery').addClass('show-edit-form');
    $('.media-edit-panel').removeClass('hide');

    // If the parent is NOT a modal, we want to load the edit image page in a new page
    // If the parent IS a modal, we want to load the edit image page without layout
    const selected_modus = document.querySelector('.media-library').dataset.selectedModus
    $('#editpaneliframe').
        attr('src',
            '/admin/content/' + event.target.closest('.media-item').dataset.id +
            '/iframe.html');

    $('#editpaneliframe').attr('data-selected-modus', selected_modus);
    $('#editpaneliframe').attr('data-media_id', event.target.closest('.media-item').dataset.id);
    $('#editpaneliframe').attr('data-file', event.target.closest('.media-item').dataset.file);
}

function handleMediaEditClose(event) {
    $('.media-gallery').removeClass('show-edit-form');
    $('.media-edit-panel').addClass('hide');
}

async function enableBulkSelection() {
    $('.single_delete').addClass('hidden')
    $('.bulkselectionbutton').removeClass('bulkselected')
    $('.media-container').addClass('mode-select')
}

function getAdditionalInfo(media_id) {
    return $('#' + media_id)[0];
}

window.send_cancel_to_parent = function() {
    window.parent.postMessage('cancel', '*');
};

window.send_message_to_parent = function() {
    const selection = bulkSelection.map((key) => {
        return getAdditionalInfo(key).dataset;
    });

    window.parent.postMessage(JSON.stringify(selection), '*');
};

window.onlyUnique = function(value, index, self) {
    return self.indexOf(value) === index;
}

function handleBulkItemClick(event) {
    if (modi[selected_modus].selectOnlyOneEnabled) {
        const element_id = event.currentTarget.getAttribute('data-id');
        if (bulkSelection.length > 0) {
            $('#' + bulkSelection[0]).removeClass('selected');
        }
        bulkSelection = [event.currentTarget.getAttribute('data-id')];
        $('#' + element_id).addClass('selected');
    } else {
        let media_id = null;
        if (event.shiftKey === true) {
            //SHIFT CLICK
            if (latestBulkSelectionItemClicked === null) {
                latestBulkSelectionItemClicked = 1;
            }

            const step_from = Math.min(latestBulkSelectionItemClicked * 1,
                event.currentTarget.getAttribute('data-media_id') * 1);
            const step_to = Math.max(latestBulkSelectionItemClicked * 1,
                event.currentTarget.getAttribute('data-media_id') * 1);

            for (let step = step_from; step <= step_to; step++) {
                const element = document.querySelector(
                    '.media-item[data-media_id="' + step + '"]');

                media_id = element.getAttribute('data-id');

                bulkSelection.push(media_id);
                $('#' + media_id).addClass('selected');
            }
        } else {
            //SINGLE CLICK
            media_id = event.currentTarget.getAttribute('data-id');
            if (bulkSelection.includes(media_id)) {
                bulkSelection = bulkSelection.filter(item => item !== media_id);
                $('#' + media_id).removeClass('selected');
            } else {
                bulkSelection.push(media_id);
                $('#' + media_id).addClass('selected');
            }
        }
    }

    if (bulkSelection.length > 0) {
        bulkSelection = bulkSelection.filter(onlyUnique);
        document.querySelector('#bulkselection_delete').style.display = ""
        document.querySelector('#amount_of_files_to_delete').textContent = '('+bulkSelection.length+')'
    } else {
        document.querySelector('#bulkselection_delete').style.display = "none"
        document.querySelector('#amount_of_files_to_delete').textContent = ''
    }

    latestBulkSelectionItemClicked = event.currentTarget.getAttribute('data-media_id')
    draggingAmountOfItems = bulkSelection.length
}

async function askForConfirmation() {
    if (bulkSelection.length === 0) {
        return
    }
    document.querySelector('#bulkdelete_confirm_popup').style.display = 'block'
    await confirmDelete(false)
}

async function singleDelete(event) {
    bulkSelection = [event.target.parentElement.dataset.id]
    await askForConfirmation()
}

async function hideBulkdeletionPopup() {
    document.querySelector('#bulkdelete_confirm_popup').style.display = 'none'
    document.querySelector('#used_images').innerHTML = ''
}

async function confirmBulkDelete() {
    await confirmDelete(true)
}

function showUsedByPopup(json_response) {
    if (json_response?.used_by?.length > 0) {
        showUsedByToUser(json_response)
    }
}

function showUsedByToUser(json_response) {
    for (let to_delete_item of json_response.used_by) {

        let new_item = document.querySelector('#used_image').cloneNode(true)
        new_item.removeAttribute('id');

        let new_p = document.createElement('p');
        new_p.textContent = to_delete_item.title + ' is used in:'
        new_p.style.marginBottom = "0px";
        new_item.classList.add('used_image_copy')
        new_item.appendChild(new_p)

        for (let used_by_item of to_delete_item.usedBy) {
            let new_div = document.createElement('div');
            let new_link = document.createElement('a');
            new_link.style.color = "rgb(1, 131, 213)"
            new_link.textContent = used_by_item.title
            new_link.href = used_by_item.link
            new_div.appendChild(new_link)
            new_item.appendChild(new_div)
        }

        document.querySelector('#used_images').appendChild(new_item);
    }
}

async function confirmDelete(confirmed_by_user) {
    const json_content = JSON.stringify({
        csrf: document.querySelector('#media_category_csrf').value,
        bulkselection: bulkSelection,
        confirmed_by_user: confirmed_by_user,
    })

    const response = await deleteData(bulkdelete_path, json_content)
    const json_response = await response.json()
    document.querySelector('#used_images').innerHTML = ''
    if (confirmed_by_user === false) {
        showUsedByPopup(json_response)
    } else {
        document.querySelector('#bulkdelete_confirm_popup').classList.add('hidden')
        window.location.reload();
    }

    async function deleteData(url = '', data = {}) {
        const response = await fetch(url, {
            method: 'PUT',
            body: data
        });

        return response;
    }
}

async function disableBulkSelection() {
    bulkSelection = []
    $('.media-container').removeClass('mode-select')
    $('.single_delete').removeClass('hidden')
    $('.media-item').removeClass('selected')
    draggingAmountOfItems = 1
    document.querySelector('#bulkselection_delete').style.display = "none"
}

window.asideFolderSearch = function(elem) {
    let filter, ul, li, a, i, txtValue;

    filter = elem.value.toUpperCase();
    ul = elem.parentNode.parentNode.querySelector(
        '.aside-item-list-container > ul');
    li = ul.getElementsByTagName('li');

    // Loop through all list items, and hide those who don't match the search query
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName('a')[0];
        txtValue = a.textContent || a.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            li[i].classList.remove('menu-item-hidden');
        } else {
            li[i].classList.add('menu-item-hidden');
        }
    }
};

$(function() {
    handleUserChoice();
    const $gallery = $('#gallery');
    const $media_items = $('.media_category');

    //Good example: https://www.htmlgoodies.com/css/mastering-drag-and-drop-with-jquery-ui/
    $('li.media-item', $gallery).draggable({
        helper: 'clone',
        distance: 20,
        cursorAt: {left: 10, top: 10},
        start: function(ev, ui) {
        },
    });

    // Let the media items be droppable, accepting the gallery items
    $media_items.droppable({
        activate: function(event, ui) {
            $('.ui-draggable-dragging').
                html('<div class="drag-media">' + draggingAmountOfItems +
                    ' item(s)</div>');
        },
        accept: '#gallery > li.media-item',
        cursorAt: {left: 5, top: 5},
        classes: {
            'ui-droppable-active': 'ui-state-highlight',
        },
        drop: function(event, ui) {
            moveImage(ui.draggable, event, this.id);
        },
        over: function(event, ui) {

        },
    });

    function onlyUnique(value, index, self) {
        return self.indexOf(value) === index;
    }

    function moveImage($item, event, category_id = null) {
        const media_id = $item[0].id || null;

        //So here we want to send something to the server
        if (bulkSelection.length > 0) {
            const duplicatesRemoved = bulkSelection.filter(onlyUnique);
            sendAjaxRequest(category_id, duplicatesRemoved)
        } else {
            sendAjaxRequest(category_id, [media_id]);
        }
        $('.media_category').removeClass('flash');
        $('#' + category_id).addClass('flash');
    }

    function getCurrentCategoryID() {
        return new URL(location.href).searchParams.get('media_taxonomy_id') ||
            '';
    }

    function sendAjaxRequest(category_id, media_id) {
        if (getCurrentCategoryID() === category_id) {
            return;
        }

        const jsonContent = JSON.stringify({
            csrf: document.querySelector('#media_category_csrf').value,
            category_id_target: category_id,
            media_id: media_id,
            category_id_origin: getCurrentCategoryID(),
        });

        postData(path, jsonContent);

        async function postData(url = '', data = {}) {
            const response = await fetch(url, {
                method: 'PUT',
                mode: 'cors',
                cache: 'no-cache',
                credentials: 'same-origin',
                redirect: 'follow',
                referrerPolicy: 'no-referrer',
                body: data,
            });

            return response;
        }
    }

    function setup() {
        if (bulkSelectionEnabled) {
            enableBulkSelection();
        }

        if (modi[selected_modus].showBulkSelectionButton == false) {
            $('#bulkselection').hide();
        }

        if (!showSelectButton) {
            $('#confirm_selection').hide();
        }
    }

    setup();
});
