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

    return (a.host && a.host != window.location.host);
}

function extractEmbeddableUrl(input) {
    if (!input) {
        return '';
    }

    const container = document.createElement('div');
    container.innerHTML = input;

    const firstLink = container.querySelector('a[href]');
    if (firstLink) {
        return firstLink.getAttribute('href') || '';
    }

    return container.textContent ? container.textContent.trim() : input;
}

const TINYMCE_BRAND_THEME_EVENT = 'integrated:editor-brand-theme';
const TINYMCE_PREVIEW_STYLE_ID = 'integrated-tinymce-button-preview-style';

function normalizeTinyMceStyleFormats(styles = []) {
    return styles.map((style) => {
        const newStyle = {...style};

        for (const property in newStyle) {
            if (newStyle[property] === 'true') {
                newStyle[property] = true;
            } else if (newStyle[property] === 'false') {
                newStyle[property] = false;
            }
        }

        if (newStyle.inline === 'a' && !newStyle.selector) {
            newStyle.inline = 'span';
        }

        return newStyle;
    });
}

function parseTinyMceContentStyleVariables(contentStyle = '') {
    const variables = {};

    for (const match of String(contentStyle || '').matchAll(/(--td-color-[a-z-]+)\s*:\s*([^;]+)\s*;/g)) {
        const property = String(match[1] || '').trim();
        const value = String(match[2] || '').trim();

        if (!property || !value) {
            continue;
        }

        variables[property] = value;
    }

    return variables;
}

function applyTinyMceContentStyleVariables(target, variables = {}) {
    if (!target || !target.style) {
        return;
    }

    Object.entries(variables).forEach(([property, value]) => {
        target.style.setProperty(property, value);
    });
}

function ensureTinyMceToolbarPreviewStyles() {
    if (document.getElementById(TINYMCE_PREVIEW_STYLE_ID)) {
        return;
    }

    const style = document.createElement('style');
    style.id = TINYMCE_PREVIEW_STYLE_ID;
    style.textContent = `
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label {
            line-height: 1.35 !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label h1,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label h2,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label h3,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label h4,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label h5,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label blockquote,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label cite,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .intro,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .kader,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .kaderkop {
            margin: 0 !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label h1 {
            font-size: 1.05rem !important;
            font-weight: 700 !important;
            line-height: 1.2 !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label h2 {
            font-size: 1rem !important;
            font-weight: 700 !important;
            line-height: 1.2 !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label h3 {
            font-size: 0.95rem !important;
            font-weight: 650 !important;
            line-height: 1.2 !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label h4,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label h5 {
            font-size: 0.9rem !important;
            font-weight: 600 !important;
            line-height: 1.2 !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label blockquote {
            border-left: 2px solid #cbd5e1 !important;
            color: #475569 !important;
            font-size: 0.9rem !important;
            padding-left: 0.5rem !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label cite {
            color: #64748b !important;
            font-size: 0.85rem !important;
            font-style: italic !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .intro,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .kader,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .kaderkop {
            background: #f7f7f8 !important;
            border-radius: 6px !important;
            display: inline-block !important;
            font-size: 0.9rem !important;
            line-height: 1.25 !important;
            padding: 0.125rem 0.5rem !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .btn {
            align-items: center;
            appearance: none;
            border: 1px solid transparent !important;
            border-radius: 999px !important;
            cursor: pointer;
            display: inline-flex !important;
            font-size: 0.8rem !important;
            font-weight: 600 !important;
            line-height: 1.2 !important;
            padding: 0.1rem 0.45rem !important;
            text-decoration: none !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .btn-gray,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .btn-gray:visited {
            background-color: #8f9195 !important;
            border-color: #8f9195 !important;
            color: #fff !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .btn-gray:hover,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .btn-gray:active {
            background-color: #74767a !important;
            border-color: #74767a !important;
            color: #fff !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .btn-primary,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .btn-primary:visited {
            background-color: var(--editor-color-primary, #d2312f) !important;
            border-color: var(--editor-color-primary, #d2312f) !important;
            color: #fff !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .btn-primary:hover,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .btn-primary:active {
            background-color: var(--editor-color-primary-dark, #a72826) !important;
            border-color: var(--editor-color-primary-dark, #a72826) !important;
            color: #fff !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .btn-secondary,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .btn-secondary:visited {
            background-color: var(--editor-color-secondary, #d2312f) !important;
            border-color: var(--editor-color-secondary, #d2312f) !important;
            color: #fff !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .btn-secondary:hover,
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .btn-secondary:active {
            background-color: var(--editor-color-secondary-dark, #a72826) !important;
            border-color: var(--editor-color-secondary-dark, #a72826) !important;
            color: #fff !important;
        }
        .tox-tinymce-aux .tox-collection__item .tox-collection__item-label .btn.btn-l {
            font-size: 0.8rem !important;
            padding: 0.1rem 0.45rem !important;
        }
    `;

    document.head.appendChild(style);
}

