import 'select2/dist/js/select2.full';

const MEDIA_GALLERY_NS = '.mediaGallery';

function bindMediaGalleryEvents() {
    bindViewToggles();
    bindBulkActions();
    bindUploadActions();
    bindSearchActions();
    bindMediaItemActions();
    bindEditPanelForm();
}

function bindViewToggles() {
    const doc = $(document);

    doc.off('click' + MEDIA_GALLERY_NS, '.button_enable_grid_view')
        .on('click' + MEDIA_GALLERY_NS, '.button_enable_grid_view', function() {
            enable_grid_view();
        });

    doc.off('click' + MEDIA_GALLERY_NS, '.button_enable_list_view')
        .on('click' + MEDIA_GALLERY_NS, '.button_enable_list_view', function() {
            enable_list_view();
        });
}

function bindBulkActions() {
    const doc = $(document);

    doc.off('click' + MEDIA_GALLERY_NS, '#confirm_delete')
        .on('click' + MEDIA_GALLERY_NS, '#confirm_delete', async function() {
            await confirmBulkDelete();
        });

    doc.off('click' + MEDIA_GALLERY_NS, '.single_delete')
        .on('click' + MEDIA_GALLERY_NS, '.single_delete', async function(event) {
            await singleDelete(event);
        });

    doc.off('click' + MEDIA_GALLERY_NS, '#bulkselection_delete')
        .on('click' + MEDIA_GALLERY_NS, '#bulkselection_delete', async function() {
            await askForConfirmation();
        });

    doc.off('click' + MEDIA_GALLERY_NS, '#cancel_delete')
        .on('click' + MEDIA_GALLERY_NS, '#cancel_delete', async function() {
            await hideBulkdeletionPopup();
        });

    doc.off('click' + MEDIA_GALLERY_NS, '#bulkselection')
        .on('click' + MEDIA_GALLERY_NS, '#bulkselection', async function() {
            if (bulkSelectionEnabled) {
                await disableBulkSelection();
            } else {
                await enableBulkSelection();
            }
            bulkSelectionEnabled = !bulkSelectionEnabled;
        });
}

function bindUploadActions() {
    const doc = $(document);

    doc.off('click' + MEDIA_GALLERY_NS, '.uppy-close')
        .on('click' + MEDIA_GALLERY_NS, '.uppy-close', function() {
            closeUppyWithRefresh();
        });

    doc.off('click' + MEDIA_GALLERY_NS, '.button_toggle_upload_view')
        .on('click' + MEDIA_GALLERY_NS, '.button_toggle_upload_view', function() {
            const uploadContainer = document.querySelector('#upload_container');
            if (uploadContainer) {
                uploadContainer.dataset.customContenttype = '';
            }
            toggle_upload_view();
        });

    doc.off('click' + MEDIA_GALLERY_NS, '.button_toggle_upload_view_with_contenttype')
        .on('click' + MEDIA_GALLERY_NS, '.button_toggle_upload_view_with_contenttype', function(event) {
            const uploadContainer = document.querySelector('#upload_container');
            if (uploadContainer) {
                uploadContainer.dataset.customContenttype = event.target.dataset.id;
            }
            toggle_upload_view(event.target.dataset.id);
        });
}

function bindSearchActions() {
    const doc = $(document);

    doc.off('change' + MEDIA_GALLERY_NS + ' keyup' + MEDIA_GALLERY_NS, '.input_aside_folder_search')
        .on('change' + MEDIA_GALLERY_NS + ' keyup' + MEDIA_GALLERY_NS, '.input_aside_folder_search', function() {
            asideFolderSearch(this);
        });
}

function bindMediaItemActions() {
    const doc = $(document);

    doc.off('click' + MEDIA_GALLERY_NS, '.media-edit')
        .on('click' + MEDIA_GALLERY_NS, '.media-edit', function(event) {
            handleMediaClick(event);
        });

    doc.off('click' + MEDIA_GALLERY_NS, '.media-item')
        .on('click' + MEDIA_GALLERY_NS, '.media-item', function(event) {
            if (bulkSelectionEnabled === true) {
                if ($(event.target).hasClass('media-edit')) {
                    return;
                }
                handleBulkItemClick(event);
            }
        });

    doc.off('click' + MEDIA_GALLERY_NS, '.close-media-edit-form')
        .on('click' + MEDIA_GALLERY_NS, '.close-media-edit-form', function(event) {
            handleMediaEditClose(event);
        });

    doc.off('click' + MEDIA_GALLERY_NS, '#go_to_editor')
        .on('click' + MEDIA_GALLERY_NS, '#go_to_editor', function(event) {
            event.preventDefault();

            const panel = document.querySelector('#media-edit-panel');
            const wrapper = document.querySelector('#editimagewrapper');
            if (!panel || !wrapper) {
                return;
            }

            const mediaId = panel.dataset.mediaId;
            const selectedModusRaw = panel.dataset.selectedModus;
            const selectedModus = (!selectedModusRaw || selectedModusRaw === 'undefined')
                ? 'media_gallery'
                : selectedModusRaw;
            const editImagePath = wrapper.dataset.editimagepath;
            const editImageIframePath = wrapper.dataset.editimageiframepath;

            if (!mediaId || !editImagePath || !editImageIframePath) {
                return;
            }

            if (selectedModus === 'media_gallery') {
                window.location.href = editImagePath.replace('REPLACE', mediaId);
            } else {
                window.location.href = editImageIframePath.replace('REPLACE', mediaId);
            }
        });

}

