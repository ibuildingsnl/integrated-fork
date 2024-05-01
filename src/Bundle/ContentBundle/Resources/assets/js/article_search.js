import tinymce from "tinymce";

tinymce.PluginManager.add('articlelinksearch', (editor, url) => {
    const hostname = window.location.hostname;
    const protocol = window.location.protocol;

    const openDialog = function () {
        const selectedNode = editor.selection.getNode();
        let data;

        if(selectedNode.nodeName.toLocaleUpperCase() === 'A') {
            data = {
                selectionText: selectedNode.innerText,
                url: selectedNode.href,
                openInNewTab: (selectedNode.target ?? '') === '_blank',
            };
        } else {
            data = {
                selectionText: editor.selection.getContent({format: "text"}),
            };
        }

        return editor.windowManager.openUrl({
            title: 'Link maker',
            url: `${protocol}//${hostname}/admin/article-search?data=${encodeURIComponent(JSON.stringify(data))}`,
            width: 900,
            height: 600
        });
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
