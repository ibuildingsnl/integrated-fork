$(function() {
    initContentChoice();
});

function initContentChoice() {
    $('select.integrated_content_choice').each(function() {
        const $element = $(this);

        $element.select2({
            ajax: {
                multiple: $element.data('multiple'),
                allowClear: $element.data('allow-clear'),
                placeholder: '',
                url: $element.data('ajax-url'),
                dataType: 'json',
                data: (param) => ({
                    contenttypes: $element.data('types'),
                    limit: 100,
                    sort: 'title_sort',
                    q: param.term ? param.term + '*' : ''
                }),
                processResults: function (data) {
                    var items = [];

                    if ('items' in data) {
                        for (var k in data.items) {
                            var item = data.items[k];
                            if (!item.text) {
                                item.text = item.title;
                                if (item.path) {
                                    item.text = item.path + ' > ' + item.text;
                                }
                            }
                            items.push(item);
                        }
                    }

                    return { results: items };
                }
            }
        });
    });
}