function bindEditPanelForm() {
    const doc = $(document);

    doc.off('submit' + MEDIA_GALLERY_NS, '#media-edit-panel form.content-form')
        .on('submit' + MEDIA_GALLERY_NS, '#media-edit-panel form.content-form', function(event) {
            if (!window.Turbo) {
                return;
            }

            event.preventDefault();

            const form = event.currentTarget;
            const formData = new FormData(form);

            // Ensure CSRF token is included (Turbo Frame re-render can sometimes drop it from FormData)
            if (!formData.has('integrated_content[_token]')) {
                const tokenInput = form.querySelector('input[name="integrated_content[_token]"]')
                    || document.querySelector('#media-edit-panel input[name="integrated_content[_token]"]');
                if (tokenInput && tokenInput.value) {
                    formData.append('integrated_content[_token]', tokenInput.value);
                }
            }

            // Ensure submit button name/value is included
            if (event.submitter && event.submitter.name) {
                formData.append(event.submitter.name, event.submitter.value || '');
            }

            const body = new URLSearchParams(formData);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'text/vnd.turbo-stream.html, text/html',
                },
                body: body,
            })
                .then(response => response.text())
                .then(html => {
                    window.Turbo.renderStreamMessage(html);
                });
        });
}

function closeUppyWithRefresh() {
    $('#upload_container').removeClass('show');
    $('#dropdown_overlay').addClass('hide');
    window.location.reload();
}

function bindEditPanelSaveButton() {
    const panel = document.querySelector('#media-edit-panel');
    if (!panel) {
        return;
    }

    const form = panel.querySelector('.content-form');
    const submitButton = panel.querySelector('#integrated_content_actions_save');

    if (!form || !submitButton) {
        return;
    }

    function showSubmitButton() {
        submitButton.style.display = 'block';
        form.removeEventListener('change', showSubmitButton);
        form.removeEventListener('input', showSubmitButton);
    }

    form.addEventListener('change', showSubmitButton);
    form.addEventListener('input', showSubmitButton);
}

function initEditPanelUi() {
    const panel = document.querySelector('#media-edit-panel');
    if (!panel || typeof window.jQuery === 'undefined') {
        return;
    }

    const $ = window.jQuery;
    if (!$.fn || typeof $.fn.select2 !== 'function') {
        return;
    }

    $(panel).find('select.select2, .basic-multiple').each(function() {
        const $el = $(this);
        if (!$el.hasClass('select2-hidden-accessible')) {
            if ($el.hasClass('basic-multiple')) {
                $el.select2();
            } else {
                $el.select2({
                    placeholder: $el.data('placeholder'),
                });
            }
        }
    });
}

function initRelationItemsInPanel() {
    const panel = document.querySelector('#media-edit-panel');
    if (!panel || typeof window.jQuery === 'undefined') {
        return;
    }

    const $ = window.jQuery;
    if (!$.fn || typeof $.fn.select2 !== 'function') {
        return;
    }

    $(panel).find('.relation-items').each(function() {
        const $relation = $(this);
        if ($relation.hasClass('select2-hidden-accessible')) {
            return;
        }

        const relationId = $relation.attr('id');
        let defaultValues = {};
        try {
            defaultValues = $.parseJSON(panel.querySelector('#default_references')?.value || '{}') || {};
        } catch (e) {
            defaultValues = {};
        }

        if (defaultValues[relationId]) {
            defaultValues[relationId].forEach(({id, title, image}) => {
                $relation.append(`<option selected value="${id}" data-image="${image || ''}">${title}</option>`);
            });
        }

        const updateSelected = (data) => {
            const image = data.image || $(data.element).data('image');
            return $('<div class="select2-selected">' + (image ? `<img src="${image}" />` : '') + data.text + '</div>');
        };

        $relation.select2({
            multiple: $relation.data('multiple'),
            allowClear: !$relation.data('multiple'),
            placeholder: '',
            ajax: {
                type: 'GET',
                url: $relation.data('url'),
                dataType: 'json',
                data: (param) => ({
                    relation: relationId,
                    limit: 100,
                    sort: 'title_sort',
                    q: param.term ? param.term + '*' : '',
                }),
                processResults: (data) => ({
                    results: data.items.map(item => {
                        item.text = (item.path ? item.path + ' > ' : '') + item.title;
                        return item;
                    }),
                }),
            },
            templateResult: (state) => {
                if (!state.id) return state.text;
                const image = state.image ? `<img src="${state.image}" class="select2-dropdown-image" />` : '';
                return $(`<span>${image}${state.text}</span>`);
            },
            templateSelection: (data) => data.id ? updateSelected(data) : data.text,
        }).on('change', function() {
            $(`[data-relation="${relationId}"]`).val($(this).val());
        });
    });
}

