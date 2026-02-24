function implementSelect2() {
    /* add select 2 for each relations input */
    $(".relation-items").each(function () {
        var relation = $(this);

        if (relation.data('taxonomyPopup') === 1 || relation.data('taxonomyPopup') === '1') {
            return;
        }

        var value = relation.data('value');
        if (value) {
            for (var i in value) {
                if (!Object.prototype.hasOwnProperty.call(value, i)) {
                    continue;
                }

                var option = relation.find('option[value="' + i + '"]');
                if (!option.length) {
                    relation.append('<option selected="selected" value="' + i + '">' + value[i] + '</option>');
                    continue;
                }

                option.prop('selected', true);
            }
        }

        if (relation.data('bulkRelationInitialized') === '1' || relation.hasClass('select2-hidden-accessible')) {
            relation.trigger('change');
            return;
        }

        relation.select2({
            multiple: $(this).data('multiple'),
            ajax: {
                type: 'GET',
                url: $(this).data('url'),
                dataType: 'json',
                data: function (param) {
                    return {
                        relation: $(this).data('id'),
                        limit:  100,
                        sort: 'title_sort',
                        q: typeof param.term !== 'undefined' ? param.term + '*' : ''
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data.items, function (item) {
                            return {
                                text: item.title,
                                id: item.id
                            }
                        })
                    };
                }
            }
        });

        relation.data('bulkRelationInitialized', '1');
        relation.trigger('change');
    });
}
