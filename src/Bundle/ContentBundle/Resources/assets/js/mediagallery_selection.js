let form_relations = {}; //this holds all the form relation objects with an id
const mediagallery_link = '/admin/media/';

window.addEventListener('load', function() {
    populateFormRelations();
    populateSelectedImages().then(() => {
        setupFormRelations();
        addEventListeners();
    });
});

function setupFormRelations() {
    Object.values(form_relations).forEach(form_relation => {
        selected_relation = form_relation;
        rebuildDOM();
    });
}

function populateSelectedImages() {
    return new Promise(resolve => {
        document.querySelectorAll('.mediagallery_selector').forEach((relation) => {
            const relationid = relation.getAttribute('id');
            const given_images = relation.querySelectorAll('.previously_selected_images li');
            given_images.forEach((given_image) => {
                form_relations[relationid].selected_images.push({...given_image.dataset});
            });
        });
        resolve();
    });
}

function populateFormRelations() {
    document.querySelectorAll('.mediagallery_selector').forEach((item) => {
        const id = item.getAttribute('id');
        const isRelationField = item.parentNode.classList.contains('relation');
        const multipleValue = (item.querySelector('.select_multimedia_button').dataset.multiple || '').toLowerCase();
        const isMultiple = multipleValue === 'true'
            || multipleValue === '1'
            || (multipleValue === '' && isRelationField);
        const inputSelector = isRelationField
            ? `input[name="integrated_content[relations][${id}]"]`
            : `#${id} .mediagallery_selector_input`;

        form_relations[id] = {
            modus: isMultiple ? 'select_multiple' : 'select_one',
            selected_images: [],
            relationid: id,
            types: JSON.parse(item.querySelector('.select_multimedia_button').dataset.types),
            input_selector: inputSelector,
            selected_images_selector: `#${id} .selected_images`,
            wrap_selector: `.${id}.wrap`,
            iframe_selector: `.${id}.iframe`,
        };
        form_relations[id].types_url = getTypesUrl(form_relations[id].types);

        if (!document.querySelector(`iframe.iframe.${id}`)) {
            var wrap = document.createElement('div');
            wrap.className = `wrap media-library iframe-wrapper close-outside ${id}`;

            var iframe = document.createElement('iframe');
            iframe.className = `iframe ${id}`;

            wrap.appendChild(iframe);
            document.body.appendChild(wrap);
        }

    });
}

function getTypesUrl(types) {
    return types.reduce((accumulator, currentValue) => accumulator +
        'available_contenttypes[]=' + currentValue.type + '&', '');
}

function addEventListeners() {
    const selectButtons = document.querySelectorAll('.select_multimedia_button');
    const selectedImages = document.querySelectorAll('.selected_images');

    selectButtons.forEach((selectButton) => {
        selectButton.addEventListener('click', (event) => {
            const { relationid } = event.target.dataset;
            selected_relation = form_relations[relationid];
            showMediaGallery(selected_relation);
        });
    });

    selectedImages.forEach((selectedImage) => {
        selectedImage.addEventListener('click', (event) => {
            const imageItem = event.target.closest('li');
            const removeButton = event.target.closest('.remove');

            if (imageItem && !removeButton) {
                const { relationid } = imageItem.closest('.selected_images').dataset;
                selected_relation = form_relations[relationid];
                showMediaGallery(selected_relation);
            }
        });
    });
}

window.removeImage = function(event) {
    event.preventDefault();
    const closest_image_id = event.target.closest('li').id;
    const closest_media_gallery_selector = event.target.closest('.mediagallery_selector').id;
    selected_relation = form_relations[closest_media_gallery_selector];
    selected_relation.selected_images = selected_relation.selected_images.filter(
        item => item.id !== closest_image_id);
    rebuildDOM();
};

function createClone(selectedImage) {
    const clone = document.querySelector('#selected_image').cloneNode(true);
    const clone_img = clone.querySelector('img');
    const remove_link = clone.querySelector('.remove_link');
    clone.id = selectedImage.id;
    clone.classList.remove('hidden');
    clone_img.src = selectedImage.thumbnail;
    remove_link.setAttribute('onclick', 'removeImage(event)');
    return clone;
}

