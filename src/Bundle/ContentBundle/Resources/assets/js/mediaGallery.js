$('.button_enable_grid_view').bind("click", function () {
    enable_grid_view()
});

$('.button_enable_list_view').bind("click", function () {
    enable_list_view()
});

$('.button_toggle_upload_view').bind("click", function () {
    toggle_upload_view()
});

$('.input_aside_folder_search').change(function () {
    asideFolderSearch(this)
}).keyup(function () {
    asideFolderSearch(this)
});

$(".media-item").on("click", function (event) {
    handleMediaClick(event)
    if (bulkSelectionEnabled === true) {
        handleBulkItemClick(event)
    }
});

$(".close-media-edit-form").on("click", function (event) {
    handleMediaEditClose(event)
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
    }
}

//BULKSELECTION FUNCTIONALITY
let bulkSelectionEnabled = modi[selected_modus].bulkSelectionEnabled
let showSelectButton = modi[selected_modus].showSelectButton
let bulkSelection = [] //this keeps track which items are selected
let latestBulkSelectionItemClicked = null //so we can handle a shift click with a from - to
let draggingAmountOfItems = 1

window.toggle_upload_view = function() {
    $('#upload_container').addClass('show').addClass('close-outside');
    $('#dropdown_overlay').removeClass('hide');
}

window.disable_x = function(view) {
    $('.toggle_view.'+view).removeClass('active');
    $('.media-item').removeClass(view);
}

window.enable_x = function(view) {
    $('.toggle_view.'+view).addClass('active');
    $('.media-item').addClass(view);
}

window.enable_grid_view = function() {
    disable_x('list')
    enable_x('grid')
}

window.enable_list_view = function() {
    disable_x('grid')
    enable_x('list')
}

$(".uppy-close").on("click", function () {
    $('#dropdown_overlay').addClass('hide');
    $('#upload_container').removeClass('show').removeClass('close-outside');
});

$("#bulkselection").on("click", async function () {
    if (bulkSelectionEnabled) {
        await disableBulkSelection()
    } else {
        await enableBulkSelection()
    }
    bulkSelectionEnabled = !bulkSelectionEnabled
});

function handleMediaClick(event) {
    $('.media-gallery').addClass('show-edit-form');
    $('.media-edit-panel').removeClass('hide');
    $('#editpaneliframe').attr('src', '/admin/content/'+event.target.closest('.media-item').dataset.id + '/iframe');
}

function handleMediaEditClose(event) {
    $('.media-gallery').removeClass('show-edit-form');
    $('.media-edit-panel').addClass('hide');
}

async function enableBulkSelection() {
    $('.bulkselectionbutton').removeClass('bulkselected')
    $('.media-container').addClass('mode-select')
}

function getAdditionalInfo(media_id) {
    return $('#' + media_id)[0]
}

window.send_cancel_to_parent = function() {
    window.parent.postMessage('cancel', '*');
}

window.send_message_to_parent = function() {
    const selection = bulkSelection.map((key) => {
        return getAdditionalInfo(key).dataset
    })

    window.parent.postMessage(JSON.stringify(selection), '*');
}

