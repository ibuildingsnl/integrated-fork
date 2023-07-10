import Uppy from '@uppy/core'
global.Uppy = Uppy

import Dashboard from '@uppy/dashboard'
global.Dashboard = Dashboard

import XHRUpload from '@uppy/xhr-upload'
global.XHRUpload = XHRUpload

import ImageEditor from '@uppy/image-editor'
global.ImageEditor = ImageEditor

async function urlToBlob(url) {
    try {
        const response = await fetch(url);
        console.log(response)
        if (!response.ok) {
            throw new Error('Failed to convert URL to blob');
        }
        console.log(response)
        const blob = await response.blob();
        console.log(blob)
        return blob;
    } catch (error) {
        throw new Error('Failed to convert URL to blob');
    }
}

async function inititalizeUppy(uppyOptions) {
    console.log(uppyOptions)
    console.log(uppyOptions.imageurlexample)

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

    console.log("hi there")

    let uppy = new Uppy({
        restrictions: {
            maxFileSize: 50*1000*1000, //50 MB
            allowedFileTypes: ['image/*', 'video/*', 'doc', 'docx', 'pdf', 'xls', 'xlsx'],
        },

        // locale: (uppyOptions.language === 'nl') ? UppyDutch : default_language,
        onBeforeUpload(files) {
            console.log("before upload")
            // We have 2 entry points: when a user selects a ContentType, and when he clicks on the add button.
            // We are setting this field via mediaGallery.js, search for: dataset.customContenttype
            // let custom_contenttype = document.querySelector('#upload_container').dataset.customContenttype || ''

            // let current_category_id = new URL(location.href).searchParams.get('media_taxonomy_id') || '';

            // for (const [key, file] of Object.entries(files)) {
            //     if (null !== current_category_id && '' !== current_category_id) {
            //         file.meta.category_id_target = current_category_id;
            //     }
            //     if (null !== custom_contenttype && '' !== custom_contenttype) {
            //         file.meta.custom_contenttype = custom_contenttype;
            //     }
            // }
        },
    })

    uppy.on('complete', (result) => {
        if (result.failed.length === 0) {
            // closeUppyWithRefresh()
        }
    });

    uppy.use(Dashboard, {
        inline: true,
        target: uppyOptions.target,
        width: '100%',
        height: uppyOptions.height || default_height,
        proudlyDisplayPoweredByUppy: false,
        showProgressDetails: false,
        // doneButtonHandler: () => {
        //     closeUppyWithRefresh()
        // },
    });

    function closeUppyWithRefresh() {
        $('#upload_container').removeClass('show');
        $('#dropdown_overlay').addClass('hide');
        window.location.reload();
    }

    uppy.on('file-editor:start', (file) => {
        console.log("file editor start")
        console.log(file)
        //cleanup
        const items = document.querySelectorAll('.uppy-DashboardContent-back')
        console.log(items)
        items.forEach(item => console.log(item))
        items.forEach(item => item.style.display = "none")
    })

    uppy.on('file-editor:cancel', (file) => {
        console.log('fiile editor cancel')
        console.log(fiile)
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
        console.log(file)
        console.log("upload to server")
        // let result = uppy.upload()
        // console.log(result)

        file.replace = true
        file.meta.replace = true;
        file.user_approved_overwrite = true
        file.meta.user_approved_overwrite = true;

        uppy.upload().then((result) => {
            console.info('uploads:', result);
            console.info('Successful uploads:', result.successful);

            if (result.failed.length > 0) {
                console.error('Errors:');
                result.failed.forEach((file) => {
                    console.error(file.error);
                });
            }
        });
        // if (hasNewFiles) {
        //     const dashboard = uppy.getPlugin('Dashboard')
        //     const files = uppy.getFiles()
        //     const currentFilePosition = files.findIndex(f => f.id == file.id)
        //     const nextFile = getNextFile(dashboard.canEditFile, files, currentFilePosition)
        //     if (!nextFile) {
        //         hasNewFiles = false
        //     } else {
        //         dashboard.openFileEditor(nextFile)
        //     }
        // }
    })

    function uploadToServer() {
        console.log("upload to server")
        let result = uppy.upload()
        console.log(result)

        uppy.upload().then((result) => {
            console.info('Successful uploads:', result.successful);

            if (result.failed.length > 0) {
                console.error('Errors:');
                result.failed.forEach((file) => {
                    console.error(file.error);
                });
            }
        });
    }

    // uppy.on('files-added', async () => {
    //     hasNewFiles = true // This could be a react component's state for example
    //     const files = uppy.getFiles()
    //     console.log(files)
    //     const dashboard = uppy.getPlugin('Dashboard')
    //
    //     let imageBoxes = document.querySelectorAll('.uppy-Dashboard-Item')
    //
    //     const elm = await waitForElm('.uppy-Dashboard-Item');
    //
    //     //add custom button
    //     for (let imageBox of imageBoxes) {
    //         var myString = '<button onclick="editFileWithImageEditor(\''+imageBox.id+'\')">EDIT</button>';
    //         var $jQueryObject = $($.parseHTML(myString))
    //
    //         let child = imageBox.querySelector('.uppy-Dashboard-Item-fileInfoAndButtons')
    //         child.append($jQueryObject[0])
    //     }
    // })

    uppy.on('file-added', (file) => {
        uppy.getPlugin('Dashboard').toggleFileCard(true, file.id)
        uppy.getPlugin('Dashboard').openFileEditor(file)
    })

    console.log("whhut")
    console.log(uppyOptions.imageurlexample)
    try {
        const blob = await urlToBlob(uppyOptions.imageurlexample);
        console.log("blob")
        console.log(blob)
        uppy.addFile({
            name: 'my-file.jpg', // file name
            type: 'image/jpeg', // file type
            data: blob, // file blob
            meta: {
                // optional, store the directory path of a file so Uppy can tell identical files in different directories apart.
                // relativePath: webkitFileSystemEntry.relativePath,
            },
            source: 'Local', // optional, determines the source of the file, for example, Instagram.
            isRemote: false,
        })
    } catch (error) {
        console.error('Error:', error);
    }

    return uppy;
}

$('.drag-drop-area').each(function() {
    let uppyOptions = $(this)[0].dataset
    let uppy = inititalizeUppy(uppyOptions)
})