function placeClone(clone) {
    const selectedImagesContainer = document.querySelector(selected_relation.selected_images_selector);
    if (selected_relation.modus === 'select_one') {
        selectedImagesContainer.innerHTML = '';
    }
    selectedImagesContainer.appendChild(clone);
}

function filterImages(selection) {
    const allowed_types = selected_relation.types
        .map(item => String(item.type || '').toLowerCase())
        .filter(Boolean);

    if (allowed_types.length === 0) {
        return selection;
    }

    return selection.filter(item => {
        const contentType = String(
            item.content_type ||
            item.contentType ||
            item.contenttype ||
            item.type ||
            ''
        ).toLowerCase();

        // Keep items without type metadata instead of silently dropping valid selections.
        if (!contentType) {
            return true;
        }

        return allowed_types.includes(contentType);
    });
}

function addImageIDsToInputField() {
    const input = document.querySelector(selected_relation.input_selector);
    if (!input) {
        return;
    }

    const selectedIds = selected_relation.selected_images.map(item => item.id).filter(Boolean);
    input.value = selected_relation.modus === 'select_one'
        ? (selectedIds[selectedIds.length - 1] || '')
        : selectedIds.join(',');
}

function emptyShownImagesInDOM() {
    document.querySelector(selected_relation.selected_images_selector).innerHTML = '';
}

function selectImagesToShow(response_from_iframe) {
    if (selected_relation.modus == 'select_one') {
        const selected = response_from_iframe.filter(item => item && item.id);
        selected_relation.selected_images = selected.length > 0 ? [selected[selected.length - 1]] : [];
    } else {
        //concat AND filter for unique values:
        selected_relation.selected_images = [
            ...selected_relation.selected_images,
            ...response_from_iframe].filter(
            (v, i, a) => a.findIndex(v2 => (v2.id === v.id)) === i);
    }
}

function populateDOMWithImages() {
    selected_relation.selected_images.forEach((item) => {
        placeClone(createClone(item));
    });
}

window.addEventListener('message', function(e) {
    if (e.data === 'cancel') {
        closeMediaGallery();
        return;
    }
    if (typeof e.data === 'string' && e.data.length > 0) {
        const parsedResponse = JSON.parse(e.data);
        const filteredResponse = filterImages(parsedResponse);
        const response_from_iframe = filteredResponse.length > 0 ? filteredResponse : parsedResponse;
        if (response_from_iframe.length > 0) {
            selectImagesToShow(response_from_iframe);
            rebuildDOM();
            closeMediaGallery();
        } else {
            console.log('no image selected');
        }
    }
});

function rebuildDOM() {
    emptyShownImagesInDOM();
    populateDOMWithImages();
    addImageIDsToInputField();
}

function closeMediaGallery() {
    reloadMediaLibrary();

    document.querySelector(selected_relation.wrap_selector).classList.remove('show');
    document.querySelector('#dropdown_overlay').classList.add('hide');
    window.popupShown = false;
}

function reloadMediaLibrary() {
    const iframe = document.querySelector(selected_relation.iframe_selector);
    iframe.src = iframe.src;
}

function showMediaGallery(selected_relation) {
    window.popupShown = true;

    const iframe = document.querySelector(selected_relation.iframe_selector);
    const selectedIds = selected_relation.selected_images.map(item => item.id).filter(Boolean).join(',');
    const selectedIdsQuery = selectedIds.length ? `&selected_ids=${encodeURIComponent(selectedIds)}` : '';
    const link = `${mediagallery_link}${selected_relation.modus}?page=1&${selected_relation.types_url}${selectedIdsQuery}`;
    iframe.setAttribute('src', link);

    document.querySelector(selected_relation.wrap_selector).classList.add('show');
    document.querySelector('#dropdown_overlay').classList.remove('hide');
}
