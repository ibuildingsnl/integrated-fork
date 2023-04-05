$(function() {
    $('.sortable-collection').sortable({
        placeholder: "ui-state-highlight"
    });

    function updateAttributes(index) {
        var hiddenInput = $(this).find('input[data-itemorder="collection"]');
        if (hiddenInput.length > 0) {
            hiddenInput.val(index);
        } else {
            var inputs = $(this).find('input, select, textarea');
            if (inputs.length > 0) {
                inputs.each(function() {
                    var newIndex = $(this).attr('name').replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newIndex);
                });
            }
        }
    }

    $('.sortable-collection').closest('form').submit(function() {
        $('ul.ui-sortable > li').each(function(index) {
            updateAttributes.call(this, index);
        });
    });

    $('.sortable-collection').on('sortupdate', function() {
        $('ul.ui-sortable > li').each(function(index) {
            updateAttributes.call(this, index);
        });
    });
});
