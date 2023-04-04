$(function() {
    $('.sortable-collection').sortable({
        placeholder: "ui-state-highlight"
    });

    function updateAttributes(index) {
        var select = $(this).find('select');
        if (select.length > 0) {
            var newIndex = select.attr('name').replace(/\[\d+\]/, '[' + index + ']');
            select.attr('name', newIndex);
        }

        var hiddenInput = $(this).find('input[data-itemorder="collection"]');
        if (hiddenInput.length > 0) {
            hiddenInput.val(index);
        }
    }

    $('.sortable-collection').closest('form').submit(function() {
        $('ul.ui-sortable li').each(function(index) {
            updateAttributes.call(this, index);
        });
    });

    $('.sortable-collection').on('sortupdate', function() {
        $('ul.ui-sortable li').each(function(index) {
            updateAttributes.call(this, index);
        });
    });
});
