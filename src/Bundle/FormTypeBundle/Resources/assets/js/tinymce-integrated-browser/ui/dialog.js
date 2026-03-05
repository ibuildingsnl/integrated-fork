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

const getSelectionArticleSwiper = (editor) => {
    if (!editor.selection || typeof editor.selection.getNode !== 'function') {
        return null;
    }

    const node = editor.selection.getNode();
    if (!node) {
        return null;
    }

    if (node.classList && node.classList.contains('article-swiper')) {
        return node;
    }

    if (typeof node.closest === 'function') {
        return node.closest('.article-swiper');
    }

    return null;
};

const findEditableImage = (node) => {
    const isStandaloneEditableImage = (image) => {
        if (!image || !image.hasAttribute('data-integrated-id')) {
            return false;
        }

        if (typeof image.closest === 'function' && image.closest('.article-swiper')) {
            return false;
        }

        return true;
    };

    if (!node) {
        return null;
    }

    if (node.tagName === 'IMG' && isStandaloneEditableImage(node)) {
        return node;
    }

    if (typeof node.querySelector === 'function') {
        const candidateImages = node.querySelectorAll('img[data-integrated-id]');
        for (let i = 0; i < candidateImages.length; i += 1) {
            if (isStandaloneEditableImage(candidateImages[i])) {
                return candidateImages[i];
            }
        }
    }

    return null;
};

const getSelectionImage = (editor) => {
    if (!editor.selection || typeof editor.selection.getNode !== 'function') {
        return null;
    }

    return findEditableImage(editor.selection.getNode());
};

const getStoredImage = (editor) => {
    const target = editor && editor.__integratedLastEditableImage
        ? editor.__integratedLastEditableImage
        : null;

    if (target && target.isConnected) {
        return target;
    }

    return null;
};

const resolveImageTarget = (editor, candidate = null) => {
    if (candidate && candidate.isConnected) {
        return candidate;
    }

    return getSelectionImage(editor) || getStoredImage(editor);
};

const getStoredArticleSwiper = (editor) => {
    const target = editor && editor.__integratedLastArticleSwiper
        ? editor.__integratedLastArticleSwiper
        : null;

    if (target && target.isConnected) {
        return target;
    }

    return null;
};

const getSingleArticleSwiper = (editor) => {
    if (!editor || !editor.contentDocument) {
        return null;
    }

    const swipers = editor.contentDocument.querySelectorAll('.article-swiper');
    return swipers.length === 1 ? swipers[0] : null;
};

const resolveGalleryTarget = (editor, candidate = null) => {
    if (candidate && candidate.isConnected) {
        return candidate;
    }

    return getSelectionArticleSwiper(editor)
        || getStoredArticleSwiper(editor)
        || getSingleArticleSwiper(editor);
};

const buildDialogUrl = (editor, mode, galleryTarget = null, imageTarget = null) => {
    const baseUrl = Options.getDialogUrl(editor, mode);
    let selectedIds = [];

    if (mode === 'gallery' && galleryTarget) {
        selectedIds = Array.from(galleryTarget.querySelectorAll('img[data-integrated-id]'))
            .map((image) => String(image.getAttribute('data-integrated-id') || '').trim())
            .filter((id) => id !== '');
    } else if (mode === 'image' && imageTarget) {
        const imageId = String(imageTarget.getAttribute('data-integrated-id') || '').trim();
        selectedIds = imageId ? [imageId] : [];
    }

    if (selectedIds.length === 0) {
        return baseUrl;
    }

    const targetUrl = new URL(baseUrl, window.location.origin);
    targetUrl.searchParams.set('selected_ids', selectedIds.join(','));

    return targetUrl.pathname + targetUrl.search + targetUrl.hash;
};

