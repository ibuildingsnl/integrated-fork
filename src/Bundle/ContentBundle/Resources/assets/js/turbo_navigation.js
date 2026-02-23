export function visitWithTurbo(url, options) {
    if (window.Turbo && typeof window.Turbo.visit === 'function') {
        window.Turbo.visit(url, options);
        return;
    }

    window.location.href = url;
}

export function reloadWithTurbo() {
    if (window.Turbo && typeof window.Turbo.visit === 'function') {
        window.Turbo.visit(window.location.href, {action: 'replace'});
        return;
    }

    window.location.reload();
}
