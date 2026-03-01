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

function isValidURL(str) {
    var a  = document.createElement('a');
    a.href = str;
    console.log(a.href);
    console.log(a.host);
    console.log(window.location.host);

    return (a.host && a.host != window.location.host);
}

function initTinyMceEditors(root = document) {
    $('.integrated_tinymce', root).each(function(key, elem){
        const element = $(elem);
        let existingEditor = elem.id ? tinymce.get(elem.id) : null;

        if (existingEditor) {
            const targetElement = existingEditor.targetElm || null;
            if (targetElement && targetElement !== elem) {
                existingEditor.remove();
                existingEditor = null;
            } else if (targetElement && !targetElement.isConnected) {
                existingEditor.remove();
                existingEditor = null;
            }
        }

        const hasEditorInstance = Boolean(existingEditor || (elem.id && tinymce.get(elem.id)));

        if (element.data('tinymce-initialized') && !hasEditorInstance) {
            element.removeData('tinymce-initialized');
        }

        if (element.data('tinymce-initialized')) {
            return;
        }

        if (hasEditorInstance) {
            element.data('tinymce-initialized', true);
            return;
        }


    let style_formats = [
        {title: 'Paragraph', format: 'p'},
        {title: 'Heading 2', block: 'h2' },
        {title: 'Heading 3', block: 'h3' },
        {title: 'Heading 4', block: 'h4' },
        {title: 'Heading 5', block: 'h5' },
        {title: 'Blockquote', format: 'blockquote'},
        {title: 'Cite', format: 'cite'},
        {title: 'Superscript', icon: 'superscript', inline: 'sup'},
        {title: 'Subscript', icon: 'subscript', inline: 'sub'},
    ];

    let custom_styles = element.data('format_styles') || [];

    custom_styles = custom_styles.map(style => {
        const newStyle = {...style};

        for (const property in newStyle) {
            if (newStyle[property] === 'true') {
                newStyle[property] = true;
            } else if (newStyle[property] === 'false') {
                newStyle[property] = false;
            }
        }

        return newStyle;
    });

    style_formats = style_formats.concat(custom_styles);

    tinymce.init({
        target: elem,
        theme: "silver",
        plugins:
             "advlist autolink link lists charmap anchor pagebreak " +
             "searchreplace wordcount visualchars fullscreen nonbreaking " +
             "table directionality wordcount autoresize code " +
             "integratedbrowser articlelinksearch"
        ,
        add_unload_trigger: false,
        schema: "html5",
        menubar: 'edit view insert format tools table',
        branding: false,
        toolbar:
            "styles | bold italic underline subscript superscript | bullist numlist | alignleft aligncenter alignright alignjustify | " +
            "integratedArticleLinkSearch anchor table charmap | integratedimage integratedgallery integratedvideo image media | print | " +
            "pastetext searchreplace | code fullscreen ",
        contextmenu: 'integratedArticleLinkSearch',
        formats: {
            alignleft: {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'align-left'},
            aligncenter: {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'align-center'},
            alignright: {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'align-right'},
            alignjustify: {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'align-justify'},
            cite: {block: 'cite'}
        },
        toolbar_sticky: false,
        toolbar_location: 'top',
        statusbar: true,
        statusbar_size: "small",
        fixed_toolbar_container: '.tox-editor-header',
        width: "100%",
        height: "100%",
        browser_spellcheck : true,
        autoresize_bottom_margin: 0,
        convert_urls: false,
        content_css: element.data('content_css'),
        integrated_browser_image_dialog_url: element.data('integrated_browser_image_dialog_url'),
        integrated_browser_gallery_dialog_url: element.data('integrated_browser_gallery_dialog_url'),
        integrated_browser_video_dialog_url: element.data('integrated_browser_video_dialog_url'),
        document_base_url : element.data('document_base_url'),
        style_formats: style_formats,
        noneditable_class: 'embed-content',

        paste_preprocess: function(plugin, args) {

            var tempDiv = document.createElement('div');
            tempDiv.innerHTML = args.content;

            // Remove elements with inline styles, classes, or lang attributes
            tempDiv.querySelectorAll('[style], [class], [lang]').forEach(el => el.removeAttribute('style') || el.removeAttribute('class') || el.removeAttribute('lang'));

            // Replace spans with their content (unwrap)
            tempDiv.querySelectorAll('span').forEach(el => {
                var parent = el.parentNode;
                while (el.firstChild) parent.insertBefore(el.firstChild, el);
                parent.removeChild(el);
            });

            args.content = tempDiv.innerHTML;

            var cleanContent = args.content.replace(/<meta[^>]*>/g, ''); // Remove <meta> tags
            cleanContent = cleanContent.replace(/<span[^>]*>(.*?)<\/span>/g, '$1'); // Unwrap <span> tags
            args.content = cleanContent;

            let input = args.content.trim();

            if (!isValidURL(input)) return;

            $.ajax({
                url: '/admin/_oembed/fetch-data',
                dataType: 'json',
                type: 'get',
                async: false,
                data: {
                    url: input
                },
                success: function (data, textStatus, jqXHR) {
                    if (!data.code) return;

                    var parser = new DOMParser();
                    var doc = parser.parseFromString(data.code, 'text/html');
                    var iframe = doc.querySelector('iframe');

                    if (iframe && data.provider_name === 'YouTube') {
                        iframe.width = '100%';
                        iframe.height = '432px';
                        iframe.classList.add('video');
                        iframe.classList.add('youtube');

                        var src = iframe.src.replace('youtube.com', 'youtube-nocookie.com');
                        var srcUrl = new URL(src);
                        srcUrl.searchParams.set('controls', '0');
                        iframe.src = srcUrl.toString();
                    }

                    var serializer = new XMLSerializer();
                    var modifiedCode = serializer.serializeToString(doc);

                    args.content = '<div class="embed-content ' + data.provider_name + '">' + modifiedCode + '</div><br>';
                },
                error: function (jqXHR, textStatus, errorThrown) {
                },
                complete: function (jqXHR, textStatus) {
                }
            });
        },
        setup: function (editor) {
            function isStandaloneEditableImage(image) {
                if (!image || image.tagName !== 'IMG' || !image.hasAttribute('data-integrated-id')) {
                    return false;
                }

                if (typeof image.closest === 'function' && image.closest('.article-swiper')) {
                    return false;
                }

                return true;
            }

            function isImageOnlyHost(host, image) {
                if (!host || !image) {
                    return false;
                }

                let imageCount = 0;
                const children = host.childNodes || [];
                for (let i = 0; i < children.length; i += 1) {
                    const child = children[i];

                    if (child.nodeType === Node.TEXT_NODE) {
                        if (String(child.textContent || '').trim() !== '') {
                            return false;
                        }

                        continue;
                    }

                    if (child.nodeType !== Node.ELEMENT_NODE) {
                        continue;
                    }

                    if (child === image) {
                        imageCount += 1;
                        continue;
                    }

                    if (child.classList && child.classList.contains('integrated-image-edit')) {
                        continue;
                    }

                    if (child.tagName === 'BR') {
                        continue;
                    }

                    return false;
                }

                return imageCount === 1;
            }

            function rememberArticleSwiper(node) {
                if (!node || typeof node.closest !== 'function') {
                    return;
                }

                const swiper = node.closest('.article-swiper');
                if (swiper && swiper.isConnected) {
                    editor.__integratedLastArticleSwiper = swiper;
                }
            }

            function rememberEditableImage(node) {
                if (!node) {
                    return;
                }

                let image = null;
                if (isStandaloneEditableImage(node)) {
                    image = node;
                } else if (typeof node.querySelector === 'function') {
                    const candidates = node.querySelectorAll('img[data-integrated-id]');
                    for (let i = 0; i < candidates.length; i += 1) {
                        if (isStandaloneEditableImage(candidates[i])) {
                            image = candidates[i];
                            break;
                        }
                    }
                }

                if (image && image.isConnected) {
                    editor.__integratedLastEditableImage = image;
                }
            }

            function ensureImageEditButton(image) {
                if (!isStandaloneEditableImage(image) || !image.parentElement) {
                    return;
                }

                const host = image.parentElement;
                host.classList.add('integrated-image-edit-host');
                if (isImageOnlyHost(host, image)) {
                    host.classList.add('integrated-image-edit-host-inline');
                } else {
                    host.classList.remove('integrated-image-edit-host-inline');
                }

                let button = host.querySelector(':scope > .integrated-image-edit');
                if (!button) {
                    button = editor.contentDocument.createElement('span');
                    button.classList.add('integrated-image-edit');
                    button.innerHTML = '<svg width="24" height="24" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.3638 5.65165L18.3483 9.63617M3 21H7.40614L19.0454 9.36073C20.3182 8.08792 20.3182 6.02476 19.0454 4.75195C17.7726 3.47914 15.7094 3.47914 14.4366 4.75195L3 16.1886V21Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                    host.appendChild(button);
                }

                button.setAttribute('data-mce-bogus', 'all');
                button.setAttribute('contenteditable', 'false');
                button.setAttribute('tabindex', '0');
                button.setAttribute('role', 'button');
                button.setAttribute('aria-label', 'Edit image');
                button.setAttribute('title', 'Edit image');

                if (button.dataset.integratedImageEditBound === '1') {
                    return;
                }

                const suppressMouseDown = function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                };

                const openImageEditor = function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    editor.__integratedLastEditableImage = image;
                    editor.selection.select(image);
                    editor.execCommand('integratedEditImageDialog');
                };

                const handleKeyDown = function(event) {
                    if (event.key !== 'Enter' && event.key !== ' ') {
                        return;
                    }

                    openImageEditor(event);
                };

                button.addEventListener('mousedown', suppressMouseDown);
                button.addEventListener('click', openImageEditor);
                button.addEventListener('keydown', handleKeyDown);
                button.dataset.integratedImageEditBound = '1';
            }

            function ensureRemoveButton(element, className, onRemove) {
                let button = element.querySelector(':scope > .remove');
                if (!button) {
                    button = editor.contentDocument.createElement('span');
                    button.classList.add(className, 'remove');
                    button.innerHTML = '<svg width="24" height="24" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.75827 17.2426L12.0009 12M17.2435 6.75736L12.0009 12M12.0009 12L6.75827 6.75736M12.0009 12L17.2435 17.2426" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                    element.appendChild(button);
                }

                button.setAttribute('data-mce-bogus', 'all');
                button.setAttribute('contenteditable', 'false');
                button.setAttribute('tabindex', '0');
                button.setAttribute('role', 'button');
                button.setAttribute('aria-label', className === 'swiper-append' ? 'Remove slider' : 'Remove slide');

                if (button.dataset.integratedRemoveBound === '1') {
                    return;
                }

                const suppressMouseDown = function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                };

                const handleClick = function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    onRemove();
                };

                const handleKeyDown = function(event) {
                    if (event.key !== 'Enter' && event.key !== ' ') {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    onRemove();
                };

                button.addEventListener('mousedown', suppressMouseDown);
                button.addEventListener('click', handleClick);
                button.addEventListener('keydown', handleKeyDown);
                button.dataset.integratedRemoveBound = '1';
            }

            function ensureEditButton(swiper) {
                let button = swiper.querySelector(':scope > .integrated-swiper-edit');
                if (!button) {
                    button = editor.contentDocument.createElement('span');
                    button.classList.add('swiper-append', 'edit', 'integrated-swiper-edit');
                    button.innerHTML = '<svg width="24" height="24" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.3638 5.65165L18.3483 9.63617M3 21H7.40614L19.0454 9.36073C20.3182 8.08792 20.3182 6.02476 19.0454 4.75195C17.7726 3.47914 15.7094 3.47914 14.4366 4.75195L3 16.1886V21Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                    swiper.appendChild(button);
                }

                button.setAttribute('data-mce-bogus', 'all');
                button.setAttribute('contenteditable', 'false');
                button.setAttribute('tabindex', '0');
                button.setAttribute('role', 'button');
                button.setAttribute('aria-label', 'Edit gallery');
                button.setAttribute('title', 'Edit gallery');

                if (button.dataset.integratedEditBound === '1') {
                    return;
                }

                const suppressMouseDown = function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                };

                const openGalleryEditor = function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    editor.__integratedLastArticleSwiper = swiper;
                    editor.selection.select(swiper);
                    editor.execCommand('integratedEditGalleryDialog');
                };

                const handleKeyDown = function(event) {
                    if (event.key !== 'Enter' && event.key !== ' ') {
                        return;
                    }

                    openGalleryEditor(event);
                };

                button.addEventListener('mousedown', suppressMouseDown);
                button.addEventListener('click', openGalleryEditor);
                button.addEventListener('keydown', handleKeyDown);
                button.dataset.integratedEditBound = '1';
            }

            function normalizeArticleSwiper(swiper) {
                const wrapper = swiper.querySelector(':scope > .swiper-wrapper');
                if (!wrapper) {
                    swiper.remove();
                    return;
                }

                wrapper.querySelectorAll(':scope > .swiper-slide').forEach(function(slide) {
                    slide.querySelectorAll(':scope > .remove').forEach(function(button) {
                        button.remove();
                    });

                    slide.querySelectorAll(':scope > br[data-mce-bogus]').forEach(function(node) {
                        node.remove();
                    });

                    const image = slide.querySelector('img.template-image-gallery[src], img[data-integrated-id], img[src]');
                    if (!image) {
                        slide.remove();
                        return;
                    }

                    ensureRemoveButton(slide, 'slider-append', function() {
                        slide.remove();

                        if (!wrapper.querySelector(':scope > .swiper-slide')) {
                            swiper.remove();
                        }
                    });
                });

                if (!wrapper.querySelector(':scope > .swiper-slide')) {
                    swiper.remove();
                    return;
                }

                ensureRemoveButton(swiper, 'swiper-append', function() {
                    swiper.remove();
                });
                ensureEditButton(swiper);

                ['.swiper-button-next', '.swiper-button-prev'].forEach(function(selector) {
                    const navigation = swiper.querySelector(':scope > ' + selector);
                    if (!navigation) {
                        return;
                    }

                    navigation.setAttribute('contenteditable', 'false');
                    navigation.querySelectorAll('br[data-mce-bogus]').forEach(function(node) {
                        node.remove();
                    });
                });
            }

            function normalizeAllArticleSwipers() {
                editor.contentDocument.querySelectorAll('.article-swiper').forEach(function(swiper) {
                    normalizeArticleSwiper(swiper);
                });
            }

            function normalizeAllEditableImages() {
                const hosts = editor.contentDocument.querySelectorAll('.integrated-image-edit-host');
                hosts.forEach(function(host) {
                    const image = host.querySelector(':scope > img[data-integrated-id]');
                    if (!isStandaloneEditableImage(image)) {
                        const staleButton = host.querySelector(':scope > .integrated-image-edit');
                        if (staleButton) {
                            staleButton.remove();
                        }

                        host.classList.remove('integrated-image-edit-host', 'integrated-image-edit-host-inline');
                    }
                });

                editor.contentDocument.querySelectorAll('img[data-integrated-id]').forEach(function(image) {
                    ensureImageEditButton(image);
                });
            }

            editor.on('init', function() {

                let event = new CustomEvent('tinyMCEInitialized', { detail: { editor } });
                window.dispatchEvent(event);
                normalizeAllArticleSwipers();
                normalizeAllEditableImages();
                const currentNode = editor.selection && editor.selection.getNode ? editor.selection.getNode() : null;
                rememberArticleSwiper(currentNode);
                rememberEditableImage(currentNode);
            });

            editor.on('change', function() {
                normalizeAllArticleSwipers();
                normalizeAllEditableImages();
            });

            editor.on('click', function(event) {
                const target = event && event.target ? event.target : null;
                rememberArticleSwiper(target);
                rememberEditableImage(target);
            });

            editor.on('NodeChange', function(event) {
                const element = event && event.element ? event.element : null;
                rememberArticleSwiper(element);
                rememberEditableImage(element);
            });

            editor.on('keyup', function() {
                const currentNode = editor.selection && editor.selection.getNode ? editor.selection.getNode() : null;
                rememberArticleSwiper(currentNode);
                rememberEditableImage(currentNode);
            });
        }
    });

        element.data('tinymce-initialized', true);
    });
}

