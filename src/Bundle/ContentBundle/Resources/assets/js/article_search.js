import tinymce from "tinymce";

tinymce.PluginManager.add('articlelinksearch', (editor, url) => {
    const hostname = window.location.hostname;
    const protocol = window.location.protocol;

    const openDialog = function () {
        let data;
        let selectedNode = editor.selection.getNode();
        let anchorNode = null;

        if (selectedNode.nodeName.toUpperCase() === 'A') {
            anchorNode = selectedNode;
        } else if (selectedNode.parentNode && selectedNode.parentNode.nodeName.toUpperCase() === 'A') {
            anchorNode = selectedNode.parentNode;
        }

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
                        if(selectedNode.nodeName.toLocaleUpperCase() === 'A') {
                            selectedNode.innerText = data.linkText;
                            editor.dom.setAttrib(selectedNode, 'href', data.href);
                            editor.dom.setAttrib(selectedNode, 'title', data.title || null);
                            editor.dom.setAttrib(selectedNode, 'target', data.newTab ? '_blank' : '');
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

             if (element.nodeName.toLocaleUpperCase() === 'A') {
                 items.push('integratedArticleLinkSearch');
                 items.push('unlink');
             } else {
                 if(element.nodeName.toLocaleUpperCase() === 'P' || element.parentElement.nodeName.toLocaleUpperCase() === 'P') {
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