function applyTinyMceBrandTheme(contentStyle = '') {
    const variables = parseTinyMceContentStyleVariables(contentStyle);
    if (Object.keys(variables).length === 0) {
        return;
    }

    ensureTinyMceToolbarPreviewStyles();
    applyTinyMceContentStyleVariables(document.documentElement, variables);
    applyTinyMceContentStyleVariables(document.querySelector('.tox-tinymce-aux'), variables);
}

function applyTinyMceBrandThemeToEditor(editor, contentStyle = '') {
    const variables = parseTinyMceContentStyleVariables(contentStyle);
    if (!editor || Object.keys(variables).length === 0) {
        return;
    }

    applyTinyMceContentStyleVariables(editor.getBody && editor.getBody(), variables);

    const doc = editor.getDoc ? editor.getDoc() : null;
    if (doc && doc.documentElement) {
        applyTinyMceContentStyleVariables(doc.documentElement, variables);
    }
}

function bindTinyMceBrandThemeUpdates() {
    if (document.body?.dataset.boundTinyMceBrandThemeUpdates === 'true') {
        return;
    }

    document.addEventListener(TINYMCE_BRAND_THEME_EVENT, function(event) {
        const contentStyle = String(event?.detail?.contentStyle || '').trim();
        if (!contentStyle) {
            return;
        }

        document.querySelectorAll('.integrated_tinymce').forEach((field) => {
            field.setAttribute('data-content_style', contentStyle);
        });

        applyTinyMceBrandTheme(contentStyle);

        if (typeof tinymce === 'undefined' || !Array.isArray(tinymce.editors)) {
            return;
        }

        tinymce.editors.forEach((editor) => {
            applyTinyMceBrandThemeToEditor(editor, contentStyle);
        });
    });

    if (document.body) {
        document.body.dataset.boundTinyMceBrandThemeUpdates = 'true';
    }
}

function getTinyMceStyleClasses(style) {
    if (!style || style.classes == null) {
        return [];
    }

    if (typeof style.classes === 'string') {
        return style.classes.trim().split(/\s+/).filter(Boolean);
    }

    if (Array.isArray(style.classes)) {
        return style.classes
            .filter((className) => typeof className === 'string')
            .map((className) => className.trim())
            .filter(Boolean);
    }

    return [];
}

