$(document).ready(function () {
    const form = $('form.content-form');
    const modal = $('#content-edit-modal');

    if (!form.length) {
        return;
    }

    form.data('changed', false);
    initFormChangeObservation();
    initFormButtons();

    if (modal.length) {
        initPageNavigationHandling();
        initLeavePageHandling();
        initModalActions();
    }

    function initPageNavigationHandling() {
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            $(':focus').blur();
            if (form.data('changed')) {
                setModalReturnUrl(document.referrer);
                showModal();
            } else {
                leavePage(document.referrer);
            }
            history.pushState(null, null, location.href);
        };
    }

    function markFormChanged() {
        form.data('changed', true);
    }

    function initFormChangeObservation() {
        form.on('change input', function (event) {
            if (!event || !event.originalEvent) {
                return;
            }

            markFormChanged();
        });

        if (typeof tinymce !== 'undefined' && Array.isArray(tinymce.editors)) {
            tinymce.editors.forEach(function (editor) {
                if (!editor || typeof editor.on !== 'function') {
                    return;
                }

                editor.on('Change', function () {
                    if (typeof editor.isDirty === 'function' && !editor.isDirty()) {
                        return;
                    }

                    markFormChanged();
                });
            });
        }

        if (window.formInvalid) {
            markFormChanged();
        }
    }

    function initLeavePageHandling() {
        $('a:not(form.content-form a), form.content-form button[name*=cancel]').on('click', function (e) {
            if (e.ctrlKey || e.metaKey) {
                return;
            }

            const url = $(this).attr('href');

            if (url && url !== '#') {
                if ($(this).attr('target') === '_blank') {
                    return;
                }

                e.preventDefault();

                if (form.data('changed')) {
                    setModalReturnUrl(url);
                    showModal();
                } else {
                    leavePage(url);
                }
            } else if ($(this).is('button[name*=cancel]', form) && form.data('changed')) {
                e.preventDefault();
                showModal();
            }
        });
    }

    function initModalActions() {
        $('.live-page', modal).on('click', function () {
            closeModal();
            leavePage($(this).data('return_url'));
        });

        $('button[data-dismiss="modal"]', modal).on('click', function () {
            closeModal();
        });
    }

    function initFormButtons() {
        $('button[type="submit"]', form).on('click', function () {
            window.onbeforeunload = null;
        });

        document.addEventListener('turbo:submit-end', function (event) {
            if (!event || !event.detail || !event.detail.success) {
                return;
            }

            if (!event.target || event.target !== form.get(0)) {
                return;
            }

            form.data('changed', false);
        });

        window.onbeforeunload = function () {
            if (form.data('changed')) {
                return 'You have unsaved changes. When you leave this page your changes will be lost.';
            }
        };
    }

    function setModalReturnUrl(url) {
        $('.live-page', modal).data('return_url', url);
    }

    function showModal() {
        modal.show();
        $(document.body).append('<div class="modal-backdrop fade in"></div>');
    }

    function closeModal() {
        modal.hide();
        $('.modal-backdrop').remove();
    }

    function leavePage(returnUrl) {
        const returnUrlInput = $('.return-url', form);
        const cancelButton = $('[name*=cancel]', form);

        window.onbeforeunload = null;
        form.data('changed', false);

        // Edit forms can safely leave via cancel + return-url.
        if (returnUrlInput.length && cancelButton.length) {
            returnUrlInput.val(returnUrl);
            cancelButton.trigger('click');

            return;
        }

        // New forms don't always have a return-url field; navigate directly.
        if (returnUrl) {
            window.location.href = returnUrl;
        }
    }
});
