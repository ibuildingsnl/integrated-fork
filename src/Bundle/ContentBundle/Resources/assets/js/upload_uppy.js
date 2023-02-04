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
    if (! ("target" in uppyOptions)) {
        console.log("No target given for the Uppy component")
        return false
    }
    if (! ("endpoint" in uppyOptions)) {
        console.log("No endpoint given for the Uppy component")
        return false
    }

    let uppy = new Uppy({
        // locale: (uppyOptions.language === 'nl') ? UppyDutch : default_language,
        onBeforeUpload(files) {
            let current_category_id = new URL(location.href).searchParams.get('media_taxonomy_id') || '';
            if (null !== current_category_id && '' !== current_category_id) {
                for (const [key, file] of Object.entries(files)) {
                    file.meta.category_id_target = current_category_id;
                }
            }
        },
    })

    uppy.use(Dashboard, {
        inline: true,
        target: uppyOptions.target,
        width: '100%',
        height: uppyOptions.height || default_height,
        proudlyDisplayPoweredByUppy: false,
        showProgressDetails: true,
        doneButtonHandler: () => {
            $('#upload_container').removeClass('show');
            $('#dropdown_overlay').addClass('hide');
        },
    });

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
