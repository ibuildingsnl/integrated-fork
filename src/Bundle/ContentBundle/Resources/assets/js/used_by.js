$(function() {
    var cnt = $('#used-by');

    var handleUsedBy = function(data) {
        var optionsTemplateSource = $('#used-by-template').html(),
            optionsTemplate = Handlebars.compile(optionsTemplateSource);
        var paginationTemplateSource = $('#pagination-template').html(),
            paginationTemplate = Handlebars.compile(paginationTemplateSource);

        data.title = 'Used by';

        if (data.pagination.numFound == 0) {
            cnt.hide();
        }
        cnt.html(optionsTemplate(data)).
            find('.content_column_inner .aside-item-list-container').
            append(paginationTemplate(data));
        cnt.find('.pagination a').click(function(ev) {
            ev.preventDefault();
            loadUsedBy($(this).attr('href'));
        });
    };

    var loadUsedBy = function(url) {
        cnt.find('.pagination a').unbind('click').click(function(ev) {
            ev.preventDefault();
        });

        $.ajax({
            url: url,
            data: {
                'limit': 5,
            },
            success: handleUsedBy,
        });
    };
    if (typeof usedByUrl !== 'undefined') {
        loadUsedBy(usedByUrl);
    }

    window.addEventListener('message', function(event) {
        // also resize modal if relations are changed
        var iframe = $('[data-id="' + event.data.resizeModal + '"]');
        var height = iframe.contents().height();
        var margin = 120;

        if (height > ($(window).height() - margin)) {
            height = $(window).height() - margin;
        }

        iframe.attr('height', height);
    }, false);

});
