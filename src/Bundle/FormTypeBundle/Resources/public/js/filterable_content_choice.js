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

    function getFilterValue($widget, role) {
        var $filter = $widget.find('[data-filter-role="' + role + '"]');

        return $filter.length ? $filter.val() : '';
    }

    function initIntegratedFilterableContentChoice($) {
        if (!$ || !$.fn || !$.fn.select2) {
            return;
        }

        $('select.integrated_filterable_content_choice').each(function () {
            var $element = $(this);
            var $widget = $element.closest('.integrated_filterable_content_choice_widget');

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
                            search_context: 'filterable_content_choice',
                            channels: getFilterValue($widget, 'channel'),
                            contenttypes: getFilterValue($widget, 'content-type') || $element.data('types'),
                            limit: 100,
                            sort: 'title',
                            q: param && param.term ? param.term : '',
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

            $widget.find('[data-filter-role]').on('change', function () {
                $element.val(null).trigger('change.select2');
            });
        });
    }

    onReady(initIntegratedFilterableContentChoice);
})();
