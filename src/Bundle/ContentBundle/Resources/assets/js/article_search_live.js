const ROOT_SELECTOR = '[data-article-search-root]';
const APPLY_SELECTOR = '[data-article-search-apply]';
const CANCEL_SELECTOR = '[data-article-search-cancel]';
const LINK_TEXT_SELECTOR = '[data-article-search-link-text]';
const LINK_TITLE_SELECTOR = '[data-article-search-link-title]';
const TERM_SELECTOR = '[data-article-search-term]';
const NEW_TAB_SELECTOR = '[data-article-search-new-tab]';
const SELECTED_RESULT_SELECTOR = '[data-article-search-selection]:checked';
const RESULT_CONTAINER_SELECTOR = '[data-article-search-result]';

function getOriginFromUrl(url) {
    if (typeof url !== 'string' || url.length === 0) {
        return null;
    }

    try {
        return new URL(url, window.location.origin).origin;
    } catch (error) {
        return null;
    }
}

function getPreferredParentOrigin() {
    return getOriginFromUrl(document.referrer) ?? window.location.origin;
}

function isTrustedParentMessage(event) {
    if (!event || event.source !== window.parent) {
        return false;
    }

    const trustedOrigins = new Set([window.location.origin]);
    const referrerOrigin = getOriginFromUrl(document.referrer);

    if (referrerOrigin) {
        trustedOrigins.add(referrerOrigin);
    }

    return trustedOrigins.has(event.origin);
}

function postToParent(payload) {
    window.parent.postMessage(payload, getPreferredParentOrigin());
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function getRoot() {
    return document.querySelector(ROOT_SELECTOR);
}

function startsWithAny(value, prefixes) {
    return prefixes.some((prefix) => value.startsWith(prefix));
}

function ensureHttps(url) {
    if (startsWithAny(url, ['/', '#', 'mailto:', 'tel:', 'https://', 'http://'])) {
        return url;
    }

    return `https://${url}`;
}

function isSearchTermUrl(searchTerm) {
    if (searchTerm.length === 0) {
        return false;
    }

    if (startsWithAny(searchTerm, ['/', '#', 'mailto:', 'tel:', 'www'])) {
        return true;
    }

    try {
        // eslint-disable-next-line no-new
        new URL(searchTerm);

        return true;
    } catch (error) {
        return false;
    }
}

function shouldOpenInNewTab(url, openInNewTab) {
    return openInNewTab && !startsWithAny(url, ['#', 'mailto:', 'tel:']);
}

function getState(root) {
    const linkText = root.querySelector(LINK_TEXT_SELECTOR)?.value ?? '';
    const linkTitle = root.querySelector(LINK_TITLE_SELECTOR)?.value ?? '';
    const searchTerm = root.querySelector(TERM_SELECTOR)?.value ?? '';
    const openInNewTab = root.querySelector(NEW_TAB_SELECTOR)?.checked ?? false;
    const existing = (root.querySelector(APPLY_SELECTOR)?.dataset.existing ?? '0') === '1';

    return {
        linkText,
        linkTitle,
        searchTerm,
        openInNewTab,
        existing,
    };
}

function getSelectedResultUrl(root) {
    const selection = root.querySelector(SELECTED_RESULT_SELECTOR);

    if (!selection) {
        return '';
    }

    return selection.closest(RESULT_CONTAINER_SELECTOR)?.dataset.url ?? '';
}

function isApplyEnabled(root) {
    const state = getState(root);

    if (state.linkText.length === 0 || state.linkTitle.length === 0) {
        return false;
    }

    if (isSearchTermUrl(state.searchTerm)) {
        return true;
    }

    return getSelectedResultUrl(root).length > 0;
}

function syncApplyState() {
    const root = getRoot();

    if (!root) {
        return;
    }

    const button = root.querySelector(APPLY_SELECTOR);

    if (!button) {
        return;
    }

    const enabled = isApplyEnabled(root);

    button.disabled = !enabled;
    button.classList.toggle('bg-[#007b67]', !enabled);
    button.classList.toggle('disabled:hover:bg-[#007b67]', !enabled);
    button.classList.toggle('text-zinc-300', !enabled);
    button.classList.toggle('cursor-not-allowed', !enabled);
}

function closeIframe() {
    postToParent({
        mceAction: 'close',
    });
}

function insertOrReplaceLink() {
    const root = getRoot();

    if (!root) {
        return;
    }

    const state = getState(root);

    if (state.linkText.length === 0 || state.linkTitle.length === 0) {
        return;
    }

    let url = '';
    const searchTermIsUrl = isSearchTermUrl(state.searchTerm);

    if (searchTermIsUrl) {
        url = ensureHttps(state.searchTerm);
    } else {
        const selectedResultUrl = getSelectedResultUrl(root);

        if (selectedResultUrl.length === 0) {
            return;
        }

        url = ensureHttps(selectedResultUrl);
    }

    if (state.existing) {
        postToParent({
            mceAction: 'linkMakerReplace',
            title: state.linkTitle,
            href: url,
            newTab: shouldOpenInNewTab(url, state.openInNewTab),
            linkText: state.linkText,
        });
    } else {
        const target = shouldOpenInNewTab(url, state.openInNewTab) ? ' target="_blank"' : '';
        const safeLinkText = escapeHtml(state.linkText);
        const safeTitle = escapeHtml(state.linkTitle);
        const safeUrl = escapeHtml(url);

        postToParent({
            mceAction: 'insertContent',
            content: `<a href="${safeUrl}"${target} title="${safeTitle}">${safeLinkText}</a>`,
        });
    }

    closeIframe();
}

function setSelectionTextFromMessage(event) {
    if (!isTrustedParentMessage(event)) {
        return;
    }

    if (!event || typeof event.data !== 'object' || event.data === null) {
        return;
    }

    if (typeof event.data.selectionText !== 'string') {
        return;
    }

    const root = getRoot();

    if (!root) {
        return;
    }

    const input = root.querySelector(LINK_TEXT_SELECTOR);

    if (!input) {
        return;
    }

    input.value = event.data.selectionText;
    input.dispatchEvent(new Event('input', { bubbles: true }));
    syncApplyState();
}

function bindEvents() {
    document.addEventListener('click', (event) => {
        const cancelButton = event.target.closest(CANCEL_SELECTOR);

        if (cancelButton) {
            event.preventDefault();
            closeIframe();

            return;
        }

        const applyButton = event.target.closest(APPLY_SELECTOR);

        if (applyButton) {
            event.preventDefault();
            insertOrReplaceLink();
        }
    });

    document.addEventListener('input', syncApplyState);
    document.addEventListener('change', syncApplyState);
    window.addEventListener('message', setSelectionTextFromMessage);

    const observer = new MutationObserver(() => {
        syncApplyState();
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        bindEvents();
        syncApplyState();
    });
} else {
    bindEvents();
    syncApplyState();
}
