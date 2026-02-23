import * as Options from '../api/options';
import {validate} from '../core/schema';
import * as Templates from '../core/templates';

const toStringValue = (value) => {
    if (value === null || value === undefined) {
        return '';
    }

    return String(value);
};

const normalizeLegacyMessage = (mode, data) => {
    if (!data) {
        return null;
    }

    if (typeof data === 'object' && !Array.isArray(data) && data.mceAction) {
        return data;
    }

    if (data === 'cancel') {
        return {mceAction: 'close'};
    }

    let payload = data;
    if (typeof payload === 'string') {
        try {
            payload = JSON.parse(payload);
        } catch (e) {
            return null;
        }
    }

    if (!Array.isArray(payload) || payload.length === 0) {
        return null;
    }

    if (mode === 'image') {
        const selected = payload[0];
        return {
            mceAction: 'insertImage',
            image: {
                id: toStringValue(selected.id),
                uri: toStringValue(selected.file),
                title: toStringValue(selected.title),
            },
        };
    }

    if (mode === 'video') {
        const selected = payload[0];
        return {
            mceAction: 'insertVideo',
            video: {
                id: toStringValue(selected.id),
                poster: toStringValue(selected.thumbnail),
                uri: toStringValue(selected.file),
                mine: toStringValue(selected.mime),
            },
        };
    }

    return {
        mceAction: 'insertGallery',
        images: payload.map((selected) => ({
            id: toStringValue(selected.id),
            uri: toStringValue(selected.file),
            thumbnail: toStringValue(selected.thumbnail),
            title: toStringValue(selected.title),
        })),
    };
};

const bindBackdropClose = (dialog) => {
    let attempts = 0;
    const maxAttempts = 20;

    const attach = () => {
        const backdrop = document.querySelector('.tox-dialog-wrap__backdrop');
        if (backdrop) {
            backdrop.addEventListener('click', () => dialog.close(), {once: true});
            return;
        }

        attempts += 1;
        if (attempts < maxAttempts) {
            window.setTimeout(attach, 50);
        }
    };

    attach();
};

const imageHandler = (editor, dialog, data) => {
    if (data.mceAction !== 'insertImage') {
        return;
    }

    if (!validate(data)) {
        throw 'Invalid "insertImage" message data received';
    }

    if (!data.image.uri) {
        editor.notificationManager.open({
            text: 'Selected image has no source URL',
            type: 'error',
        });
        return;
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

    if (!data.video.uri) {
        editor.notificationManager.open({
            text: 'Selected video has no source URL',
            type: 'error',
        });
        return;
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

    const images = data.images.filter((image) => !!image.uri);
    if (images.length === 0) {
        editor.notificationManager.open({
            text: 'Selected gallery items have no source URL',
            type: 'error',
        });
        return;
    }

    editor.insertContent(Templates.gallery(images));

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
        const message = normalizeLegacyMessage(mode, data);
        if (!message) {
            return;
        }

        if (message.mceAction === 'close') {
            dialog.close();
            return;
        }

        try {
            if (mode === 'video') {
                videoHandler(editor, dialog, message);
            } else if (mode === 'image') {
                imageHandler(editor, dialog, message);
            } else {
                galleryHandler(editor, dialog, message);
            }
        } catch (e) {
            console.error(e);
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

        const toxDialog = document.querySelector('.tox-dialog');
        if (toxDialog) {
            toxDialog.classList.add('media_library');
            toxDialog.focus();
        }

        bindBackdropClose(dialog);
    };

    return {
        open: open,
    };
};
