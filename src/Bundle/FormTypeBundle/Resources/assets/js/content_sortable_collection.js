$(function() {
    $('.sortable-collection').sortable({
        placeholder: "ui-state-highlight"
    });

    $('.sortable-collection').closest('form').submit(function() {
        $('.sortable-collection').find('li').each(function(i, element) {
            $(element).find('input[data-itemorder="collection"]').val(i);
        });
    });
});
