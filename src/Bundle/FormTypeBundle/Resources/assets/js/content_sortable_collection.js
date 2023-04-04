$(function() {
    $('.sortable-collection').sortable({
        placeholder: "ui-state-highlight"
    });

    $('.sortable-collection').closest('form').submit(function() {
        $('.sortable-collection').find('li').each(function(i, element) {
            $(element).find('input[data-itemorder="collection"]').val(i);
        });
    });

    $('.sortable-collection').on('sortupdate', function() {
        // Loop through each <select> element and update its "name" attribute
        $('ul.ui-sortable li select').each(function(index) {
            var newName = $(this).attr('name').replace(/\[\d+\]/, '[' + index + ']');
            $(this).attr('name', newName);
        });
    });
});
