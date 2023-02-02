$(function() {
    var $control = $('.login-visible-control');

    $control.change(function() {
        var $wrap = $('.integrated-user-form');
        var $parent = $(this).closest('.form-group');

        var items = $wrap.find('.form-item').not($parent);
        if ($(this).is(':checked')) {
            items.show();
        } else {
            items.hide();
        }
    }).trigger('change');
});
