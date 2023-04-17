import Uppy from '@uppy/core'
global.Uppy = Uppy

import Dashboard from '@uppy/dashboard'
global.Dashboard = Dashboard

import XHRUpload from '@uppy/xhr-upload'
global.XHRUpload = XHRUpload

import ImageEditor from '@uppy/image-editor'
global.ImageEditor = ImageEditor

// import UppyDutch from '@uppy/locales/lib/nl_NL'
// global.UppyDutch = UppyDutch

function inititalizeUppy(uppyOptions) {
    let default_height = '750px'
    let default_language = '' //defaults to eng
    let uploaded_files = 0
    if (! ("target" in uppyOptions)) {
        console.log("No target given for the Uppy component")
        return false
    }
    if (! ("endpoint" in uppyOptions)) {
        console.log("No endpoint given for the Uppy component")
        return false
    }

    let uppy = new Uppy({
        restrictions: {
            maxFileSize: 50000000, //50 MB
            allowedFileTypes: ['image/*', 'video/*', 'doc', 'docx', 'pdf', 'xls', 'xlsx'],
        },

        // locale: (uppyOptions.language === 'nl') ? UppyDutch : default_language,
        onBeforeUpload(files) {
            // We have 2 entry points: when a user selects a ContentType, and when he clicks on the add button.
            // We are setting this field via mediaGallery.js, search for: dataset.customContenttype
            let custom_contenttype = document.querySelector('#upload_container').dataset.customContenttype || ''

            let current_category_id = new URL(location.href).searchParams.get('media_taxonomy_id') || '';

            for (const [key, file] of Object.entries(files)) {
                if (null !== current_category_id && '' !== current_category_id) {
                    file.meta.category_id_target = current_category_id;
                }
                if (null !== custom_contenttype && '' !== custom_contenttype) {
                    file.meta.custom_contenttype = custom_contenttype;
                }
            }
        },
    })

    uppy.on('complete', (result) => {
        if (result.failed.length === 0) {
            closeUppyWithRefresh()
        }
    });

    uppy.use(Dashboard, {
        inline: true,
        target: uppyOptions.target,
        width: '100%',
        height: uppyOptions.height || default_height,
        proudlyDisplayPoweredByUppy: false,
        showProgressDetails: true,
        doneButtonHandler: () => {
            closeUppyWithRefresh()
        },
    });

    function closeUppyWithRefresh() {
        $('#upload_container').removeClass('show');
        $('#dropdown_overlay').addClass('hide');
        window.location.reload();
    }

    uppy.use(XHRUpload, {
        endpoint: uppyOptions.endpoint,
    });

    if (uppyOptions.imageEditorEnabled) {
        uppy.use(ImageEditor, {
            target: Dashboard,
            quality: 0.9,
        });
    }

    return uppy;
}

$('.drag-drop-area').each(function() {
    let uppyOptions = $(this)[0].dataset
    let uppy = inititalizeUppy(uppyOptions)
})
