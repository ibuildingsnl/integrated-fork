function initContentIndexLockPolling() {
    var POLL_DELAY = 30000;
    var STORE_KEY = '__integratedContentNavigatorLockPolling';
    var BOOTSTRAP_KEY = '__integratedContentNavigatorLockBootstrap';

    var initLockPolling = function () {
        var tableBody = document.getElementById('post-list');
        if (!tableBody) {
            return;
        }

        var endpoint = tableBody.getAttribute('data-lock-endpoint') || '';
        var lockedText = tableBody.getAttribute('data-locked-text') || 'This item is locked';
        var lockedByText = tableBody.getAttribute('data-locked-by-text') || 'This item is locked by';

        if (!endpoint) {
            return;
        }

        var rows = Array.prototype.slice.call(tableBody.querySelectorAll('tr[data-lock-resource-type][data-lock-resource-id]'));
        if (!rows.length) {
            return;
        }

        var existing = window[STORE_KEY];
        if (existing && typeof existing.stop === 'function') {
            existing.stop();
        }

        var state = {
            timer: null,
            stopped: false,
        };
        window[STORE_KEY] = state;

        var resources = rows.map(function (row) {
            return {
                type: row.getAttribute('data-lock-resource-type'),
                id: row.getAttribute('data-lock-resource-id'),
            };
        });

        var toKey = function (type, id) {
            return String(type) + '|' + String(id);
        };
        var isDocumentHidden = function () {
            return document.visibilityState === 'hidden';
        };

        var renderLock = function (row, lockData) {
            var slot = row.querySelector('[data-lock-slot]');
            if (!slot) {
                return;
            }

            slot.innerHTML = '';
            if (!lockData) {
                return;
            }

            var info = document.createElement('div');
            info.className = 'locked-info';

            var text = document.createElement('span');
            text.className = 'locked-text';

            var icon = document.createElement('i');
            icon.className = 'iconoir-lock';
            text.appendChild(icon);
            text.appendChild(document.createTextNode('\u00A0'));

            if (lockData.user) {
                text.appendChild(document.createTextNode(lockedByText + ' ' + lockData.user));
            } else {
                text.appendChild(document.createTextNode(lockedText));
            }

            info.appendChild(text);
            slot.appendChild(info);
        };

        var updateLocks = function (locks) {
            rows.forEach(function (row) {
                var key = toKey(row.getAttribute('data-lock-resource-type'), row.getAttribute('data-lock-resource-id'));
                renderLock(row, locks[key] || null);
            });
        };

        var scheduleNext = function () {
            if (state.stopped || isDocumentHidden()) {
                return;
            }

            state.timer = window.setTimeout(fetchLocks, POLL_DELAY);
        };

        var stop = function () {
            if (state.stopped) {
                return;
            }

            state.stopped = true;
            if (state.timer) {
                window.clearTimeout(state.timer);
                state.timer = null;
            }

            document.removeEventListener('turbo:before-cache', onBeforeCache);
            window.removeEventListener('pagehide', onPageHide);
            document.removeEventListener('visibilitychange', onVisibilityChange);
            if (window[STORE_KEY] === state) {
                delete window[STORE_KEY];
            }
        };

        var fetchLocks = function () {
            if (state.timer) {
                window.clearTimeout(state.timer);
                state.timer = null;
            }

            if (state.stopped || isDocumentHidden()) {
                return;
            }

            fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({resources: resources}),
            }).then(function (response) {
                if (!response.ok) {
                    throw new Error('Lock status polling failed');
                }

                return response.json();
            }).then(function (payload) {
                if (!payload || typeof payload !== 'object' || !payload.locks || typeof payload.locks !== 'object') {
                    return;
                }

                updateLocks(payload.locks);
            }).catch(function () {
                // Keep current UI state on network errors.
            }).finally(function () {
                scheduleNext();
            });
        };

        var onBeforeCache = function () {
            stop();
        };
        var onPageHide = function () {
            stop();
        };
        var onVisibilityChange = function () {
            if (state.stopped) {
                return;
            }

            if (isDocumentHidden()) {
                if (state.timer) {
                    window.clearTimeout(state.timer);
                    state.timer = null;
                }

                return;
            }

            if (!state.timer) {
                fetchLocks();
            }
        };

        state.stop = stop;

        document.addEventListener('turbo:before-cache', onBeforeCache);
        window.addEventListener('pagehide', onPageHide);
        document.addEventListener('visibilitychange', onVisibilityChange);
        fetchLocks();
    };

    if (!window[BOOTSTRAP_KEY]) {
        document.addEventListener('turbo:load', initLockPolling);
        document.addEventListener('turbo:frame-load', function (event) {
            if (event && event.target && event.target.id === 'content-navigator') {
                initLockPolling();
            }
        });
        window[BOOTSTRAP_KEY] = true;
    }

    initLockPolling();
}

document.addEventListener('DOMContentLoaded', initContentIndexLockPolling);
document.addEventListener('turbo:load', initContentIndexLockPolling);