function getTinyMceWrapperDivStyles(styles = []) {
    return styles.filter((style) => {
        return style
            && style.wrapper === true
            && style.block === 'div'
            && getTinyMceStyleClasses(style).length > 0;
    });
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function isFacebookFallbackEmbed(doc) {
    return Boolean(doc && doc.querySelector && doc.querySelector('.fb-post[data-href]'));
}

function extractFacebookFallbackUrl(doc) {
    const node = doc && doc.querySelector ? doc.querySelector('.fb-post[data-href]') : null;
    return node ? node.getAttribute('data-href') || '' : '';
}

function buildFacebookEmbedPreview(data, extractedUrl) {
    const image = data.image ? '<div style="background:#eef1f4;"><img src="' + escapeHtml(data.image) + '" alt="" style="display:block;width:100%;max-height:320px;object-fit:cover;"></div>' : '';
    const title = escapeHtml(data.author_name || data.provider_name || 'Facebook');
    const meta = escapeHtml(data.published_date || 'Facebook post');
    const description = escapeHtml(data.description || extractedUrl || 'Facebook embed');
    const visibleUrl = escapeHtml(data.provider_url || data.url || extractedUrl || '');

    return '' +
        '<div class="facebook-embed-preview" style="max-width:500px;border:1px solid #d9dee5;border-radius:8px;overflow:hidden;background:#fff;font-family:Helvetica,Arial,sans-serif;color:#1c1e21;">' +
            image +
            '<div style="padding:16px;">' +
                '<div style="font-size:18px;line-height:1.3;font-weight:700;margin:0 0 4px;">' + title + '</div>' +
                '<div style="font-size:13px;line-height:1.4;color:#65676b;margin:0 0 12px;">' + meta + '</div>' +
                '<div style="font-size:16px;line-height:1.55;white-space:pre-wrap;margin:0 0 12px;">' + description + '</div>' +
                '<div style="font-size:12px;line-height:1.4;color:#65676b;word-break:break-word;">' + visibleUrl + '</div>' +
            '</div>' +
        '</div>';
}

function buildFacebookPreviewWrapper(originalHtml, previewHtml) {
    return '<div class="embed-content Facebook" data-facebook-embed-original="' + escapeHtml(originalHtml) + '">' + previewHtml + '</div><br>';
}

function restoreFacebookEmbedOriginalMarkup(content) {
    const container = document.createElement('div');
    container.innerHTML = content;

    container.querySelectorAll('.embed-content.Facebook[data-facebook-embed-original]').forEach((node) => {
        const original = node.getAttribute('data-facebook-embed-original');
        if (!original) {
            return;
        }

        node.innerHTML = original;
        node.removeAttribute('data-facebook-embed-original');
    });

    return container.innerHTML;
}

function hydrateFacebookEmbedPreviews(root) {
    if (!root || typeof root.querySelectorAll !== 'function') {
        return;
    }

    root.querySelectorAll('.embed-content.Facebook').forEach((node) => {
        if (!node || node.nodeType !== Node.ELEMENT_NODE || node.querySelector('.facebook-embed-preview')) {
            return;
        }

        const originalHtml = node.getAttribute('data-facebook-embed-original') || node.innerHTML;
        if (!originalHtml) {
            return;
        }

        const parser = new DOMParser();
        const doc = parser.parseFromString(originalHtml, 'text/html');
        if (!isFacebookFallbackEmbed(doc)) {
            return;
        }

        const extractedUrl = extractFacebookFallbackUrl(doc);
        const previewHtml = buildFacebookEmbedPreview({
            provider_name: 'Facebook',
            description: '',
            image: '',
            author_name: '',
            published_date: '',
            provider_url: '',
            url: extractedUrl
        }, extractedUrl);

        node.setAttribute('data-facebook-embed-original', originalHtml);
        node.innerHTML = previewHtml;

        if (!extractedUrl || node.dataset.facebookPreviewMetadataLoaded === 'true' || node.dataset.facebookPreviewMetadataLoading === 'true') {
            return;
        }

        node.dataset.facebookPreviewMetadataLoading = 'true';

        $.ajax({
            url: '/admin/_oembed/fetch-data',
            dataType: 'json',
            data: {
                url: encodeURIComponent(extractedUrl)
            },
            success: function(data) {
                if (!data || data.provider_name !== 'Facebook') {
                    return;
                }

                node.setAttribute('data-facebook-embed-original', originalHtml);
                node.innerHTML = buildFacebookEmbedPreview(data, extractedUrl);
                node.dataset.facebookPreviewMetadataLoaded = 'true';
            },
            complete: function() {
                delete node.dataset.facebookPreviewMetadataLoading;
            }
        });
    });
}

function matchesTinyMceWrapperClasses(element, style) {
    if (!element || !style || element.tagName !== 'DIV') {
        return false;
    }

    const classes = getTinyMceStyleClasses(style);

    return classes.every((className) => element.classList.contains(className));
}

function getSignificantChildNodes(element) {
    return Array.from(element.childNodes || []).filter((node) => {
        if (node.nodeType === Node.TEXT_NODE) {
            return String(node.textContent || '').trim() !== '';
        }

        return node.nodeType === Node.ELEMENT_NODE;
    });
}

function normalizeTinyMceWrappedLists(editor, wrapperStyles = []) {
    if (!editor || !editor.dom || !editor.getBody || !wrapperStyles.length) {
        return;
    }

    const body = editor.getBody();
    if (!body || body.dataset.integratedNormalizingWrappedLists === '1') {
        return;
    }

    body.dataset.integratedNormalizingWrappedLists = '1';

    try {
        body.querySelectorAll('ul, ol').forEach((list) => {
            const items = Array.from(list.children || []).filter((item) => item.tagName === 'LI');
            if (!items.length) {
                return;
            }

            wrapperStyles.forEach((style) => {
                const wrappers = items.map((item) => {
                    const children = getSignificantChildNodes(item);
                    if (children.length !== 1 || children[0].nodeType !== Node.ELEMENT_NODE) {
                        return null;
                    }

                    const child = children[0];
                    return matchesTinyMceWrapperClasses(child, style) ? child : null;
                });

                if (wrappers.some((wrapper) => !wrapper)) {
                    return;
                }

                wrappers.forEach((wrapper) => {
                    while (wrapper.firstChild) {
                        wrapper.parentNode.insertBefore(wrapper.firstChild, wrapper);
                    }

                    wrapper.remove();
                });

                let container = list.parentElement;
                if (!matchesTinyMceWrapperClasses(container, style)) {
                    container = editor.dom.create('div', {'class': getTinyMceStyleClasses(style).join(' ')});
                    list.parentNode.insertBefore(container, list);
                    container.appendChild(list);
                }
            });
        });
    } finally {
        delete body.dataset.integratedNormalizingWrappedLists;
    }
}

function initTinyMceEditors(root = document) {
    bindTinyMceBrandThemeUpdates();
    ensureTinyMceToolbarPreviewStyles();

    $('.integrated_tinymce', root).each(function(key, elem){
        const element = $(elem);
        const currentContentStyle = String(element.data('content_style') || '').trim();
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
    ];

    let custom_styles = element.data('format_styles') || [];

    style_formats = style_formats.concat(normalizeTinyMceStyleFormats(custom_styles));
    const wrapperDivStyles = getTinyMceWrapperDivStyles(style_formats);
    applyTinyMceBrandTheme(currentContentStyle);

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
            "styles | bold italic underline | bullist numlist | alignleft aligncenter alignright alignjustify | " +
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
        content_style: element.data('content_style'),
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

            let input = extractEmbeddableUrl(args.content.trim());

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

                    if (data.provider_name === 'Facebook' && isFacebookFallbackEmbed(doc)) {
                        var extractedUrl = extractFacebookFallbackUrl(doc);
                        var previewHtml = buildFacebookEmbedPreview(data, extractedUrl);
                        args.content = buildFacebookPreviewWrapper(modifiedCode, previewHtml);
                        return;
                    }

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

            function getAdjacentSlide(slide, direction) {
                let candidate = direction < 0 ? slide.previousElementSibling : slide.nextElementSibling;

                while (candidate && (!candidate.classList || !candidate.classList.contains('swiper-slide'))) {
                    candidate = direction < 0 ? candidate.previousElementSibling : candidate.nextElementSibling;
                }

                return candidate;
            }

            function moveSlide(slide, direction) {
                const wrapper = slide ? slide.parentElement : null;
                if (!wrapper || !wrapper.classList || !wrapper.classList.contains('swiper-wrapper')) {
                    return false;
                }

                if (direction < 0) {
                    const previousSlide = getAdjacentSlide(slide, -1);
                    if (!previousSlide) {
                        return false;
                    }

                    wrapper.insertBefore(slide, previousSlide);
                    return true;
                }

                const nextSlide = getAdjacentSlide(slide, 1);
                if (!nextSlide) {
                    return false;
                }

                wrapper.insertBefore(nextSlide, slide);
                return true;
            }

            function updateSlideMoveButtons(wrapper) {
                const slides = Array.from(wrapper.querySelectorAll(':scope > .swiper-slide'));
                const lastIndex = slides.length - 1;

                slides.forEach(function(slide, index) {
                    const previousButton = slide.querySelector(':scope > .slider-append.move-prev');
                    const nextButton = slide.querySelector(':scope > .slider-append.move-next');

                    if (previousButton) {
                        const disabled = index === 0;
                        previousButton.classList.toggle('is-disabled', disabled);
                        previousButton.setAttribute('aria-disabled', disabled ? 'true' : 'false');
                    }

                    if (nextButton) {
                        const disabled = index === lastIndex;
                        nextButton.classList.toggle('is-disabled', disabled);
                        nextButton.setAttribute('aria-disabled', disabled ? 'true' : 'false');
                    }
                });
            }

            function ensureSlideMoveButton(slide, direction) {
                const directionClass = direction < 0 ? 'move-prev' : 'move-next';
                const buttonTitle = direction < 0 ? 'Move slide left' : 'Move slide right';
                const boundKey = direction < 0 ? 'integratedMovePrevBound' : 'integratedMoveNextBound';
                let button = slide.querySelector(':scope > .slider-append.' + directionClass);
                if (!button) {
                    button = editor.contentDocument.createElement('span');
                    button.classList.add('slider-append', 'move', directionClass);
                    button.innerHTML = direction < 0
                        ? '<svg width="24" height="24" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 6L8 12L14 18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                        : '<svg width="24" height="24" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 6L16 12L10 18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                    slide.appendChild(button);
                }

                button.setAttribute('data-mce-bogus', 'all');
                button.setAttribute('contenteditable', 'false');
                button.setAttribute('tabindex', '0');
                button.setAttribute('role', 'button');
                button.setAttribute('aria-label', buttonTitle);
                button.setAttribute('title', buttonTitle);

                if (button.dataset[boundKey] === '1') {
                    return;
                }

                const suppressMouseDown = function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                };

                const onMove = function(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    if (button.getAttribute('aria-disabled') === 'true') {
                        return;
                    }

                    if (moveSlide(slide, direction)) {
                        updateSlideMoveButtons(slide.parentElement);
                        button.focus();
                    }
                };

                const handleKeyDown = function(event) {
                    if (event.key !== 'Enter' && event.key !== ' ') {
                        return;
                    }

                    onMove(event);
                };

                button.addEventListener('mousedown', suppressMouseDown);
                button.addEventListener('click', onMove);
                button.addEventListener('keydown', handleKeyDown);
                button.dataset[boundKey] = '1';
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
                    ensureSlideMoveButton(slide, -1);
                    ensureSlideMoveButton(slide, 1);
                });

                if (!wrapper.querySelector(':scope > .swiper-slide')) {
                    swiper.remove();
                    return;
                }

                updateSlideMoveButtons(wrapper);
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
                if (editor.contentDocument?.body && editor.contentDocument.body.dataset.boundTinyMceToolbarOverflowClose !== 'true') {
                    editor.contentDocument.addEventListener('mousedown', function() {
                        closeTinyMceToolbarOverflow();
                    });
                    editor.contentDocument.body.dataset.boundTinyMceToolbarOverflowClose = 'true';
                }
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

            editor.on('blur', function() {
                closeTinyMceToolbarOverflow();
            });

            const normalizeWrappedLists = function() {
                window.requestAnimationFrame(function() {
                    normalizeTinyMceWrappedLists(editor, wrapperDivStyles);
                });
            };

            editor.on('init change SetContent ExecCommand', normalizeWrappedLists);
            editor.on('init SetContent', function() {
                applyTinyMceBrandThemeToEditor(editor, currentContentStyle);
                hydrateFacebookEmbedPreviews(editor.getBody());
            });
            editor.on('GetContent', function(event) {
                if (!event || typeof event.content !== 'string' || event.content.indexOf('data-facebook-embed-original') === -1) {
                    return;
                }

                event.content = restoreFacebookEmbedOriginalMarkup(event.content);
            });
        }
    });

        element.data('tinymce-initialized', true);
    });
}

