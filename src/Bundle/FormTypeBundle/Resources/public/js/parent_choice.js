(function () {
    function onReady(callback) {
        if (typeof window.IntegratedJQReady === 'function') {
            window.IntegratedJQReady(callback);

            return;
        }

        if (window.jQuery) {
            window.jQuery(function () {
                callback(window.jQuery);
            });
        }
    }

    function initIntegratedParentChoice($) {
        if (!$ || !$.fn || !$.fn.select2) {
            return;
        }

        $('select.integrated_content_parent_choice').each(function () {
            var $element = $(this);

            if ($element.data('select2')) {
                return;
            }

            $element.select2({
                ajax: {
                    url: $element.data('ajax-url'),
                    dataType: 'json',
                    data: function (param) {
                        return {
                            limit: 100,
                            sort: 'title_sort',
                            q: param && typeof param.term !== 'undefined' ? param.term + '*' : '',
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

                        return {results: items};
                    },
                },
            });
        });
    }

    onReady(initIntegratedParentChoice);
})();
