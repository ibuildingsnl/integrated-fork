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

export const Dialog = (editor, mode) => {
    const messageHandler = (dialog, data) => {
        if (mode === 'video') {
            videoHandler(editor, dialog, data);
        } else {
            imageHandler(editor, dialog, data);
        }
    }

    const open = () => {
        const dialog = editor.windowManager.openUrl({
            title: mode === 'image' ? 'Browse images' : 'Browse ' + mode,
            url: Options.getDialogUrl(editor, mode),
            buttons: [
              {
                type: 'cancel',
                name: 'cancel',
                text: 'Close',
                primary: true
              }
            ],
            onMessage: messageHandler,
        });
    }

    return {
        open: open
    }
}