const imageHandler = (editor, dialog, data, selectionImageTarget = null, allowEditExisting = true) => {
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

    const targetImage = selectionImageTarget && selectionImageTarget.isConnected
        ? selectionImageTarget
        : (allowEditExisting ? resolveImageTarget(editor) : null);

    if (targetImage) {
        targetImage.setAttribute('src', data.image.uri);
        targetImage.setAttribute('title', data.image.title || '');
        targetImage.setAttribute('alt', data.image.title || '');

        if (data.image.id) {
            targetImage.setAttribute('data-integrated-id', data.image.id);
        } else {
            targetImage.removeAttribute('data-integrated-id');
        }

        editor.selection.select(targetImage);
        editor.__integratedLastEditableImage = targetImage;
    } else {
        editor.insertContent(Templates.image(data.image));
    }

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

const galleryHandler = (editor, dialog, data, selectionGalleryTarget = null, allowEditExisting = false) => {
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
    const selectedSwiper = selectionGalleryTarget && selectionGalleryTarget.isConnected
        ? selectionGalleryTarget
        : (allowEditExisting ? resolveGalleryTarget(editor) : null);

    function ensureRemoveButton(element, className, onRemove) {
        let button = element.querySelector(':scope > .remove');
        if (!button) {
            button = editor.contentDocument.createElement('span');
            button.classList.add(className, 'remove');
            button.innerHTML = '<svg width="24" height="24" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.75827 17.2426L12.0009 12M17.2435 6.75736L12.0009 12M12.0009 12L6.75827 6.75736M12.0009 12L17.2435 17.2426" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            element.appendChild(button);
        }

        button.setAttribute('data-mce-bogus', 'all');
        button.setAttribute('contenteditable', 'false');
        button.setAttribute('tabindex', '0');
        button.setAttribute('role', 'button');
        button.setAttribute('aria-label', className === 'swiper-append' ? 'Remove slider' : 'Remove slide');

        if (button.dataset.integratedRemoveBound === '1') {
            return;
        }

        const suppressMouseDown = function(event) {
            event.preventDefault();
            event.stopPropagation();
        };

        const handleClick = function(event) {
            event.preventDefault();
            event.stopPropagation();
            onRemove();
        };

        const handleKeyDown = function(event) {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            onRemove();
        };

        button.addEventListener('mousedown', suppressMouseDown);
        button.addEventListener('click', handleClick);
        button.addEventListener('keydown', handleKeyDown);
        button.dataset.integratedRemoveBound = '1';
    }

    function getAdjacentSlide(slide, direction) {
        let candidate = direction < 0 ? slide.previousElementSibling : slide.nextElementSibling;

        while (candidate && (!candidate.classList || !candidate.classList.contains('swiper-slide'))) {
            candidate = direction < 0 ? candidate.previousElementSibling : candidate.nextElementSibling;
        }

        return candidate;
    }

    function moveSlide(slide, direction) {
        const wrapper = slide ? slide.parentElement : null;
        if (!wrapper || !wrapper.classList || !wrapper.classList.contains('swiper-wrapper')) {
            return false;
        }

        if (direction < 0) {
            const previousSlide = getAdjacentSlide(slide, -1);
            if (!previousSlide) {
                return false;
            }

            wrapper.insertBefore(slide, previousSlide);
            return true;
        }

        const nextSlide = getAdjacentSlide(slide, 1);
        if (!nextSlide) {
            return false;
        }

        wrapper.insertBefore(nextSlide, slide);
        return true;
    }

    function updateSlideMoveButtons(wrapper) {
        const slides = Array.from(wrapper.querySelectorAll(':scope > .swiper-slide'));
        const lastIndex = slides.length - 1;

        slides.forEach(function(slide, index) {
            const previousButton = slide.querySelector(':scope > .slider-append.move-prev');
            const nextButton = slide.querySelector(':scope > .slider-append.move-next');

            if (previousButton) {
                const disabled = index === 0;
                previousButton.classList.toggle('is-disabled', disabled);
                previousButton.setAttribute('aria-disabled', disabled ? 'true' : 'false');
            }

            if (nextButton) {
                const disabled = index === lastIndex;
                nextButton.classList.toggle('is-disabled', disabled);
                nextButton.setAttribute('aria-disabled', disabled ? 'true' : 'false');
            }
        });
    }

    function ensureSlideMoveButton(slide, direction) {
        const directionClass = direction < 0 ? 'move-prev' : 'move-next';
        const buttonTitle = direction < 0 ? 'Move slide left' : 'Move slide right';
        const boundKey = direction < 0 ? 'integratedMovePrevBound' : 'integratedMoveNextBound';
        let button = slide.querySelector(':scope > .slider-append.' + directionClass);
        if (!button) {
            button = editor.contentDocument.createElement('span');
            button.classList.add('slider-append', 'move', directionClass);
            button.innerHTML = direction < 0
                ? '<svg width="24" height="24" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 6L8 12L14 18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                : '<svg width="24" height="24" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 6L16 12L10 18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            slide.appendChild(button);
        }

        button.setAttribute('data-mce-bogus', 'all');
        button.setAttribute('contenteditable', 'false');
        button.setAttribute('tabindex', '0');
        button.setAttribute('role', 'button');
        button.setAttribute('aria-label', buttonTitle);
        button.setAttribute('title', buttonTitle);

        if (button.dataset[boundKey] === '1') {
            return;
        }

        const suppressMouseDown = function(event) {
            event.preventDefault();
            event.stopPropagation();
        };

        const onMove = function(event) {
            event.preventDefault();
            event.stopPropagation();

            if (button.getAttribute('aria-disabled') === 'true') {
                return;
            }

            if (moveSlide(slide, direction)) {
                updateSlideMoveButtons(slide.parentElement);
                button.focus();
            }
        };

        const handleKeyDown = function(event) {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            onMove(event);
        };

        button.addEventListener('mousedown', suppressMouseDown);
        button.addEventListener('click', onMove);
        button.addEventListener('keydown', handleKeyDown);
        button.dataset[boundKey] = '1';
    }

    function normalizeArticleSwiper(swiper) {
        const wrapper = swiper.querySelector(':scope > .swiper-wrapper');
        if (!wrapper) {
            swiper.remove();
            return;
        }

        wrapper.querySelectorAll(':scope > .swiper-slide').forEach(function(slide) {
            slide.querySelectorAll(':scope > .remove').forEach(function(button) {
                button.remove();
            });

            slide.querySelectorAll(':scope > br[data-mce-bogus]').forEach(function(node) {
                node.remove();
            });

            const image = slide.querySelector('img.template-image-gallery[src], img[data-integrated-id], img[src]');
            if (!image) {
                slide.remove();
                return;
            }

            ensureRemoveButton(slide, 'slider-append', function() {
                slide.remove();

                if (!wrapper.querySelector(':scope > .swiper-slide')) {
                    swiper.remove();
                }
            });
            ensureSlideMoveButton(slide, -1);
            ensureSlideMoveButton(slide, 1);
        });

        if (!wrapper.querySelector(':scope > .swiper-slide')) {
            swiper.remove();
            return;
        }

        updateSlideMoveButtons(wrapper);
        ensureRemoveButton(swiper, 'swiper-append', function() {
            swiper.remove();
        });

        ['.swiper-button-next', '.swiper-button-prev'].forEach(function(selector) {
            const navigation = swiper.querySelector(':scope > ' + selector);
            if (!navigation) {
                return;
            }

            navigation.setAttribute('contenteditable', 'false');
            navigation.querySelectorAll('br[data-mce-bogus]').forEach(function(node) {
                node.remove();
            });
        });
    }

    if (selectedSwiper) {
        let wrapper = selectedSwiper.querySelector(':scope > .swiper-wrapper');
        if (!wrapper) {
            wrapper = editor.contentDocument.createElement('div');
            wrapper.classList.add('swiper-wrapper');
            selectedSwiper.appendChild(wrapper);
        }

        wrapper.innerHTML = '';

        images.forEach(function(image) {
            const slide = editor.contentDocument.createElement('div');
            slide.classList.add('swiper-slide');

            const slideImage = editor.contentDocument.createElement('img');
            slideImage.classList.add('img-responsive', 'template-image-gallery');
            slideImage.setAttribute('src', image.thumbnail || image.uri);
            slideImage.setAttribute('title', image.title || '');
            slideImage.setAttribute('alt', image.title || '');

            if (image.id) {
                slideImage.setAttribute('data-integrated-id', image.id);
            }

            slide.appendChild(slideImage);
            wrapper.appendChild(slide);
        });

        if (!selectedSwiper.querySelector(':scope > .swiper-button-next')) {
            const nextButton = editor.contentDocument.createElement('div');
            nextButton.classList.add('swiper-button-next');
            nextButton.setAttribute('contenteditable', 'false');
            selectedSwiper.appendChild(nextButton);
        }

        if (!selectedSwiper.querySelector(':scope > .swiper-button-prev')) {
            const prevButton = editor.contentDocument.createElement('div');
            prevButton.classList.add('swiper-button-prev');
            prevButton.setAttribute('contenteditable', 'false');
            selectedSwiper.appendChild(prevButton);
        }

        normalizeArticleSwiper(selectedSwiper);
    } else {
        const caretMarkerId = 'integrated-gallery-caret-' + String(Date.now());
        editor.insertContent(
            Templates.gallery(images) + '<p><br></p><p id="' + caretMarkerId + '"><br data-mce-bogus="1"></p>'
        );

        const insertedCaretMarker = editor.contentDocument.getElementById(caretMarkerId);
        if (insertedCaretMarker) {
            editor.selection.setCursorLocation(insertedCaretMarker, 0);
            insertedCaretMarker.removeAttribute('id');
        }
    }

    editor.contentDocument.querySelectorAll('.article-swiper').forEach(function(swiper) {
        normalizeArticleSwiper(swiper);
    });

    dialog.close();
};

export const Dialog = (editor, mode, config = {}) => {
    const editExistingImage = mode === 'image' && config.editExisting !== false;
    const editExistingGallery = mode === 'gallery' && config.editExisting === true;
    let pendingImageTarget = null;
    let pendingGalleryTarget = null;

    const messageHandler = (dialog, data) => {
        const message = normalizeLegacyMessage(mode, data);
        if (!message) {
            return;
        }

        if (message.mceAction === 'close') {
            pendingImageTarget = null;
            pendingGalleryTarget = null;
            dialog.close();
            return;
        }

        try {
            if (mode === 'video') {
                videoHandler(editor, dialog, message);
            } else if (mode === 'image') {
                imageHandler(editor, dialog, message, pendingImageTarget, editExistingImage);
            } else {
                galleryHandler(editor, dialog, message, pendingGalleryTarget, editExistingGallery);
            }
        } catch (e) {
            console.error(e);
        } finally {
            if (mode === 'image') {
                pendingImageTarget = null;
            }

            if (mode === 'gallery') {
                pendingGalleryTarget = null;
            }
        }
    };

    const open = () => {
        if (mode === 'image') {
            pendingImageTarget = editExistingImage ? resolveImageTarget(editor) : null;
        } else {
            pendingImageTarget = null;
        }

        if (mode === 'gallery') {
            pendingGalleryTarget = editExistingGallery ? resolveGalleryTarget(editor) : null;
        } else {
            pendingGalleryTarget = null;
        }

        const isEditingImage = mode === 'image' && !!pendingImageTarget;
        const isEditingGallery = mode === 'gallery' && editExistingGallery && !!pendingGalleryTarget;
        const title = mode === 'image'
            ? (isEditingImage ? 'Edit image' : 'Browse images')
            : (isEditingGallery ? 'Edit gallery' : 'Browse ' + mode);
        const url = buildDialogUrl(
            editor,
            mode,
            isEditingGallery ? pendingGalleryTarget : null,
            isEditingImage ? pendingImageTarget : null
        );

        const dialog = editor.windowManager.openUrl({
            title: title,
            url: url,
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
