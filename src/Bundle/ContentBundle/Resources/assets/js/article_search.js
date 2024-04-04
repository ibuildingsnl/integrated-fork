import tinymce from "tinymce";

tinymce.PluginManager.add('articlelinksearch', (editor, url) => {
    const hostname = window.location.hostname;
    const protocol = window.location.protocol;

    const openDialog = function (data) {
        return editor.windowManager.openUrl({
            title: 'Find Article',
            url: `${protocol}//${hostname}/admin/article-search?data=${encodeURIComponent(JSON.stringify(data))}`,
            width: 900,
            height: 600
        });
    }

    editor.ui.registry.addButton('integratedArticleLinkSearch', {
        text: 'Find article',
        icon: 'link',
        onAction: () => {
            const dialog = openDialog({
                selectionText: editor.selection.getContent({format: "text"})
            });

            return dialog;
        },
    });

    editor.ui.registry.addMenuItem('integratedArticleLinkSearch', {
        icon: 'link',
        text: 'Find article',
        onAction: () => {
            const dialog = openDialog({
                selectionText: editor.selection.getContent({format: "text"})
            });

            return dialog;
        },
    });

    return {
        getMetadata: function () {
            return {
                name: 'Article Link Searching',
            };
        },
    };
});