function closeTinyMceToolbarOverflow() {
    let closedViaToggle = false;

    document.querySelectorAll('.tox-tinymce-aux .tox-toolbar__overflow').forEach((overflow) => {
        const container = overflow.closest('[id^="aria-controls_"]');
        const toggle = findTinyMceToolbarOverflowToggle(container ? container.id : null);
        if (toggle && toggle.getAttribute('aria-expanded') === 'true') {
            toggle.click();
            closedViaToggle = true;
            return;
        }

        if (container) {
            resetTinyMceToolbarOverflowToggle(container.id);
            container.remove();
            return;
        }

        overflow.remove();
    });

    if (!closedViaToggle) {
        resetTinyMceToolbarOverflowToggle();
    }
}

function findTinyMceToolbarOverflowToggle(controlId = null) {
    return Array.from(document.querySelectorAll('.tox .tox-tbtn[aria-haspopup="true"]')).find((button) => {
        const label = button.getAttribute('aria-label');
        if (label !== 'Reveal or hide additional toolbar items') {
            return false;
        }

        if (!controlId) {
            return true;
        }

        return button.getAttribute('aria-controls') === controlId;
    }) || null;
}

function resetTinyMceToolbarOverflowToggle(controlId = null) {
    const button = findTinyMceToolbarOverflowToggle(controlId);
    if (!button) {
        return;
    }

    if (!controlId && button.getAttribute('aria-expanded') !== 'true') {
        return;
    }

    button.setAttribute('aria-expanded', 'false');
    button.removeAttribute('aria-controls');
    button.classList.remove('tox-tbtn--enabled');
}

