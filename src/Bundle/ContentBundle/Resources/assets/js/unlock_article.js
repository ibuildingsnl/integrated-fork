// import 'bootstrap-sass';

/* This javascript blocks navigation on the block edit / new block pages. */

const form = $('form.content-form');
const modal = $('#content-edit-modal');

if (modal.length && form.length) {
    /* handle BACK|FORWARD buttons in browser */
    history.pushState(null, null, location.href);
    window.onpopstate = function(e) {
        $(':focus').blur();
        if ($('form.content-form').data('changed')) {
            $('.live-page', modal).data('return_url', document.referrer);
            modal.show();
            $(document.body).
                append('<div class="modal-backdrop fade in"></div>');
        } else {
            leavePage(document.referrer);
        }
        //stay on the page
        history.pushState(null, null, location.href);
    };

    /* observe form for changes */
    form.on('change', function() {
        form.data('changed', true);
    });

    $(function() {
        if (typeof tinymce !== 'undefined' && tinymce.activeEditor !== null) {
            /* this works for one editor, not for multiple */
            tinymce.activeEditor.on('Change', function(e) {
                form.data('changed', true);
            });
        }
    });

    //var is set in view
    if (global.formInvalid) {
        form.data('changed', true);
    }

    /* ask user before leave page via href links and unlock article */
    $('a:not(form.content-form a), form.content-form button[name*=cancel]').
        on('click', function(e) {
            const url = $(this).attr('href');

            if (url && url !== '#') {
                if ($(this).attr('target') === '_blank') {
                    return;
                }

                e.preventDefault();

                if (form.data('changed')) {
                    $('.live-page', modal).data('return_url', url);
                    modal.show();
                    $(document.body).
                        append('<div class="modal-backdrop fade in"></div>');
                } else {
                    leavePage(url);
                }
            } else if ($(this).is('button[name*=cancel]', form) &&
                form.data('changed')) {
                e.preventDefault();
                modal.show();
                $(document.body).
                    append('<div class="modal-backdrop fade in"></div>');
            }
        });

    /* handle "leave page button" in modal */
    $('.live-page', modal).on('click', function() {
        modal.hide();
        $('.modal-backdrop').remove();
        leavePage($(this).data('return_url'));
    });

    /* handle "Stay on page" in modal */
    $('button[data-dismiss="modal"]', modal).on('click', function() {
        modal.hide();
        $('.modal-backdrop').remove();
    });

    function leavePage(returnUrl) {
        $('.return-url', form).val(returnUrl);

        window.onbeforeunload = null;
        form.data('changed', false);
        $('[name*=cancel]', form).trigger('click');
    }

    $('button', form).on('click', function() {
        window.onbeforeunload = null;
    });

    window.onbeforeunload = function() {
        if (form.data('changed')) {
            return 'You have unsaved changes. When you leave this page your changes will be lost.';
        }
    };
}

