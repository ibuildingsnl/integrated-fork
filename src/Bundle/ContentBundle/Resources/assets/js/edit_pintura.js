import '@pqina/pintura/pintura.css';
import { openDefaultEditor } from '@pqina/pintura';
import { appendDefaultEditor } from '@pqina/pintura';

import {
    // The editor factory method
    appendEditor,

    // Import the default image reader and writer
    createDefaultImageReader,
    createDefaultImageWriter,

    // The method used to register the plugins
    setPlugins,

    // The plugins we want to use
    plugin_crop,
    plugin_finetune,
    plugin_filter,
    plugin_annotate,
} from '@pqina/pintura';

$('.drag-drop-area').each(function() {
    let options = { ...$(this)[0].dataset }
    setupPintura(options)
})

//to download the file
const downloadFile = (file) => {
    // Create a hidden link and set the URL using createObjectURL
    const link = document.createElement('a');
    link.style.display = 'none';
    link.href = URL.createObjectURL(file);
    link.download = file.name;

    // We need to add the link to the DOM for "click()" to work
    document.body.appendChild(link);
    link.click();

    // To make this work on Firefox we need to wait a short moment before clean up
    setTimeout(() => {
        URL.revokeObjectURL(link.href);
        link.parentNode.removeChild(link);
    }, 0);
};

function setupPintura(options) {
    console.log("setting plugins rawrrr")
    console.log(options)
    const pintura = appendDefaultEditor('.my-editor', {
        // The source image to load
        // src: 'https://integrated.localhost.e-active.nl/files/42391b88848896082aa56c092ab4eb38.jpg',
        src: options.imageurlexample,

        // This will set a square crop aspect ratio
        imageWriter: {
            store: {
                // Where to popst the files to
                url: '/admin/media/upload_file',

                // Which fields to post
                dataset: (state) => [
                    ['file', state.dest, state.dest.name],
                ],
            },
        },

        imageCropAspectRatio: 1,
        cropSelectPresetOptions: [
            [
                'Crop',
                [
                    [undefined, 'Custom'],
                    [1, 'Square'],
                    [4 / 3, 'Landscape'],
                    [3 / 4, 'Portrait'],
                    [12 / 2, 'Sliver'],
                ],
            ],
            [
                'Size',
                [
                    [[180, 180], 'Profile Picture'],
                    [[1200, 600], 'Header Image'],
                    [[800, 400], 'Timeline Photo'],
                ],
            ],
        ],
    });

    //This acts after the image is fully loaded
    pintura.on('load', (imageReaderResult) => {
        console.log(imageReaderResult);
    });

    //to download the file
    pintura.on('process', (imageState) => {
        downloadFile(imageState.dest);
    });

    //Image is updated
    pintura.on('update', (imageState) => {
        // console.log(imageState);
        // logs: { cropLimitToImage:…, cropMinSize:{…} , cropMaxSize:{…}, … }
    });
}

