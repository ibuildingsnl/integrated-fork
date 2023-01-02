//BULKSELECTION FUNCTIONALITY
//this controls if we show an icon with each image

// const selected_modus = 'media_gallery'

console.log("selected modus: " + selected_modus)

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

let bulkSelectionEnabled = modi[selected_modus].bulkSelectionEnabled
let showSelectButton = modi[selected_modus].showSelectButton
let bulkSelection = [] //this keeps track which items are selected
let latestBulkSelectionItemClicked = null //so we can handle a shift click with a from - to
let draggingAmountOfItems = 1

$("#bulkselection").on("click", async function () {
    if (bulkSelectionEnabled) {
        await disableBulkSelection()
    } else {
        await enableBulkSelection()
    }
    bulkSelectionEnabled = !bulkSelectionEnabled
});

async function enableBulkSelection() {
    $('.bulkselectionbutton').removeClass('bulkselected')
    $('.media-container').addClass('mode-select')

    $(".media-item").on("click", function (event) {
        handleBulkItemClick(event);
    });
}

function getAdditionalInfo(media_id) {
    return $('#' + media_id)[0]
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
        localStorage.setItem("age", 38)
        bulkSelection = [event.currentTarget.getAttribute('data-id')]
        $('#' + element_id).addClass('selected')
    } else {
    let media_id = null
        if (event.shiftKey === true) {
            //SHIFT CLICK
            if (latestBulkSelectionItemClicked === null) {
                latestBulkSelectionItemClicked = 1
            }

            let step_from = latestBulkSelectionItemClicked * 1
            step_to = event.currentTarget.getAttribute('data-media_id') * 1

            for (let step = step_from; step <= step_to; step++) {
                const element = document.querySelector('.media-item[data-media_id="' + step + '"]')

                media_id = element.getAttribute('data-id')

                bulkSelection.push(media_id)
                $('#' + element.id).addClass('selected')
            }
        } else {
            //SINGLE CLICK
            media_id = event.currentTarget.getAttribute('data-id')

            if (bulkSelection.includes(media_id)) {
                bulkSelection = bulkSelection.filter(item => item !== media_id)
                $('#' + event.currentTarget.id).removeClass('selected')
            } else {
                bulkSelection.push(media_id)
                $('#' + event.currentTarget.id).addClass('selected')
            }
        }

        latestBulkSelectionItemClicked = event.currentTarget.getAttribute('data-media_id')
    }

    console.log(bulkSelection)
    draggingAmountOfItems = bulkSelection.length
}

async function disableBulkSelection() {
    bulkSelection = []
    $('.media-container').removeClass('mode-select')
    $('.media-item').removeClass('selected')
    draggingAmountOfItems = 1
}

//DRAG AND DROP FUNCTIONALITY
(function ($) {
    $(function () {
        const $gallery = $("#gallery")
        const $media_items = $(".media_category");

        //Good example: https://www.htmlgoodies.com/css/mastering-drag-and-drop-with-jquery-ui/
        $("li", $gallery).draggable({
            helper: "clone",
            start: function (ev, ui) {
                offset = {
                    top: 50,
                    left: 50
                }
            }
        });

        // Let the media items be droppable, accepting the gallery items
        $media_items.droppable({
            activate: function (event, ui) {
                $('.ui-draggable-dragging').html('<div class="border border-black p-2 bg-white bg-red-500">' + draggingAmountOfItems + ' item(s)</div>')
            },
            accept: "#gallery > li",
            classes: {
                "ui-droppable-active": "ui-state-highlight"
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
            let media_id = $item[0].id || null

            //So here we want to send something to the server
            if (bulkSelection.length > 0) {
                let duplicatesRemoved = bulkSelection.filter(onlyUnique);
                sendAjaxRequest(category_id, duplicatesRemoved)
            } else {
                sendAjaxRequest(category_id, [media_id])
            }
            $('.media_category').removeClass('flash')
            $('#' + category_id).addClass("flash");
        }

        function get_current_category_id() {
            return new URL(location.href).searchParams.get("media_taxonomy_id") || "";
        }

        function sendAjaxRequest(category_id, media_id) {
            if (get_current_category_id() === category_id) {
                return
            }

            let jsonContent = JSON.stringify({
                csrf: document.querySelector('#media_category_csrf').value,
                category_id_target: category_id,
                media_id: media_id,
                category_id_origin: get_current_category_id(),
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
    });

    if (bulkSelectionEnabled) {
        enableBulkSelection()
    }

    if (modi[selected_modus].showBulkSelectionButton == false) {
        console.log("hide")
        $('#bulkselection').hide()
    }

    if (!showSelectButton) {
        $('#confirm_selection').hide()
    }

})(jQuery);