function bindTinyMceToolbarOverflowClose() {
    if (document.body?.dataset.boundTinyMceToolbarOverflowClose === 'true') {
        return;
    }

    document.addEventListener('mousedown', function(event) {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        const openOverflow = document.querySelector('.tox-tinymce-aux .tox-toolbar__overflow');
        if (!openOverflow) {
            return;
        }

        if (target.closest('.tox-tinymce-aux .tox-toolbar__overflow')) {
            return;
        }

        closeTinyMceToolbarOverflow();
    });

    if (document.body) {
        document.body.dataset.boundTinyMceToolbarOverflowClose = 'true';
    }
}

$(window).keyup(function(e) {
    if (e.key === "Escape") {
        closeTinyMceToolbarOverflow();
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
    bindTinyMceToolbarOverflowClose();
    initTinyMceEditors(document);
}

function initTinyMceFromFrame(event) {
    const root = event && event.target ? event.target : document;
    bindTinyMceToolbarOverflowClose();
    initTinyMceEditors(root);
}

function scheduleTinyMceInit(root = document) {
    bindTinyMceToolbarOverflowClose();
    initTinyMceEditors(root);
    window.requestAnimationFrame(() => initTinyMceEditors(root));
    window.setTimeout(() => initTinyMceEditors(root), 120);
}

function getTinyMceInitRootFromEvent(event) {
    if (event && event.detail && event.detail.newStream) {
        const stream = event.detail.newStream;
        const targetId = stream.getAttribute('target') || stream.target || '';
        if (targetId) {
            return document.getElementById(targetId) || document;
        }

        const targets = stream.getAttribute('targets');
        if (targets) {
            return document.querySelector(targets) || document;
        }

        return document;
    }

    if (event && event.target) {
        return event.target;
    }

    return document;
}

function scheduleTinyMceInitFromEvent(event) {
    const root = getTinyMceInitRootFromEvent(event);
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
document.addEventListener('turbo:after-stream-render', scheduleTinyMceInitFromEvent);
document.addEventListener('turbo:before-frame-render', cleanupTinyMceBeforeFrameRender);
document.addEventListener('turbo:frame-load', scheduleTinyMceInitFromEvent);
document.addEventListener('turbo:frame-render', scheduleTinyMceInitFromEvent);
document.addEventListener('turbo:before-cache', destroyTinyMceEditors);
