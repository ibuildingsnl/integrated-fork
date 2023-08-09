$(document).ready(function() {
    const block = $("#integrated_block_block");
    const useTitle = $('.use-title', block);
    const title = $(".main-title", block);
    const publishedTitle = $('.published-title', block);
    const publishedFormRow = publishedTitle.closest(".form-item");
    const useTitleFormRow = useTitle.closest('.form-item');

    const compareTitles = function(atStart) {
        if (title.val() === publishedTitle.val()) {
            useTitle.prop('checked', true);
            useTitleFormRow.show();
            publishedFormRow.hide();
        } else if (atStart) {
            useTitleFormRow.hide();
        }
    };
    compareTitles(true);

    useTitle.on('change', function() {
        if (this.checked) {
            publishedFormRow.hide();
            useTitleFormRow.show();
        }
    });
});

$(document).ready(function() {
    if ($('body').hasClass('integrated_block_block_edit')) {
        $('#block_edit_id').addClass('disabled');
    }
});
