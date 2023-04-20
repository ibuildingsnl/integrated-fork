import * as Options from '../api/options';
import {validate} from '../core/schema';
import * as Templates from '../core/templates';

const imageHandler = (editor, dialog, data) => {
    if (data.mceAction !== 'insertImage') {
        return;
    }

    if (!validate(data)) {
        throw 'Invalid "insertImage" message data received';
    }

    editor.insertContent(Templates.image(data.image));

    dialog.close();
};

const videoHandler = (editor, dialog, data) => {
    if (data.mceAction !== 'insertVideo') {
        return;
    }

    if (!validate(data)) {
        throw 'Invalid "insertVideo" message data received';
    }

    editor.insertContent(Templates.video(data.video));

    dialog.close();
};

const galleryHandler = (editor, dialog, data) => {
    if (data.mceAction !== 'insertGallery') {
        return;
    }

    if (!validate(data)) {
        throw 'Invalid "insertGallery" message data received';
    }

    editor.insertContent(Templates.gallery(data.images));

    function addRemoveButton(element, className) {
        const removeButton = element.querySelector(`:scope > .remove`);
        if (removeButton) {
            // If a "remove" button already exists, do nothing
            return;
        }

        const newRemoveButton = editor.contentDocument.createElement('span');
        newRemoveButton.classList.add(className, 'remove');
        newRemoveButton.innerHTML = '<svg width="24" height="24" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.75827 17.2426L12.0009 12M17.2435 6.75736L12.0009 12M12.0009 12L6.75827 6.75736M12.0009 12L17.2435 17.2426" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>';

        newRemoveButton.addEventListener('click', function() {
            element.parentNode.removeChild(element);
        });

        element.appendChild(newRemoveButton);
    }

    function addRemoveButtons() {
        const swiperSlides = editor.contentDocument.querySelectorAll('.swiper-slide');
        const articleSwiper = editor.contentDocument.querySelectorAll('.article-swiper');

        articleSwiper.forEach(function(swiper) {
            addRemoveButton(swiper, 'swiper-append');
        });

        swiperSlides.forEach(function(slide) {
            addRemoveButton(slide, 'slider-append');
        });
    }

    addRemoveButtons();

    dialog.close();
};

export const Dialog = (editor, mode) => {
    const messageHandler = (dialog, data) => {
        if (mode === 'video') {
            videoHandler(editor, dialog, data);
        } else if (mode === 'image') {
            imageHandler(editor, dialog, data);
        } else {
            galleryHandler(editor, dialog, data);
        }
    };

    const open = () => {
        const dialog = editor.windowManager.openUrl({
            title: mode === 'image' ? 'Browse images' : 'Browse ' + mode,
            url: Options.getDialogUrl(editor, mode),
            width: window.innerWidth - 60,
            height: window.innerHeight - 120,
            onMessage: messageHandler,
        });

        document.querySelector('.tox-dialog').classList.add('media_library');
        document.querySelector('.tox-dialog').focus();
    };

    return {
        open: open,
    };
};