function handleBulkItemClick(event) {
    if (modi[selected_modus].selectOnlyOneEnabled) {
        const element_id = event.currentTarget.getAttribute('data-id')
        if (bulkSelection.length > 0) {
            $('#' + bulkSelection[0]).removeClass('selected')
        }
        bulkSelection = [event.currentTarget.getAttribute('data-id')]
        $('#' + element_id).addClass('selected')
    } else {
        let media_id = null
        if (event.shiftKey === true) {
            //SHIFT CLICK
            if (latestBulkSelectionItemClicked === null) {
                latestBulkSelectionItemClicked = 1
            }

            const step_from = Math.min(latestBulkSelectionItemClicked * 1, event.currentTarget.getAttribute('data-media_id') * 1)
            const step_to = Math.max(latestBulkSelectionItemClicked * 1, event.currentTarget.getAttribute('data-media_id') * 1)

            for (let step = step_from; step <= step_to; step++) {
                const element = document.querySelector('.media-item[data-media_id="' + step + '"]')

                media_id = element.getAttribute('data-id')

                bulkSelection.push(media_id)
                $('#' + media_id).addClass('selected')
            }
        } else {
            //SINGLE CLICK
            media_id = event.currentTarget.getAttribute('data-id')
            if (bulkSelection.includes(media_id)) {
                bulkSelection = bulkSelection.filter(item => item !== media_id)
                $('#' + media_id).removeClass('selected')
            } else {
                bulkSelection.push(media_id)
                $('#' + media_id).addClass('selected')
            }
        }
    }

    latestBulkSelectionItemClicked = event.currentTarget.getAttribute('data-media_id')
    draggingAmountOfItems = bulkSelection.length
}

async function disableBulkSelection() {
    bulkSelection = []
    $('.media-container').removeClass('mode-select')
    $('.media-item').removeClass('selected')
    draggingAmountOfItems = 1
}

window.asideFolderSearch = function (elem) {
    let filter, ul, li, a, i, txtValue;

    filter = elem.value.toUpperCase();
    ul = elem.parentNode.parentNode.querySelector('.aside-item-list-container > ul');
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

$(function () {
    const $gallery = $("#gallery")
    const $media_items = $(".media_category");

    //Good example: https://www.htmlgoodies.com/css/mastering-drag-and-drop-with-jquery-ui/
    $('li.media-item', $gallery).draggable({
        helper: "clone",
        cursorAt: {left: 10, top: 10},
        start: function (ev, ui) {
        }
    });

    // Let the media items be droppable, accepting the gallery items
    $media_items.droppable({
        activate: function (event, ui) {
            $('.ui-draggable-dragging').html('<div class="drag-media">' + draggingAmountOfItems +
                ' item(s)</div>');
        },
        accept: '#gallery > li.media-item',
        cursorAt: {left: 5, top: 5},
        classes: {
            'ui-droppable-active': 'ui-state-highlight',
        },
        drop: function (event, ui) {
            moveImage(ui.draggable, event, this.id);
        },
        over: function (event, ui) {

        }
    });

    function onlyUnique(value, index, self) {
        return self.indexOf(value) === index;
    }

    function moveImage($item, event, category_id = null) {
        const media_id = $item[0].id || null

        //So here we want to send something to the server
        if (bulkSelection.length > 0) {
            const duplicatesRemoved = bulkSelection.filter(onlyUnique);
            sendAjaxRequest(category_id, duplicatesRemoved)
        } else {
            sendAjaxRequest(category_id, [media_id])
        }
        $('.media_category').removeClass('flash')
        $('#' + category_id).addClass("flash");
    }

    function getCurrentCategoryID() {
        return new URL(location.href).searchParams.get("media_taxonomy_id") || "";
    }

    function sendAjaxRequest(category_id, media_id) {
        if (getCurrentCategoryID() === category_id) {
            return
        }

        const jsonContent = JSON.stringify({
            csrf: document.querySelector('#media_category_csrf').value,
            category_id_target: category_id,
            media_id: media_id,
            category_id_origin: getCurrentCategoryID(),
        })

        postData(path, jsonContent)

        async function postData(url = '', data = {}) {
            const response = await fetch(url, {
                method: 'PUT',
                mode: 'cors',
                cache: 'no-cache',
                credentials: 'same-origin',
                redirect: 'follow',
                referrerPolicy: 'no-referrer',
                body: data
            });

            return response;
        }
    }

    function setup() {
        if (bulkSelectionEnabled) {
            enableBulkSelection()
        }

        if (modi[selected_modus].showBulkSelectionButton == false) {
            $('#bulkselection').hide()
        }

        if (!showSelectButton) {
            $('#confirm_selection').hide()
        }
    }

    setup()
});
