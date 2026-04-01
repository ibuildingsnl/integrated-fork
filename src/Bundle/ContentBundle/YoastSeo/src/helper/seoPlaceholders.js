const SEO_PLACEHOLDER_SEPARATOR = '|';

function normalizeText(value) {
    return typeof value === 'string' ? value.trim() : '';
}

function normalizeSlug(value) {
    const normalized = normalizeText(value);

    if (!normalized) {
        return '';
    }

    try {
        const url = new URL(normalized, 'https://placeholder.local');
        return (url.pathname || normalized).replace(/^\/+|\/+$/g, '');
    } catch (error) {
        return normalized.replace(/^\/+|\/+$/g, '');
    }
}

function replacementMap(values = {}) {
    return {
        '%%title%%': normalizeText(values.title),
        '%%site_title%%': normalizeText(values.siteTitle),
        '%%separator%%': normalizeText(values.separator) || SEO_PLACEHOLDER_SEPARATOR,
        '%%slug%%': normalizeSlug(values.slug),
        '%%channel%%': normalizeText(values.channel),
    };
}

function placeholderName(token = '') {
    return String(token).replace(/^%%|%%$/g, '');
}

export function containsSeoPlaceholder(value = '') {
    return /%%(title|site_title|separator|slug|channel)%%/.test(value);
}

export function resolveSeoPlaceholders(value = '', values = {}) {
    let resolved = String(value || '');
    const replacements = replacementMap(values);

    Object.keys(replacements).forEach((token) => {
        resolved = resolved.split(token).join(replacements[token]);
    });

    return resolved.replace(/[ \t]{2,}/g, ' ').replace(/\s+\|$/, '').replace(/^\|\s+/, '').trim();
}

export function seoPlaceholderDefinitions(configuration = {}) {
    return Array.isArray(configuration.seoPlaceholders) ? configuration.seoPlaceholders : [];
}

export function mapSeoPlaceholdersToReplacementVariables(configuration = {}, values = {}) {
    const replacements = replacementMap(values);

    return seoPlaceholderDefinitions(configuration).map((placeholder) => ({
        name: placeholderName(placeholder.token),
        value: replacements[placeholder.token] || '',
        label: placeholder.label,
        description: placeholder.description || '',
    }));
}
