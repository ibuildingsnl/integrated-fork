function updateSelected(data, relation_id) {
    const image = data.image || $(data.element).data('image');
    return $('<div class="select2-selected">' + (image ? `<img src="${image}" />` : '') + data.text + '</div>');
}

$(".relation-items").each(function () {
    const $relation = $(this);
    const relation_id = $relation.attr('id');
    const defaultValues = $.parseJSON($('#default_references').val());
    const $addWrapper = $relation.next('[data-add="1"]');

    if (defaultValues[relation_id]) {
        defaultValues[relation_id].forEach(({id, title, image}) => {
            $relation.append(`<option selected value="${id}" data-image="${image || ''}">${title}</option>`);
        });
    }

    $relation.select2({
        multiple: $relation.data('multiple'),
        allowClear: !$relation.data('multiple'),
        placeholder: '',
        ajax: {
            type: 'GET',
            url: $relation.data('url'),
            dataType: 'json',
            data: (param) => ({
                relation: relation_id,
                limit: 100,
                sort: 'title',
                q: param.term ? param.term + '*' : ''
            }),
            processResults: (data) => ({
                results: data.items.map(item => {
                    item.text = (item.path ? item.path + ' > ' : '') + item.title;
                    return item;
                })
            })
        },
        templateResult: (state) => {
            if (!state.id) return state.text;
            const image = state.image ? `<img src="${state.image}" class="select2-dropdown-image" />` : '';
            return $(`<span>${image}${state.text}</span>`);
        },
        templateSelection: (data) => data.id ? updateSelected(data, relation_id) : data.text
    }).on('change', function () {
        $(`[data-relation="${relation_id}"]`).val($(this).val());
    });

    const template = Handlebars.compile($("#add-template").html());
    const contentRelation = $relation.data('types').map(({name, type}) => ({
        name,
        href: $relation.data('url-new').replace('__type__', type).replace('__relation__', relation_id)
    }));

    $addWrapper.html(template({relations: contentRelation}));
});

const trackedModals = new Set();
const becomesHiddenObserver = new MutationObserver((entries, watcher) => {
    const entry = entries[0];
    const setElement = Array.from(trackedModals.values()).find((v) => v.modal === entry.target);

    if (!setElement) {
        console.error('No tracked modal found');
        return;
    }

    if (entry.target.classList.contains('show')) {
        return;
    }

    $(setElement.modal).detach();
    $(setElement.parent).append(entry.target);
});

$('.relations').on('click', '[data-modal]', function (e) {
    e.preventDefault();
    const modal = $(this).closest('.add-relation').length ? $(this).closest('.add-relation').next('#relation-add-modal') : $(this).next('#relation-add-modal');
    const iFrame = modal.find('iframe');
    const parent = modal.parent()[0];
    const modalEl = modal[0];

    modal.find('.modal-title').text($(this).data('title'));

    modal.detach();
    $('body').append(modal);

    const trackedData = {
        parent: parent,
        modal: modalEl,
    };

    if (!trackedModals.has(trackedData)) {
        trackedModals.add(trackedData);
        becomesHiddenObserver.observe(modalEl, {attributes: true});
    }

    iFrame.css('display', 'block').attr('src', $(this).data('href')).on('load', function () {
        iFrame.show();

        window.popupShown = true;

        modal.addClass('close-outside show');
        $('#dropdown_overlay').removeClass('hide');

        iFrame.contents().find('*[data-dismiss="modal"]').click((ev) => ev.preventDefault());

        iFrame.unbind('load');
    });
});

$('button[data-dismiss="modal"]').on('click', function () {
    $(this).closest('#relation-add-modal').removeClass('show');
    $('#dropdown_overlay').addClass('hide');
});
