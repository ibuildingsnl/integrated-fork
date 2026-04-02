import $ from 'jquery';

jQuery = $;
global.$ = global.jQuery = $;
window.$ = window.jQuery = $;

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

$('[data-prototype]').each(function(index, elm) {
    init($(this));

    function init($collection) {
        $collection.data('index', $collection.find('ul li').length);

        $collection.find('ul li').each(function() {
            register($(this));
        });

        $collection.children('[data-addfield="collection"]').click(function(event) {
            event.preventDefault();
            event.stopPropagation();

            append();
        });

        function initNestedCollections($context) {
            $context.find('[data-prototype]').each(function() {
                init($(this));
            });
        }

        function append() {
            let index = $collection.data('index');
            let prototype = $collection.data('prototype').
                replace(/__name__/g, index).
                replace(/__id__/g, $collection.attr('id') + '_' + index);

            let item = $('<li></li>').append(prototype);

            $collection.data('index', index + 1);
            $collection.find('> ul').append(item);
            initNestedCollections(item);

            if ($collection.find('ul li:last-child').
                find('select.integrated_content_choice').length > 0) {
                initContentChoice();
            }

            if ($collection.find('ul li:last-child').
                find('select.select2').length > 0) {
                $($collection.find('ul li:last-child').
                    find('select.select2')).select2({
                    placeholder: $(this).data('placeholder'),
                });
            }

            let editor = $collection.find('ul li:last-child').
                find('textarea.integrated_tinymce');

            if (editor.length > 0) {
                let style_formats = [
                    {title: 'Paragraph', format: 'p'},
                    {title: 'Heading 2', block: 'h2'},
                    {title: 'Heading 3', block: 'h3'},
                    {title: 'Heading 4', block: 'h4'},
                    {title: 'Heading 5', block: 'h5'},
                    {title: 'Preformatted (fixed font)', block: 'pre'},
                ];

                style_formats = style_formats.concat(normalizeTinyMceStyleFormats(editor.data('format_styles') || []));
                const wrapperDivStyles = getTinyMceWrapperDivStyles(style_formats);
                tinymce.init({
                    selector: '#' + editor.attr('id'),
                    plugins:
                        'advlist autolink link lists charmap anchor pagebreak ' +
                        'searchreplace wordcount visualchars fullscreen nonbreaking ' +
                        'table directionality template wordcount autoresize code articlelinksearch',
                    schema: 'html5',
                    menubar: true,
                    branding: false,
                    toolbar:
                        'styles | bold italic underline | bullist numlist | ' +
                        'link anchor integratedImage integratedVideo integratedColumn image media print preview fullpage table | ' +
                        'charmap pagebreak | pastetext searchreplace | code fullscreen articlelinksearch',
                    browser_spellcheck: true,
                    convert_urls: false,
                    content_css: editor.data('content_css'),
                    content_style: editor.data('content_style'),
                    integrated_browser_media_types_url: editor.data(
                        'integrated_browser_media_types_url'),
                    integrated_browser_search_url: editor.data(
                        'integrated_browser_search_url'),
                    integrated_browser_file_url: editor.data(
                        'integrated_browser_file_url'),
                    integrated_browser_file_resize_url: editor.data(
                        'integrated_browser_file_resize_url'),
                    document_base_url: editor.data('document_base_url'),
                    style_formats: style_formats,
                    setup: function (tinyEditor) {
                        const normalizeWrappedLists = function() {
                            window.requestAnimationFrame(function() {
                                normalizeTinyMceWrappedLists(tinyEditor, wrapperDivStyles);
                            });
                        };

                        tinyEditor.on('init change SetContent ExecCommand', normalizeWrappedLists);
                    },
                });
            }
            register($collection.find('ul li:last-child'));
        }

        function register(elm) {
            elm.find('[data-removefield="collection"]').
                filter(function() {
                    return $(this).closest('li').get(0) === elm.get(0);
                }).
                on('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    elm.remove();
                });

            elm.find('.state_name_input_field').on('input', function(event) {
                elm.find('.panel-heading .panel-title span.state-name').
                    html((this).value);
            });

            elm.find('div.panel-heading').click(function() {
                if ($(this).parent().find('.panel-collapse').hasClass('in')) {
                    $(this).parent().find('.panel-collapse').removeClass('in');
                    $(this).parent().removeClass('in');
                } else {
                    $(this).parent().find('.panel-collapse').addClass('in');
                    $(this).parent().addClass('in');
                }
            });
        }
    }
});
