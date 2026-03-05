$(document).ready(function () {
    const form = $('form.content-form');
    const modal = $('#content-edit-modal');
    const autosaveState = {
        saveUrl: '',
        getUrl: '',
        deleteUrl: '',
        enabled: false,
        timer: null,
        intervalId: null,
        inFlight: false,
        lastHash: '',
        statusNode: null,
    };

    if (!form.length) {
        return;
    }

    autosaveState.saveUrl = String(form.attr('data-draft-save-url') || '');
    autosaveState.getUrl = String(form.attr('data-draft-get-url') || '');
    autosaveState.deleteUrl = String(form.attr('data-draft-delete-url') || '');
    autosaveState.enabled = autosaveState.saveUrl !== '' && autosaveState.getUrl !== '' && autosaveState.deleteUrl !== '';

    form.data('changed', false);
    initFormChangeObservation();
    initFormButtons();
    initAutosave();

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
        scheduleAutosave();
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

    function initAutosave() {
        if (!autosaveState.enabled) {
            return;
        }

        createAutosaveStatusNode();
        loadDraftOnInit();

        autosaveState.intervalId = window.setInterval(function () {
            saveDraftIfNeeded(false, false);
        }, 30000);

        window.addEventListener('pagehide', function () {
            saveDraftIfNeeded(true, true);
        });
    }

    function initFormButtons() {
        $('button', form).on('click', function () {
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
            clearDraft();
            setAutosaveStatus('All changes saved', 'ok');
        });

        window.onbeforeunload = function () {
            if (form.data('changed')) {
                saveDraftIfNeeded(true, true);
                return 'You have unsaved changes. When you leave this page your changes will be lost.';
            }
        };
    }

    function clearDraft() {
        if (!autosaveState.enabled || autosaveState.deleteUrl === '') {
            return;
        }

        fetch(autosaveState.deleteUrl, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        }).catch(function () {
            // Ignore clear failures; autosave will overwrite on next edit.
        });
    }

    function createAutosaveStatusNode() {
        if (autosaveState.statusNode) {
            return autosaveState.statusNode;
        }

        const saveButton = document.getElementById('integrated_content_actions_save');
        if (!saveButton || !saveButton.parentNode) {
            return null;
        }

        const status = document.createElement('span');
        status.className = 'content-autosave-status';
        status.style.marginLeft = '.75rem';
        status.style.fontSize = '.85rem';
        status.style.opacity = '.9';
        status.textContent = '';
        saveButton.parentNode.appendChild(status);
        autosaveState.statusNode = status;

        return status;
    }

    function setAutosaveStatus(message, mode) {
        const node = createAutosaveStatusNode();
        if (!node) {
            return;
        }

        node.textContent = message || '';
        node.style.color = mode === 'error' ? '#b73d3d' : '#6b7280';
    }

    function shouldSkipField(name) {
        if (!name) {
            return true;
        }

        return (
            name.indexOf('[actions]') !== -1
            || name.indexOf('[returnUrl]') !== -1
            || name.indexOf('[_token]') !== -1
        );
    }

    function collectFormPayload() {
        const formElement = form.get(0);
        if (!formElement) {
            return {};
        }

        const payload = {};
        const formData = new FormData(formElement);

        for (const pair of formData.entries()) {
            const name = pair[0];
            const value = pair[1];

            if (shouldSkipField(name) || value instanceof File) {
                continue;
            }

            if (!Object.prototype.hasOwnProperty.call(payload, name)) {
                payload[name] = [];
            }

            payload[name].push(String(value));
        }

        form.find('input[type="checkbox"][name], input[type="radio"][name], select[multiple][name]').each(function () {
            const name = this.name;
            if (shouldSkipField(name) || Object.prototype.hasOwnProperty.call(payload, name)) {
                return;
            }

            payload[name] = [];
        });

        return payload;
    }

    function applyDraftPayload(payload) {
        if (!payload || typeof payload !== 'object') {
            return;
        }

        Object.keys(payload).forEach(function (name) {
            const values = Array.isArray(payload[name]) ? payload[name].map(String) : [];
            const selector = '[name="' + escapeSelectorValue(name) + '"]';
            const fields = form.find(selector);

            if (!fields.length || shouldSkipField(name)) {
                return;
            }

            fields.each(function () {
                const field = this;
                const tag = (field.tagName || '').toLowerCase();
                const type = String(field.type || '').toLowerCase();

                if (type === 'checkbox' || type === 'radio') {
                    field.checked = values.indexOf(String(field.value)) !== -1;
                    return;
                }

                if (tag === 'select' && field.multiple) {
                    Array.from(field.options).forEach(function (option) {
                        option.selected = values.indexOf(String(option.value)) !== -1;
                    });
                    return;
                }

                field.value = values.length ? values[0] : '';
            });

            fields.trigger('change');
            fields.trigger('input');
        });
    }

    function escapeSelectorValue(value) {
        return String(value).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
    }

    function saveDraftIfNeeded(force, keepalive) {
        if (!autosaveState.enabled || autosaveState.inFlight) {
            return;
        }

        if (!force && !form.data('changed')) {
            return;
        }

        if (String(form.attr('data-content-locked') || '') === '1') {
            return;
        }

        const payload = collectFormPayload();
        const payloadHash = JSON.stringify(payload);
        if (!force && payloadHash === autosaveState.lastHash) {
            return;
        }

        autosaveState.inFlight = true;
        setAutosaveStatus('Saving draft...', 'pending');

        fetch(autosaveState.saveUrl, {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: !!keepalive,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ payload: payload }),
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('autosave failed');
                }

                return response.json();
            })
            .then(function () {
                autosaveState.lastHash = payloadHash;
                setAutosaveStatus('Draft saved', 'ok');
            })
            .catch(function () {
                setAutosaveStatus('Draft save failed', 'error');
            })
            .finally(function () {
                autosaveState.inFlight = false;
            });
    }

    function scheduleAutosave() {
        if (!autosaveState.enabled) {
            return;
        }

        if (autosaveState.timer) {
            window.clearTimeout(autosaveState.timer);
        }

        autosaveState.timer = window.setTimeout(function () {
            saveDraftIfNeeded(false, false);
        }, 1200);
    }

    function loadDraftOnInit() {
        if (!autosaveState.enabled) {
            return;
        }

        fetch(autosaveState.getUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
            .then(function (response) {
                if (!response.ok) {
                    return null;
                }

                return response.json();
            })
            .then(function (data) {
                if (!data || !data.exists || !data.payload || form.data('changed')) {
                    return;
                }

                if (!window.confirm('A draft was found for this item. Do you want to restore it?')) {
                    return;
                }

                applyDraftPayload(data.payload);
                autosaveState.lastHash = JSON.stringify(collectFormPayload());
                markFormChanged();
                setAutosaveStatus('Draft restored', 'ok');
            })
            .catch(function () {
                // Ignore recover errors to avoid interrupting editing.
            });
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

        saveDraftIfNeeded(true, true);
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
