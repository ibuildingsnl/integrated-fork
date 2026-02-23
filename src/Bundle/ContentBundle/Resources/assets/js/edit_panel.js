function initEditPanel() {
    const wrapper = document.querySelector('#editimagewrapper');
    const panel = document.querySelector('#media-edit-panel');
    if (!wrapper || !panel) {
        return;
    }

    if (window.document.body.dataset.boundEditImageClick === 'true') {
        return;
    }

    window.document.addEventListener('editImageClick', handleEvent, false);
    window.document.body.dataset.boundEditImageClick = 'true';

    function handleEvent() {
        const mediaId = panel.dataset.mediaId;
        const editImagePath = wrapper.dataset.editimagepath;
        const editImageIframePath = wrapper.dataset.editimageiframepath;
        if (!mediaId || !editImagePath || !editImageIframePath) {
            return;
        }

        // If selected modus == media gallery, we should go to the page with a redirect,
        // If selected_modus is something else (select_multiple, select_one) then this is loaded via an iframe
        // And then we load the same page without sidebar and header
        const selectedModus = (typeof selected_modus !== 'undefined' && selected_modus && selected_modus !== 'undefined')
            ? selected_modus
            : 'media_gallery';

        if (selectedModus == 'media_gallery') {
            navigateTo(editImagePath.replace('REPLACE', mediaId));
        } else {
            navigateTo(editImageIframePath.replace('REPLACE', mediaId));
        }
    }
}

function navigateTo(url) {
    if (window.Turbo && typeof window.Turbo.visit === 'function') {
        window.Turbo.visit(url);
        return;
    }

    window.location.href = url;
}

document.addEventListener('DOMContentLoaded', initEditPanel);
document.addEventListener('turbo:load', initEditPanel);
document.addEventListener('turbo:render', initEditPanel);
