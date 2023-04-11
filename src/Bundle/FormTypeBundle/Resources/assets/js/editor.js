import tinymce from 'tinymce';

import 'tinymce/themes/silver';

import 'tinymce/models/dom';

import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/anchor';
import 'tinymce/plugins/pagebreak';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/wordcount';
import 'tinymce/plugins/visualchars';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/nonbreaking';
import 'tinymce/plugins/table';
import 'tinymce/plugins/directionality';
import 'tinymce/plugins/template';
import 'tinymce/plugins/wordcount';
import 'tinymce/plugins/autoresize';
import 'tinymce/plugins/code';

import './tinymce-integrated-browser/plugin';

$('.integrated_tinymce').each(function(key, elem){
    const element = $(elem);

    let style_formats = [
        {title: 'Paragraph', format: 'p'},
        {title: 'Heading 2', block: 'h2' },
        {title: 'Heading 3', block: 'h3' },
        {title: 'Heading 4', block: 'h4' },
        {title: 'Heading 5', block: 'h5' },
    ];

    style_formats = style_formats.concat(element.data('format_styles'));

    tinymce.init({
        target: elem,
        theme: "silver",
        plugins:
             "advlist autolink link lists charmap anchor pagebreak " +
             "searchreplace wordcount visualchars fullscreen nonbreaking " +
             "table directionality wordcount autoresize code " +
             "integratedbrowser"
        ,
        add_unload_trigger: false,
        schema: "html5",
        menubar: 'edit view insert format tools table',
        branding: false,
        toolbar:
            "styles | bold italic underline subscript superscript | bullist numlist | " +
            "link anchor table charmap | integratedimage integratedgallery integratedvideo image media | print | " +
            "pastetext searchreplace | fullscreen",
        toolbar_sticky: false,
        toolbar_location: 'top',
        statusbar: true,
        statusbar_size: "small",
        fixed_toolbar_container: '.tox-editor-header',
        width: "100%",
        height: "100%",
        browser_spellcheck : true,
        autoresize_bottom_margin: "0px",
        convert_urls: false,
        content_css: element.data('content_css'),
        integrated_browser_image_dialog_url: element.data('integrated_browser_image_dialog_url'),
        integrated_browser_gallery_dialog_url: element.data('integrated_browser_gallery_dialog_url'),
        integrated_browser_video_dialog_url: element.data('integrated_browser_video_dialog_url'),
        document_base_url : element.data('document_base_url'),
        style_formats: style_formats,
        setup: function (editor) {
            function addRemoveButton(element, className) {
                const removeButton = editor.contentDocument.createElement('span');
                removeButton.classList.add(className, 'remove');
                removeButton.innerHTML = '<svg width="24" height="24" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.75827 17.2426L12.0009 12M17.2435 6.75736L12.0009 12M12.0009 12L6.75827 6.75736M12.0009 12L17.2435 17.2426" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>';

                element.appendChild(removeButton);
            }

            function initRemoveButtons() {
                const removeButtons = editor.contentDocument.querySelectorAll('.remove');

                removeButtons.forEach(function(removeButton) {
                    removeButton.addEventListener('click', function() {
                        const element = this.parentNode;
                        element.parentNode.removeChild(element);
                    });
                });
            }

            editor.on('init', function() {
                const swiperSlides = editor.contentDocument.querySelectorAll('.swiper-slide');
                const articleSwiper = editor.contentDocument.querySelectorAll('.article-swiper');

                articleSwiper.forEach(function(swiper) {
                    addRemoveButton(swiper, 'swiper-append');
                });

                swiperSlides.forEach(function(slide) {
                    addRemoveButton(slide, 'slider-append');
                });

                initRemoveButtons();
            });

            editor.on('change', function() {
                initRemoveButtons();
            });
        }
    });
});

$(window).keyup(function(e) {
    if (e.key === "Escape") {
        $('.tox-tinymce-aux').empty()
    }
});


