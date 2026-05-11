import tinymce from "tinymce";

tinymce.PluginManager.add('articlelinksearch', (editor, url) => {
    const hostname = window.location.hostname;
    const protocol = window.location.protocol;

    const findClosestAnchor = function (node) {
        let element = null;

        if (node) {
            element = node.nodeType === 1 ? node : node.parentElement;

            if (!element && node.parentNode && node.parentNode.nodeType === 1) {
                element = node.parentNode;
            }
        }

        while (element) {
            if (element.nodeName.toUpperCase() === 'A') {
                return element;
            }

            element = element.parentElement;
        }

        return null;
    };

    const openDialog = function () {
        let data;
        let selectedNode = editor.selection.getNode();
        let anchorNode = findClosestAnchor(selectedNode);

        if (anchorNode) {
            data = {
                selectionText: anchorNode.textContent,
                title: anchorNode.getAttribute('title'),
                url: anchorNode.getAttribute('href'),
                openInNewTab: (anchorNode.getAttribute('target') ?? '') === '_blank',
                existing: true,
            };
        } else {
            data = {
                selectionText: editor.selection.getContent({format: "text"}),
            };
        }

        const window = editor.windowManager.openUrl({
            title: 'Link maker',
            url: `${protocol}//${hostname}/admin/article-search?data=${encodeURIComponent(JSON.stringify(data))}`,
            width: 900,
            height: 600,
            onMessage(instance, data) {
                switch(data.mceAction) {
                    case 'linkMakerReplace':
                        if(anchorNode) {
                            anchorNode.textContent = data.linkText;
                            editor.dom.setAttrib(anchorNode, 'href', data.href);
                            editor.dom.setAttrib(anchorNode, 'title', data.title || null);
                            editor.dom.setAttrib(anchorNode, 'target', data.newTab ? '_blank' : '');
                            break;
                        }
                        break;
                }
            }
        });

        return window;
    }

    editor.ui.registry.addButton('integratedArticleLinkSearch', {
        icon: 'link',
        onAction: () => {
            return openDialog();
        },
    });

    editor.ui.registry.addMenuItem('integratedArticleLinkSearch', {
        icon: 'link',
        text: 'Link',
        onAction: () => {
            return openDialog();
        },
    });

    editor.ui.registry.addContextMenu('integratedArticleLinkSearch', {
        update: (element) => {
            let items = [];
            let anchor = findClosestAnchor(element);
            let elementName = element && element.nodeName ? element.nodeName.toUpperCase() : '';
            let parentName = element && element.parentElement && element.parentElement.nodeName
                ? element.parentElement.nodeName.toUpperCase()
                : '';

            if (anchor) {
                items.push('integratedArticleLinkSearch');
                items.push('unlink');
            } else {
                if(elementName === 'P' || parentName === 'P') {
                    if(editor.selection.getContent({format: "text"}).length === 0) {
                        return;
                    }

                    items.push('integratedArticleLinkSearch');
                }
            }

            return items.join(' ');
        },
    });

    return {};
});
