import { Dialog } from './dialog';

const register = (editor) => {
    const openImageDialog = Dialog(editor, 'image', {editExisting: false}).open;
    const openImageEditDialog = Dialog(editor, 'image', {editExisting: true}).open;
    const openVideoDialog = Dialog(editor, 'video').open;
    const openGalleryDialog = Dialog(editor, 'gallery', {editExisting: false}).open;
    const openGalleryEditDialog = Dialog(editor, 'gallery', {editExisting: true}).open;

    editor.addCommand('integratedEditImageDialog', openImageEditDialog);
    editor.addCommand('integratedOpenGalleryDialog', openGalleryDialog);
    editor.addCommand('integratedEditGalleryDialog', openGalleryEditDialog);

    editor.ui.registry.addButton('integratedimage', {
        icon: 'image',
        tooltip: 'Add image',
        onAction: openImageDialog
    });

    editor.ui.registry.addButton('integratedvideo', {
        icon: 'embed',
        tooltip: 'Add video',
        onAction: openVideoDialog
    });

    editor.ui.registry.addButton('integratedgallery', {
        icon: 'gallery',
        tooltip: 'Add gallery',
        onAction: openGalleryDialog
    });

    editor.ui.registry.addMenuItem('integratedimage', {
        text: 'Add image',
        icon: 'image',
        onAction: openImageDialog
    });

    editor.ui.registry.addMenuItem('integratedvideo', {
        text: 'Add video',
        icon: 'embed',
        onAction: openVideoDialog
    });

    editor.ui.registry.addMenuItem('integratedgallery', {
        icon: 'gallery',
        tooltip: 'Add gallery',
        onAction: openGalleryDialog
    });
}

export {
    register
}
