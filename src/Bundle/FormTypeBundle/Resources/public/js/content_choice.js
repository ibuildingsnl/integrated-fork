$(function() {
    initContentChoice();
});

function initContentChoice() {
    $('select.integrated_content_choice').select2({
        ajax: {
            data: function (param) {
                return {
                    limit:  100,
                    sort: 'title',
                    q: typeof param.term !== 'undefined' ? param.term + '*' : ''
                };
            },
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
}