document.addEventListener('turbo:frame-load', function(event) {
    if (event.target && event.target.id === 'media-edit-panel') {
        bindEditPanelSaveButton();
        initEditPanelUi();
        initRelationItemsInPanel();
    }
});

function initMediaGallery() {
    bindEditPanelSaveButton();
    initEditPanelUi();
    initRelationItemsInPanel();
    bindMediaGalleryEvents();
}

document.addEventListener('turbo:after-stream-render', function(event) {
    if (event.detail && event.detail.newStream && event.detail.newStream.target === 'media-edit-panel') {
        initMediaGallery();
    }
});

document.addEventListener('DOMContentLoaded', initMediaGallery);
document.addEventListener('turbo:load', initMediaGallery);

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

function initializeViewPreference() {
    if (localStorage.getItem('mediagallery_view') !== null) {
        window['enable_' + localStorage.getItem('mediagallery_view') +
        '_view']();
    }
}

function handleMediaClick(event) {
    $('.media-gallery').addClass('show-edit-form');
    $('.media-edit-panel').removeClass('hide');

    const selected_modus = document.querySelector('.media-library').dataset.selectedModus
    const frame = document.querySelector('#media-edit-panel');
    const mediaId = event.target.closest('.media-item').dataset.id;
    frame.setAttribute('src', '/admin/content/' + mediaId + '?frame=1&turbo_stream=1');
    frame.setAttribute('data-selected-modus', selected_modus);
    frame.setAttribute('data-media-id', mediaId);
    frame.setAttribute('data-file', event.target.closest('.media-item').dataset.file);
}

function handleMediaEditClose(event) {
    $('.media-gallery').removeClass('show-edit-form');
    $('.media-edit-panel').addClass('hide');
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

function toggleMediaItemSelectedClass(id, selected) {
    if (!id) {
        return;
    }

    const node = document.getElementById(id);
    if (!node) {
        return;
    }

    node.classList.toggle('selected', !!selected);
}

function syncSelectionStateFromDOM() {
    const selectedNodes = Array.from(document.querySelectorAll('.media-item.selected'));
    const selectedIds = selectedNodes
        .map(node => node.getAttribute('data-id'))
        .filter(Boolean);

    if (modi[selected_modus].selectOnlyOneEnabled) {
        const selectedId = selectedIds.length > 0 ? selectedIds[selectedIds.length - 1] : null;
        selectedNodes.forEach((node) => {
            node.classList.toggle('selected', node.getAttribute('data-id') === selectedId);
        });
        bulkSelection = selectedId ? [selectedId] : [];
    } else {
        bulkSelection = selectedIds.filter(onlyUnique);
    }
}

function handleBulkItemClick(event) {
    if (modi[selected_modus].selectOnlyOneEnabled) {
        const element_id = event.currentTarget.getAttribute('data-id');
        document.querySelectorAll('.media-item.selected').forEach((node) => node.classList.remove('selected'));
        bulkSelection = [event.currentTarget.getAttribute('data-id')];
        toggleMediaItemSelectedClass(element_id, true);
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
                toggleMediaItemSelectedClass(media_id, true);
            }
        } else {
            //SINGLE CLICK
            media_id = event.currentTarget.getAttribute('data-id');
            if (bulkSelection.includes(media_id)) {
                bulkSelection = bulkSelection.filter(item => item !== media_id);
                toggleMediaItemSelectedClass(media_id, false);
            } else {
                bulkSelection.push(media_id);
                toggleMediaItemSelectedClass(media_id, true);
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

async function enableBulkSelection() {
    $('.single_delete').addClass('hidden')
    $('.bulkselectionbutton').removeClass('bulkselected')
    $('.media-container').addClass('mode-select')
}

async function disableBulkSelection() {
    bulkSelection = []
    $('.media-container').removeClass('mode-select')
    $('.single_delete').removeClass('hidden')
    $('.media-item').removeClass('selected')
    draggingAmountOfItems = 1
    document.querySelector('#bulkselection_delete').style.display = "none"
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

function setupDragAndDrop() {
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

function setupSelectionUi() {
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

function initializeMediaGalleryFeatures() {
    initializeViewPreference();
    syncSelectionStateFromDOM();
    setupDragAndDrop();
    setupSelectionUi();
}

$(function() {
    initializeMediaGalleryFeatures();
});
