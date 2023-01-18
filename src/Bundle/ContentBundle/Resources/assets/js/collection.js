import $ from 'jquery';
jQuery = $;
global.$ = global.jQuery = $;
window.$ = window.jQuery = $;

$('[data-prototype]').each(function(index, elm) {
    init($(this));

    function init($collection) {
        $collection.data('index', $collection.find('ul li').length);

        $collection.find('ul li').each(function() {
            register($(this));
        });

        $collection.find('[data-addfield="collection"]').click(function(event) {
            event.preventDefault();

            append();
        });

        function append() {
            let index = $collection.data('index');
            let prototype = $collection.data('prototype')
                .replace(/__name__/g, index)
                .replace(/__id__/g, $collection.attr('id') + '_' + index);

            let item = $('<li></li>').append(prototype);

            $collection.data('index', index + 1);
            $collection.find('> ul').append(item);

            initContentChoice();

            let editor = $collection.find('ul li:last-child').find('textarea.integrated_tinymce');

            if (editor) {
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

                style_formats = style_formats.concat(editor.data('format_styles'));
                tinymce.init({
                    selector: '#' + editor.attr('id'),
                    plugins:
                        "advlist autolink link lists charmap anchor pagebreak " +
                        "searchreplace wordcount visualchars fullscreen nonbreaking " +
                        "table directionality template wordcount autoresize code",
                    schema: "html5",
                    menubar: true,
                    branding: false,
                    toolbar:
                        "styles | bold italic underline | bullist numlist | " +
                        "link anchor integratedImage integratedVideo integratedColumn image media print preview fullpage table | " +
                        "charmap pagebreak | pastetext searchreplace | code fullscreen",
                    browser_spellcheck : true,
                    convert_urls: false,
                    content_css: editor.data('content_css'),
                    integrated_browser_media_types_url: editor.data('integrated_browser_media_types_url'),
                    integrated_browser_search_url: editor.data('integrated_browser_search_url'),
                    integrated_browser_file_url: editor.data('integrated_browser_file_url'),
                    integrated_browser_file_resize_url: editor.data('integrated_browser_file_resize_url'),
                    document_base_url : editor.data('document_base_url'),
                    style_formats: style_formats
                });
            }
            register($collection.find('ul li:last-child'));
        }

        function register(elm) {
            elm.find('[data-removefield="collection"]').on('click', function(event) {
                event.preventDefault();

                elm.remove();
            })


            elm.find('div.panel-heading').click( function() {
                if ($(this).parent().find('.panel-collapse').hasClass('in')) {
                    $(this).parent().find('.panel-collapse').removeClass('in');
                    $(this).parent().removeClass('in')
                } else {
                    $(this).parent().find('.panel-collapse').addClass('in');
                    $(this).parent().addClass('in')
                }
            })
        }
    }
});

