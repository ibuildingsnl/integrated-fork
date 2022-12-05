import Uppy from '@uppy/core'
global.Uppy = Uppy

import Dashboard from '@uppy/dashboard'
global.Dashboard = Dashboard

import XHRUpload from '@uppy/xhr-upload'
global.XHRUpload = XHRUpload

import ImageEditor from '@uppy/image-editor'
global.ImageEditor = ImageEditor

//BULKSELECTION FUNCTIONALITY
let bulkSelectionEnabled = false //this controls if we show an icon with each image
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

function handleBulkItemClick(event) {
    let media_id = null
    if (event.shiftKey === true) {
        //SHIFT CLICK
        if (latestBulkSelectionItemClicked === null) {
            latestBulkSelectionItemClicked = 1
        }

        let step_from = latestBulkSelectionItemClicked * 1
        let step_to = event.currentTarget.getAttribute('data-media_id') * 1

        for (let step = step_from; step <= step_to; step++) {
            let element = document.querySelector('.media-item[data-media_id="' + step + '"]')

            media_id = element.getAttribute('data-id')

            bulkSelection.push(media_id)
            $('#' + media_id).addClass('selected')
        }
    } else {
        //SINGLE CLICK
        media_id = event.currentTarget.getAttribute('data-id')
        console.log('#' + media_id)
        if (bulkSelection.includes(media_id)) {
            bulkSelection = bulkSelection.filter(item => item !== media_id)
            $('#' + media_id).removeClass('selected')
        } else {
            bulkSelection.push(media_id)
            $('#' + media_id).addClass('selected')
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

//DRAG AND DROP FUNCTIONALITY
(function ($) {
    $(function () {
        let $gallery = $("#gallery")
        let $media_items = $(".media_category");

        //Good example: https://www.htmlgoodies.com/css/mastering-drag-and-drop-with-jquery-ui/
        $('li.media-item', $gallery).draggable({
            helper: "clone",
            cursorAt: { left: 10, top: 10 },
            start: function (ev, ui) {
            }
        });

        // Let the media items be droppable, accepting the gallery items
        $media_items.droppable({
            activate: function(event, ui) {
                $('.ui-draggable-dragging').
                    html('<div class="drag-media">' + draggingAmountOfItems +
                        ' item(s)</div>');
            },
            accept: '#gallery > li.media-item',
            cursorAt: { left: 5, top: 5 },
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
            let media_id = $item[0].id || null

            //So here we want to send something to the server
            if (bulkSelection.length > 0) {
                console.log("Were bulk moving!")
                let duplicatesRemoved = bulkSelection.filter(onlyUnique);
                console.log(duplicatesRemoved)
                sendAjaxRequest(category_id, duplicatesRemoved)
            } else {
                console.log("We`re single moving:")
                sendAjaxRequest(category_id, [media_id])
                console.log(media_id)
            }
            console.log("To: " + category_id)
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

            //TODO how to get the proper path? For now I`ve added this in the twig template
            //let path = 'https://localhost.integratedfordevelopers.com/admin/media/manage_relations'
            // let path = "{{ path("integrated_content_media_manage_relations") }}'

            postData(path, jsonContent).then((data) => {
                console.log(data); // JSON data parsed by `data.json()` call
            });

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
})(jQuery);
