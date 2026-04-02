window.IntegratedJQReady(function ($) {
    var $primaryChannel = $('.primary-channel'),
        $channelInputs = $('.channel-options input'),
        TINYMCE_BRAND_THEME_EVENT = 'integrated:editor-brand-theme',
        $primarySelector = $('<a href="#">').addClass('primary-channel-selector')
            .text(' (' + $primaryChannel.data('make-primary-text') + ')');

    updateChannelSelectors();
    syncTinyMceBrandTheme();

    function updateChannelSelectors()
    {
        $('.primary-channel-selector').remove();
        $('.is-primary-channel').removeClass('is-primary-channel');

        $channelInputs.each(function() {
            var $input = $(this);

            if ($input.is(':checked')) {
                if ($primaryChannel.val() == $input.val()) {
                    $input.parent().addClass('is-primary-channel');
                } else {
                    $input.parent().append($primarySelector.clone());
                }
            }
        });

        syncTinyMceBrandTheme();
    }

    function getPrimaryChannelInput() {
        var primaryValue = String($primaryChannel.val() || '').trim();
        var $selectedInput = $();

        if (primaryValue !== '') {
            $selectedInput = $channelInputs.filter(':checked').filter(function() {
                return String($(this).val() || '').trim() === primaryValue;
            }).first();
        }

        if ($selectedInput.length === 0) {
            $selectedInput = $channelInputs.filter(':checked').first();
        }

        return $selectedInput;
    }

    function buildTinyMceContentStyle($input) {
        if (!$input || !$input.length) {
            return '';
        }

        var accent = String($input.attr('data-channel-brand-color') || '').trim();
        var secondary = String($input.attr('data-channel-brand-secondary-color') || accent).trim();
        var accentDark = String($input.attr('data-channel-brand-color-dark') || '').trim();
        var secondaryDark = String($input.attr('data-channel-brand-secondary-color-dark') || accentDark).trim();

        if (!accent || !secondary || !accentDark || !secondaryDark) {
            return '';
        }

        return ':root{'
            + '--editor-color-primary:' + accent + ';'
            + '--editor-color-secondary:' + secondary + ';'
            + '--editor-color-primary-dark:' + accentDark + ';'
            + '--editor-color-secondary-dark:' + secondaryDark + ';'
            + '}';
    }

    function syncTinyMceBrandTheme() {
        var contentStyle = buildTinyMceContentStyle(getPrimaryChannelInput());
        if (!contentStyle) {
            return;
        }

        document.dispatchEvent(new CustomEvent(TINYMCE_BRAND_THEME_EVENT, {
            detail: {
                contentStyle: contentStyle
            }
        }));
    }

    $channelInputs.change(function () {
        var $input = $(this);

        //the checkbox has been unchecked
        if (!$input.is(':checked')) {
            removePrimary($input);
        } else {
            checkIfOtherInputFieldsAreChecked($input);
        }

        updateChannelSelectors();
    });

    $(document).on('click', '.primary-channel-selector', function () {
        makePrimary($(this).parent().find('input'));

        updateChannelSelectors();

        //don't follow the link
        return false;
    });

    function makePrimary($input) {
        $primaryChannel.val($input.val());
    }

    function removePrimary ($input) {
        if ($input.parent().hasClass('is-primary-channel')) {
            $primaryChannel.val('');
        }

        checkIfOtherInputFieldsAreChecked($input);
    }

    function checkIfOtherInputFieldsAreChecked($input) {
        $selectedChannelInputs = $('.channel-options input:checked').not($input);
        //if only one other item is checked then that should be the new primary
        if (1 === $selectedChannelInputs.length) {
            makePrimary($selectedChannelInputs.first());
        } else if (0 ===  $selectedChannelInputs.length && $input.is(':checked')) {
            makePrimary($input);
        }
    }
});
