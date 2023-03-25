$(document).ready(function () {
    const cnt = $('#used-by');

    const handleUsedBy = (data) => {
        const optionsTemplate = Handlebars.compile($('#used-by-template').html());
        const paginationTemplate = Handlebars.compile($('#pagination-template').html());

        data.title = 'Used by';

        if (data.pagination.numFound === 0) {
            cnt.hide();
        }

        cnt.html(optionsTemplate(data))
        .find('.content_column_inner .aside-item-list-container')
        .append(paginationTemplate(data));

        cnt.find('.pagination a').click((ev) => {
            ev.preventDefault();
            loadUsedBy($(ev.currentTarget).attr('href'));
        });
    };

    const loadUsedBy = (url) => {
        cnt.find('.pagination a').unbind('click').click((ev) => {
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

    window.addEventListener('message', (event) => {
        const iframe = $('[data-id="' + event.data.resizeModal + '"]');
        let height = iframe.contents().height();
        const margin = 120;

        if (height > ($(window).height() - margin)) {
            height = $(window).height() - margin;
        }

        iframe.attr('height', height);
    }, false);
});
