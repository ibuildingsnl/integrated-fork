const dialog = (editor, mode) =>  {
    return editor.options.get('integrated_browser_' + mode + '_dialog_url');
}

const register = (editor) => {
    editor.options.register('integrated_browser_image_dialog_url', {
        processor: 'string',
        default: ''
    });

    editor.options.register('integrated_browser_video_dialog_url', {
        processor: 'string',
        default: ''
    });
}

export {
    register,
    dialog as getDialogUrl,
}
