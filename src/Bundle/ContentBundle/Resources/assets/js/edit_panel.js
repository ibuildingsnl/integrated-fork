document.addEventListener("DOMContentLoaded", function(event){
    window.document.addEventListener('editImageClick', handleEvent, false)

    const edit_image_path = document.querySelector('#editimagewrapper').dataset.editimagepath
    const edit_image_iframe_path = document.querySelector('#editimagewrapper').dataset.editimageiframepath

    function handleEvent(e) {
        const media_id = document.querySelector('#media-edit-panel').dataset.mediaId

        // If selected modus == media gallery, we should go to the page with a redirect,
        // If selected_modus is something else (select_multiple, select_one) then this is loaded via an iframe
        // And then we load the same page without sidebar and header

        if (selected_modus == 'media_gallery') {
            window.location.href = edit_image_path.replace('REPLACE', media_id)
        } else {
            window.location.href = edit_image_iframe_path.replace('REPLACE', media_id)
        }
    }
});
