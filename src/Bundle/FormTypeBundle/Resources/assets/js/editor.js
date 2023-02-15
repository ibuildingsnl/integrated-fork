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
        {title: 'Preformatted (fixed font)', block: 'pre' },
        {title: 'Superscript', icon: "superscript", inline: 'sup'},
        {title: 'Subscript', icon: "subscript", inline: 'sub'}
    ];

    style_formats = style_formats.concat(element.data('format_styles'));

    tinymce.init({
        target: elem,
        theme: "silver",
        plugins:
             "advlist autolink link lists charmap anchor pagebreak " +
             "searchreplace wordcount visualchars fullscreen nonbreaking " +
             "table directionality template wordcount autoresize code " +
             "integratedbrowser"
        ,
        external_plugins: {
            // TODO: update plugin INTEGRATED-1779
            //"integratedBrowser": "/bundles/integratedformtype/js/tinymce-plugins/integrated-browser/plugin.js",
        },
        add_unload_trigger: false,
        schema: "html5",
        menubar: true,
        branding: false,
        toolbar:
            "styles | bold italic underline | bullist numlist | " +
            "link anchor integratedimage integratedvideo integratedColumn image media print preview fullpage table | " +
            "charmap pagebreak | pastetext searchreplace | code fullscreen",
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
        integrated_browser_video_dialog_url: element.data('integrated_browser_video_dialog_url'),
        document_base_url : element.data('document_base_url'),
        style_formats: style_formats
    });
});

