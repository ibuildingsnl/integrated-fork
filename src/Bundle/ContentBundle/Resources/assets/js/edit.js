import './main';
import './collection'
import './handlebars.helpers';
import './relation';
import './unlock_article';

import 'jquery-datetimepicker';

$(function() {
    $('.form_datetime').each(function(index, elm) {
        const $input = $(elm).children('input').eq(0), $clearButton = $(elm).find('.remove');

        $input.datetimepicker({
            format: $(elm).data('dateFormat'),
            locale: $(elm).data('locale')
        });

        $clearButton.click(function () {
            $input.datetimepicker('reset'); //support hide,show and destroy command
        });
    });
});
