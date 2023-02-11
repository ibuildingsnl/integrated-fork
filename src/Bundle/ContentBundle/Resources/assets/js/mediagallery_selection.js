let form_relations = {}; //this holds all the form relation objects with an id
let form_relation = {}; //these will be placed in form_relations
let selected_relation = ''; //after a user clicks on a form button, the clicked form element is selected
const mediagallery_link = '/admin/media/';

window.onload = function() {
    addEventListeners();
    populateFormRelations();
    generateSrcAttributeForIframes();
    populateSelectedImages();
    setupFormRelations();
};

function setupFormRelations() {
    Object.values(form_relations).forEach(form_relation => {
        selected_relation = form_relation;
        rebuildDOM();
    });
}

function populateSelectedImages() {
    document.querySelectorAll('.mediagallery_selector').forEach((relation) => {
        const relationid = relation.getAttribute('id');
        const given_images = relation.querySelectorAll(
            '.previously_selected_images li');
        given_images.forEach((given_image) => {
            form_relations[relationid].selected_images.push(
                {...given_image.dataset});
        });
    });
}

function populateFormRelations() {
    document.querySelectorAll('.mediagallery_selector').forEach((item) => {
        let inputIdentifier = '';
        if (item.parentNode.classList.contains('relation')) {
            inputIdentifier = 'integrated_content[relations]'
        } else {
            inputIdentifier = 'integrated_content'
        }
        const id = item.getAttribute('id');
        form_relations[id] = {
            modus: item.querySelector(
                '.select_multimedia_button').dataset.multiple ?
                'select_multiple' :
                'select_one',
            selected_images: [],
            relationid: id,
            types: JSON.parse(
                item.querySelector('.select_multimedia_button').dataset.types),
            input_selector: 'input[name="' + inputIdentifier + '[' + id + ']"]',
            selected_images_selector: '#' + id + ' .selected_images',
            wrap_selector: '#' + id + ' .wrap',
            iframe_selector: '#' + id + ' iframe',
        };
        form_relations[id].types_url = getTypesUrl(form_relations[id].types);
    });
}

function getTypesUrl(types) {
    return types.reduce((accumulator, currentValue) => accumulator +
        'available_contenttypes[]=' + currentValue.type + '&', '');
}

function generateSrcAttributeForIframes() {
    Object.values(form_relations).forEach(form_relation => {
        const link = mediagallery_link + form_relation.modus + '?page=1&' +
            form_relation.types_url;
        document.querySelector(form_relation.iframe_selector).
            setAttribute('src', link);
    });
}

function addEventListeners() {
    document.querySelectorAll('.select_multimedia_button').forEach((item) => {
        item.addEventListener('click', function(event) {
            showMediaGallery(event, item);
        }, false);
    });
    document.querySelectorAll('.selected_images').forEach((item) => {
        item.addEventListener('click', function(event) {
            if (!event.target.classList.contains('remove')) {
                showMediaGallery(event, item);
            }
        }, false);
    });
}

window.removeImage = function(event) {
    event.preventDefault();
    const closest_image_id = event.target.closest('li').id;
    const closest_media_gallery_selector = event.target.closest(
        '.mediagallery_selector').id;
    selected_relation = form_relations[closest_media_gallery_selector];
    selected_relation.selected_images = selected_relation.selected_images.filter(
        item => item.id !== closest_image_id);
    rebuildDOM();
};

function createClone(selectedImage) {
    let clone = $('#selected_image').clone(true, true);
    let clone_img = clone.find('img');
    let remove_link = clone.find('.remove_link');
    clone.attr('id', selectedImage.id);
    clone.removeClass('hidden');
    clone_img.attr('src', selectedImage.thumbnail);
    remove_link.attr('onclick', 'removeImage(event)');
    remove_link.innerHTML = 'New Heading';
    return clone;
}

function placeClone(clone) {
    if (selected_relation.modus == 'select_one') {
        $(selected_relation.selected_images_selector).empty();
        $(selected_relation.selected_images_selector).append(clone);
    } else if (selected_relation.modus == 'select_multiple') {
        $(selected_relation.selected_images_selector).append(clone);
    }
}

function filterImages(selection) {
    const allowed_types = selected_relation.types.map(item => item.type);
    return selection.filter(item => allowed_types.includes(item.content_type));
}

function addImageIDsToInputField() {
    $(selected_relation.input_selector).
        attr('value', JSON.stringify(
            selected_relation.selected_images.map(item => item.id)).
            replace('[', '').
            replace(']', '').
            replaceAll('"', ''));
}

function emptyShownImagesInDOM() {
    $(selected_relation.selected_images_selector).empty();
}

function selectImagesToShow(response_from_iframe) {
    if (selected_relation.modus == 'select_one') {
        selected_relation.selected_images = response_from_iframe;
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
    if (e.data == 'cancel') {
        closeMediaGallery();
        return;
    }
    if (typeof e.data == 'string') {
        const response_from_iframe = filterImages(JSON.parse(e.data));
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
    $(selected_relation.wrap_selector).removeClass('show');
    $('#dropdown_overlay').addClass('hide');
}

function reloadMediaLibrary() {
    $(selected_relation.iframe_selector).attr('src', function(i, val) {
        return val;
    });
}

function showMediaGallery(event, item) {
    selected_relation = form_relations[item.dataset.relationid];
    $(selected_relation.wrap_selector).addClass('show');
    $('#dropdown_overlay').removeClass('hide');
}
