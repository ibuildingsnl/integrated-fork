$(document).ready(function () {
    const form = $('form.content-form');
    const modal = $('#content-edit-modal');
    const autosaveState = {
        saveUrl: '',
        getUrl: '',
        deleteUrl: '',
        enabled: false,
        inFlight: false,
        lastHash: '',
        versions: {},
        versionSelectNode: null,
        restoreButtonNode: null,
        versionsContainerNode: null,
        statusNode: null,
        contentUpdatedAt: '',
    };

    if (!form.length) {
        return;
    }

    autosaveState.saveUrl = String(form.attr('data-draft-save-url') || '');
    autosaveState.getUrl = String(form.attr('data-draft-get-url') || '');
    autosaveState.deleteUrl = String(form.attr('data-draft-delete-url') || '');
    autosaveState.contentUpdatedAt = String(form.attr('data-content-updated-at') || '');
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

        initDraftStatusNode();
        initManualDraftButton();
        initDraftVersionControls();
        loadDraftOnInit();
    }

    function initDraftStatusNode() {
        autosaveState.statusNode = document.getElementById('integrated_content_draft_status');
    }

    function setDraftStatus(message) {
        if (!autosaveState.statusNode) {
            return;
        }

        autosaveState.statusNode.textContent = String(message || '');
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
            clearDraft();
        });

        window.onbeforeunload = function () {
            if (form.data('changed')) {
                return 'You have unsaved changes. When you leave this page your changes will be lost.';
            }
        };
    }

    function initManualDraftButton() {
        const button = document.getElementById('integrated_content_actions_save_draft');
        if (!button || button.getAttribute('data-draft-bound') === '1') {
            return;
        }

        button.setAttribute('data-draft-bound', '1');
        button.addEventListener('click', function (event) {
            event.preventDefault();
            saveDraftIfNeeded(true, false);
        });
    }

    function initDraftVersionControls() {
        autosaveState.versionSelectNode = document.getElementById('integrated_content_actions_draft_version');
        autosaveState.restoreButtonNode = document.getElementById('integrated_content_actions_restore_draft_version');
        autosaveState.versionsContainerNode = document.getElementById('integrated_content_draft_versions');

        if (!autosaveState.versionSelectNode || !autosaveState.restoreButtonNode) {
            return;
        }

        if (autosaveState.versionSelectNode.getAttribute('data-draft-bound') !== '1') {
            autosaveState.versionSelectNode.setAttribute('data-draft-bound', '1');
            autosaveState.versionSelectNode.addEventListener('change', function () {
                setRestoreButtonState();
            });
        }

        if (autosaveState.restoreButtonNode.getAttribute('data-draft-bound') !== '1') {
            autosaveState.restoreButtonNode.setAttribute('data-draft-bound', '1');
            autosaveState.restoreButtonNode.addEventListener('click', function (event) {
                event.preventDefault();
                restoreSelectedVersion();
            });
        }

        setRestoreButtonState();
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
        setDraftStatus('Saving draft...');

        fetch(autosaveState.saveUrl, {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: !!keepalive,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                payload: payload,
                baseContentUpdatedAt: autosaveState.contentUpdatedAt || null,
            }),
        })
            .then(function (response) {
                if (!response.ok) {
                    if (response.status === 409) {
                        return response.json().then(function (data) {
                            throw new Error(data && data.message ? data.message : 'Draft conflict detected');
                        });
                    }

                    throw new Error('autosave failed');
                }

                return response.json();
            })
            .then(function (data) {
                autosaveState.lastHash = payloadHash;
                form.data('changed', false);
                setDraftStatus('Draft saved.');

                if (data && Array.isArray(data.versions)) {
                    populateDraftVersions(data.versions);
                }
                if (data && data.contentUpdatedAt) {
                    autosaveState.contentUpdatedAt = String(data.contentUpdatedAt);
                }
            })
            .catch(function (error) {
                const message = String((error && error.message) || '');
                if (message.toLowerCase().indexOf('conflict') !== -1) {
                    setDraftStatus('Draft conflict detected. Reload editor before continuing.');
                    return;
                }

                setDraftStatus('Draft save failed. Try again.');
            })
            .finally(function () {
                autosaveState.inFlight = false;
            });
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

                applyDraftPayload(data.payload);
                autosaveState.lastHash = JSON.stringify(collectFormPayload());
                form.data('changed', true);
                setDraftStatus('Draft restored.');

                if (Array.isArray(data.versions)) {
                    populateDraftVersions(data.versions);
                }
                if (data.contentUpdatedAt) {
                    autosaveState.contentUpdatedAt = String(data.contentUpdatedAt);
                }
            })
            .catch(function () {
                // Ignore recover errors to avoid interrupting editing.
            });
    }

    function populateDraftVersions(versions) {
        if (!autosaveState.versionSelectNode || !autosaveState.restoreButtonNode) {
            return;
        }

        autosaveState.versions = {};
        autosaveState.versionSelectNode.innerHTML = '<option value="">Current draft</option>';

        (versions || []).forEach(function (version, index) {
            if (!version || typeof version !== 'object' || !version.id) {
                return;
            }

            autosaveState.versions[String(version.id)] = version;

            const option = document.createElement('option');
            option.value = String(version.id);
            option.textContent = formatDraftVersionLabel(version, index);
            autosaveState.versionSelectNode.appendChild(option);
        });

        autosaveState.versionSelectNode.value = '';

        if (autosaveState.versionsContainerNode) {
            const hasVersions = Object.keys(autosaveState.versions).length > 0;
            autosaveState.versionsContainerNode.style.display = hasVersions ? 'flex' : 'none';
        }

        setRestoreButtonState();
    }

    function setRestoreButtonState() {
        if (!autosaveState.restoreButtonNode || !autosaveState.versionSelectNode) {
            return;
        }

        const versionId = String(autosaveState.versionSelectNode.value || '');
        autosaveState.restoreButtonNode.disabled = !(versionId && autosaveState.versions[versionId]);
    }

    function restoreSelectedVersion() {
        if (!autosaveState.versionSelectNode) {
            return;
        }

        const versionId = String(autosaveState.versionSelectNode.value || '');
        if (!versionId || !autosaveState.versions[versionId]) {
            return;
        }

        const version = autosaveState.versions[versionId];
        applyDraftPayload(version.payload || {});
        form.data('changed', true);
        saveDraftIfNeeded(true, false);
    }

    function formatDraftVersionLabel(version, index) {
        const fallback = 'Version ' + String(index + 1);
        if (!version || !version.savedAt) {
            return fallback;
        }

        const parsed = new Date(String(version.savedAt));
        if (Number.isNaN(parsed.getTime())) {
            return fallback;
        }

        return 'Version ' + String(index + 1) + ' - ' + parsed.toLocaleString();
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
