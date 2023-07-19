import Uppy from '@uppy/core'
global.Uppy = Uppy

import Dashboard from '@uppy/dashboard'
global.Dashboard = Dashboard

import XHRUpload from '@uppy/xhr-upload'
global.XHRUpload = XHRUpload

import ImageEditor from '@uppy/image-editor'
global.ImageEditor = ImageEditor

/* Todo:
 When a copy is made, take the copy as base instead of send parameters
 replace urls with twig paths
 UI:
  - Fix editing of meta data and save button (of uppy/9
 */
function addShowPopupButton() {
    const statusBar = document.querySelector('#uppy-DashboardContent-panel--editor .uppy-DashboardContent-bar')
    const button = document.createElement('button');
    button.innerHTML = 'Save image 🚀';
    button.addEventListener('click', () => {
        const modal = document.getElementById("myModal");
        modal.style.display = "block";
    });

    statusBar.append(button);
}

function hideDefaultButtons() {
    document.querySelector('.uppy-DashboardContent-save').hidden = true
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
        // Todo
        window.location.href = 'https://integrated.localhost.e-active.nl/admin/media'
    }

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
        file.meta.id = uppyOptions.id;
        file.meta.user_approved_overwrite = document.querySelector('#overwriteImage').checked

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
    })

    uppy.on('file-added', (file) => {
        uppy.getPlugin('Dashboard').toggleFileCard(true, file.id)
        uppy.getPlugin('Dashboard').openFileEditor(file)
    })

    await loadCurrentFile(uppy, uppyOptions)

    hideDefaultButtons()

    addShowPopupButton()

    await loadUsedBy(uppyOptions)

    //I cant hook on the file-editor:cancel event, but this works as well:
    //Most likely this is because of an open issue: https://github.com/transloadit/uppy/issues/4045
    document.querySelectorAll('.uppy-DashboardContent-back').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelector('.uppy-Root').hidden = true
            window.location.href = previous_url
        });
    })

    return uppy;
}

async function loadUsedBy(uppyOptions) {
    try {
        const response = await fetch('https://integrated.localhost.e-active.nl/admin/content/' +uppyOptions.id+ '/used-by/json?limit=10');
        const usedBy = await response.json();
        if (usedBy?.items.length > 0) {
            applyUsedBy(usedBy)
        }
    } catch (error) {
        console.error('Error fetching data:', error);
    }
}

function applyUsedBy(usedBy) {
    document.querySelector('#used-by-list').hidden = false
    document.querySelector('#not-used-by').hidden = true

    const targetElement = document.getElementById('used-by-list'); // Replace 'target' with the ID of the element you want to append to
    usedBy?.items.forEach((item) => {
        const newLink = document.createElement('li');
        newLink.innerHTML = `<a href="${item.href}">${item.title}</a>`;
        targetElement.appendChild(newLink);
    })
}

async function loadCurrentFile(uppy, uppyOptions) {
    try {
        const blob = await urlToBlob(uppyOptions.file_url);
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
        return await response.blob();
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
