import { Dialog } from './dialog';

const register = (editor) => {
    editor.ui.registry.addButton('integratedimage', {
        icon: 'image',
        tooltip: 'Add image',
        onAction: Dialog(editor, 'image').open
    });

    editor.ui.registry.addButton('integratedvideo', {
        icon: 'embed',
        tooltip: 'Add video',
        onAction: Dialog(editor, 'video').open
    });

    editor.ui.registry.addButton('integratedgallery', {
        icon: 'gallery',
        tooltip: 'Add gallery',
        onAction: Dialog(editor, 'gallery').open
    });

    editor.ui.registry.addMenuItem('integratedimage', {
        text: 'Add image',
        icon: 'image',
        onAction: Dialog(editor, 'image').open
    });

    editor.ui.registry.addMenuItem('integratedvideo', {
        text: 'Add video',
        icon: 'embed',
        onAction: Dialog(editor, 'video').open
    });

    editor.ui.registry.addMenuItem('integratedgallery', {
        icon: 'gallery',
        tooltip: 'Add gallery',
        onAction: Dialog(editor, 'gallery').open
    });
}

export {
    register
}
