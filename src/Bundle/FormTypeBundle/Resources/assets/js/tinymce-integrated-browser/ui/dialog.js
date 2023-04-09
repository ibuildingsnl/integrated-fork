import * as Options from '../api/options';
import { validate } from '../core/schema';
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
}

const videoHandler = (editor, dialog, data) => {
    if (data.mceAction !== 'insertVideo') {
        return;
    }

    if (!validate(data)) {
        throw 'Invalid "insertVideo" message data received';
    }

    editor.insertContent(Templates.video(data.video));

    dialog.close();
}

const galleryHandler = (editor, dialog, data) => {
    if (data.mceAction !== 'insertGallery') {
        return;
    }

    if (!validate(data)) {
        throw 'Invalid "insertGallery" message data received';
    }

    editor.insertContent(Templates.gallery(data.images));

    dialog.close();
}

export const Dialog = (editor, mode) => {
    const messageHandler = (dialog, data) => {
        if (mode === 'video') {
            videoHandler(editor, dialog, data);
        } else if (mode === 'image') {
            imageHandler(editor, dialog, data);
        } else {
            galleryHandler(editor, dialog, data);
        }
    }

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
    }

    return {
        open: open
    }
}
