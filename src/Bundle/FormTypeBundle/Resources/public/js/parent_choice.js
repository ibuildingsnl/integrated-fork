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

        function isTopLevelParent(item) {
            if (!item || typeof item !== 'object') {
                return false;
            }

            if (typeof item.path !== 'string') {
                return true;
            }

            return item.path.trim() === '';
        }

        function normalizeTitle(item) {
            if (!item || typeof item !== 'object') {
                return '';
            }

            if (typeof item.title === 'string') {
                return item.title.toLowerCase();
            }

            if (typeof item.text === 'string') {
                return item.text.toLowerCase();
            }

            return '';
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

                            items.sort(function (left, right) {
                                var leftTopLevel = isTopLevelParent(left);
                                var rightTopLevel = isTopLevelParent(right);

                                if (leftTopLevel !== rightTopLevel) {
                                    return leftTopLevel ? -1 : 1;
                                }

                                var leftTitle = normalizeTitle(left);
                                var rightTitle = normalizeTitle(right);

                                if (leftTitle < rightTitle) {
                                    return -1;
                                }

                                if (leftTitle > rightTitle) {
                                    return 1;
                                }

                                return 0;
                            });
                        }

                        return {results: items};
                    },
                },
            });
        });
    }

    onReady(initIntegratedParentChoice);
})();