$(window).keyup(function(e) {
    if (e.key === "Escape") {
        $('.tox-tinymce-aux').empty()
    }
});

function destroyTinyMceEditors() {
    if (typeof tinymce !== 'undefined') {
        tinymce.remove();
    }

    $('.integrated_tinymce').each(function(key, elem) {
        $(elem).removeData('tinymce-initialized');
    });
}

function destroyTinyMceEditorsWithin(root = document) {
    if (typeof tinymce === 'undefined') {
        return;
    }

    $('.integrated_tinymce', root).each(function(key, elem) {
        if (elem.id) {
            const editor = tinymce.get(elem.id);
            if (editor) {
                editor.remove();
            }
        }

        $(elem).removeData('tinymce-initialized');
    });

    if (Array.isArray(tinymce.editors)) {
        tinymce.editors.slice().forEach((editor) => {
            if (!editor || !editor.targetElm) {
                return;
            }

            if (!editor.targetElm.isConnected) {
                editor.remove();
            }
        });
    }
}

function initTinyMceFromDom() {
    initTinyMceEditors(document);
}

function initTinyMceFromFrame(event) {
    const root = event && event.target ? event.target : document;
    initTinyMceEditors(root);
}

function scheduleTinyMceInit(root = document) {
    initTinyMceEditors(root);
    window.requestAnimationFrame(() => initTinyMceEditors(root));
    window.setTimeout(() => initTinyMceEditors(root), 120);
}

function scheduleTinyMceInitFromEvent(event) {
    const root = event && event.target ? event.target : document;
    scheduleTinyMceInit(root);
}

function cleanupTinyMceBeforeFrameRender(event) {
    const root = event && event.target ? event.target : null;
    if (!root) {
        return;
    }

    destroyTinyMceEditorsWithin(root);
}

document.addEventListener('DOMContentLoaded', () => scheduleTinyMceInit(document));
window.addEventListener('load', () => scheduleTinyMceInit(document));
document.addEventListener('turbo:load', () => scheduleTinyMceInit(document));
document.addEventListener('turbo:render', () => scheduleTinyMceInit(document));
document.addEventListener('turbo:before-frame-render', cleanupTinyMceBeforeFrameRender);
document.addEventListener('turbo:frame-load', scheduleTinyMceInitFromEvent);
document.addEventListener('turbo:frame-render', scheduleTinyMceInitFromEvent);
document.addEventListener('turbo:before-cache', destroyTinyMceEditors);
