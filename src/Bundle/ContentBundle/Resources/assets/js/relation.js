/* add select 2 for each relations input */
$(".relation-items").each(function() {
    var multiple = $(this).data('multiple');
    var relation_id = $(this).attr('id');

    var $relation = $(this);
    var defaultValues = $.parseJSON($('#default_references').val());
    var $addWrapper = $relation.next('[data-add="1"]');

    if (defaultValues[relation_id] !== undefined && defaultValues[relation_id].length) {
        $.each(defaultValues[relation_id], function() {
            $relation.append('<option selected value="'+this.id+'" data-image="' + (this.image ? this.image : '') + '">'+this.title+'</option>');
        });
    }

    $relation.select2({
        multiple: multiple,
        allowClear: !multiple,
        placeholder: '',
        ajax: {
            type: 'GET',
            url: $relation.data('url'),
            dataType: 'json',
            data: function(param) {
                return {
                    relation: relation_id,
                    limit: 100,
                    sort: 'title',
                    q: typeof param.term != 'undefined' ? param.term + '*' : ''
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
        },
        templateResult: function (state) {
            if (!state.id) {
                return state.text;
            }

            var image = state.image ? '<img src="' + state.image + '" class="select2-dropdown-image" />' : '';

            return $('<span>' + image + state.text + '</span>');
        },
        templateSelection: function (data) {
            if (!data.id) {
                return data.text;
            }

            var image = '';
            if (data.image) {
                image = data.image;
            } else if (data.element && $(data.element).data('image')) {
                image = $(data.element).data('image');
            }

            if (image) {
                image = '<img src="' + image + '"/>'
            }

            return $('<div class="select2-selected">' + image + data.text + '</div>');
        }
    });

    $(this).on('change', function () {
        $('[data-relation="' + relation_id + '"]').val( $(this).val() );
    });

    var source = $("#add-template").html();
    var template = Handlebars.compile(source);

    var contentRelation = [];
    $.each($relation.data('types'), function() {
        contentRelation.push({
            'name': this.name,
            'href': $relation.data('url-new').replace('__type__', this.type).replace('__relation__', relation_id)
        });
    });

    var context = {relations: contentRelation};
    var html = template(context);
    $addWrapper.html(html);
});

$('.relations').on('click', '[data-modal]', function(e){
    e.preventDefault();
    if ($(this).parents('.add-relation').length) {
        var modal = $(this).parents('.add-relation').next('#relation-add-modal');
    } else {
        var modal = $(this).next('#relation-add-modal');
    }

    var iFrame = modal.find('iframe');

    modal.find('.modal-title').text($(this).data('title'));

    iFrame.css('display', 'block').attr('src', $(this).data('href')).on('load', function(e){

        iFrame.show();
        modal.show();
        modal.append('<div class="modal-backdrop fade in"></div>');

        iFrame.contents().find('*[data-dismiss="modal"]').click(function(ev){
            ev.preventDefault();
        });

        iFrame.unbind('load');
    });
});

/* handle Closing the modal */
$('button[data-dismiss="modal"]').on('click', function() {
    var modal = $(this).closest('#relation-add-modal');
    modal.hide();
    $('.modal-backdrop').remove();
});
