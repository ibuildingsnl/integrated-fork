import Uppy from '@uppy/core'
global.Uppy = Uppy

import Dashboard from '@uppy/dashboard'
global.Dashboard = Dashboard

import XHRUpload from '@uppy/xhr-upload'
global.XHRUpload = XHRUpload

import ImageEditor from '@uppy/image-editor'
global.ImageEditor = ImageEditor

function addShowPopupButton() {
    const statusBar = document.querySelector('#uppy-DashboardContent-panel--editor .uppy-DashboardContent-bar')

    let button = document.createElement('button');
    button.id = 'SaveButton';
    button.innerHTML = 'RAWR 🚀';
    button.addEventListener('click', () => {
        const modal = document.getElementById("myModal");
        modal.style.display = "block";
    });

    statusBar.append(button);
}

async function inititalizeUppy(uppyOptions) {
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
            maxFileSize: 50*1000*1000, //50 MB
            allowedFileTypes: ['image/*', 'video/*', 'doc', 'docx', 'pdf', 'xls', 'xlsx'],
        },
    })

    uppy.use(Dashboard, {
        inline: true,
        target: uppyOptions.target,
        width: '100%',
        height: uppyOptions.height || default_height,
        proudlyDisplayPoweredByUppy: false,
        showProgressDetails: false,
    });

    function closeUppyWithRefresh() {
        window.location.href = 'https://integrated.localhost.e-active.nl/admin/media'
        // window.location.reload();
    }

    uppy.on('file-editor:start', (file) => {
        console.log('fiile editor start')
    })

    uppy.on('file-editor:cancel', (file) => {
        console.log('fiile editor cancel')
    })

    uppy.use(XHRUpload, {
        endpoint: uppyOptions.endpoint,
    });

    if (uppyOptions.imageEditorEnabled) {
        uppy.use(ImageEditor, {
            target: Dashboard,
            quality: 0.9,
            autoOpenFileEditor: true,
        });
    }

    uppy.on('file-editor:complete', file => {
        console.log("file editor complete")

        file.meta.id = uppyOptions.id;
        file.meta.user_approved_overwrite = document.querySelector('#overwriteImage').checked

        const modal = document.getElementById("myModal");
        modal.style.display = "block";

        $('.content-wrapper').hide()

        uppy.upload().then((result) => {
            if (result.failed.length > 0) {
                console.error('Errors:');
                result.failed.forEach((file) => {
                    console.error(file.error);
                });
            }
            // closeUppyWithRefresh()
        });
    })

    uppy.on('file-added', (file) => {
        uppy.getPlugin('Dashboard').toggleFileCard(true, file.id)
        uppy.getPlugin('Dashboard').openFileEditor(file)
    })

    await loadCurrentFile(uppy, uppyOptions)

    addShowPopupButton()

    function uploadThisToServer() {
        const modal = document.getElementById("myModal");
        modal.style.display = "block";

        $('.content-wrapper').hide()

        uppy.upload().then((result) => {
            if (result.failed.length > 0) {
                console.error('Errors:');
                result.failed.forEach((file) => {
                    console.error(file.error);
                });
            }
            closeUppyWithRefresh()
        });
    }

    return uppy;
}

async function loadCurrentFile(uppy, uppyOptions) {
    try {
        const blob = await urlToBlob(uppyOptions.imageurlexample);
        uppy.addFile({
            name: 'my-file.jpg', // file name
            type: 'image/jpeg', // file type
            data: blob, // file blob
            meta: uppyOptions.meta,
            source: 'Local', // optional, determines the source of the file, for example, Instagram.
            isRemote: false,
        })
    } catch (error) {
        console.error('Error:', error);
    }
}

async function urlToBlob(url) {
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error('Failed to convert URL to blob');
        }
        const blob = await response.blob();
        return blob;
    } catch (error) {
        throw new Error('Failed to convert URL to blob');
    }
}

$('.drag-drop-area').each(function() {
    let uppyOptions = { ...$(this)[0].dataset }
    uppyOptions.meta = JSON.parse(uppyOptions.meta)
    let uppy = inititalizeUppy(uppyOptions)
})

document.querySelector('#saveEditedImage').onclick = function() {
    document.querySelector('.uppy-DashboardContent-save').click()
}
