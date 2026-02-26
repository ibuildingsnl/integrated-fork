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

    function initIntegratedContentChoice($) {
        if (!$ || !$.fn || !$.fn.select2) {
            return;
        }

        $('select.integrated_content_choice:not(.integrated_content_parent_choice)').each(function () {
            var $element = $(this);

            if ($element.data('select2')) {
                return;
            }

            $element.select2({
                ajax: {
                    multiple: $element.data('multiple'),
                    allowClear: $element.data('allow-clear'),
                    placeholder: '',
                    url: $element.data('ajax-url'),
                    dataType: 'json',
                    data: function (param) {
                        return {
                            contenttypes: $element.data('types'),
                            limit: 100,
                            sort: 'title',
                            q: param && param.term ? param.term + '*' : '',
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

    onReady(initIntegratedContentChoice);
})();
