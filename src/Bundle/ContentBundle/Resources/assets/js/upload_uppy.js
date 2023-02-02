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

async function inititalizeUppy(uppyOptions) {
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

    async function loadXHR(url) {
        let test = await new Promise(function(resolve, reject) {
            try {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", url);
                xhr.responseType = "blob";
                xhr.onerror = function() {reject("Network error.")};
                xhr.onload = function() {
                    if (xhr.status === 200) {resolve(xhr.response)}
                    else {reject("Loading error:" + xhr.statusText)}
                };
                xhr.send();
            }
            catch(err) {reject(err.message)}
        });

        return test
    }

    console.log(uppyOptions)
    if (uppyOptions.mode = "edit_images") {

        uppy.addFile({
            name: 'my-file.jpg', // file name
            type: 'image/jpeg', // file type
            data: blob, // file blob
            meta: {
                // optional, store the directory path of a file so Uppy can tell identical files in different directories apart.
                // relativePath: webkitFileSystemEntry.relativePath,
            },
            source: 'Local', // optional, determines the source of the file, for example, Instagram.
            isRemote: false, // optional, set to true if actual file is not in the browser, but on some remote server, for example,
            // when using companion in combination with Instagram.
        })
        console.log(uppyOptions.endpoint)
        console.log(uppyOptions.imageSource)
    }

    uppy.on('file-editor:start', (file) => {
        console.log("fe start")
        console.log(file)
    })

    uppy.on('file-editor:complete', (updatedFile) => {
        console.log("fe complete")
        console.log(updatedFile)
    })

    uppy.on('file-editor:cancel', (file) => {
        console.log("fe cancel")
        console.log(file)
    })

    return uppy;
}

$('.drag-drop-area').each(function() {
    let uppyOptions = $(this)[0].dataset
    let uppy = inititalizeUppy(uppyOptions)
})
