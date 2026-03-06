(function() {
    function initPublishedTitle() {
        var block = document.getElementById('integrated_block_block');
        if (!block) {
            return;
        }

        var useTitle = block.querySelector('.use-title');
        var title = block.querySelector('.main-title');
        var publishedTitle = block.querySelector('.published-title');
        if (!useTitle || !title || !publishedTitle) {
            return;
        }

        var publishedFormRow = publishedTitle.closest('.form-item');
        var useTitleFormRow = useTitle.closest('.form-item');

        var applyUseTitleState = function(checked) {
            if (publishedFormRow) {
                publishedFormRow.style.display = checked ? 'none' : '';
            }
            if (useTitleFormRow) {
                useTitleFormRow.style.display = '';
            }
        };

        var compareTitles = function(atStart) {
            if (title.value === publishedTitle.value) {
                useTitle.checked = true;
                applyUseTitleState(true);
            } else if (atStart && useTitleFormRow) {
                useTitleFormRow.style.display = 'none';
                if (publishedFormRow) {
                    publishedFormRow.style.display = '';
                }
            }
        };

        compareTitles(true);
        if (useTitleFormRow && useTitleFormRow.style.display !== 'none') {
            applyUseTitleState(!!useTitle.checked);
        }

        if (!useTitle.dataset.boundPublishedTitleChange) {
            useTitle.addEventListener('change', function() {
                applyUseTitleState(this.checked);
            });
            useTitle.dataset.boundPublishedTitleChange = 'true';
        }
    }

    function initEditIdState() {
        if (!document.body.classList.contains('integrated_block_block_edit')) {
            return;
        }

        var blockId = document.getElementById('block_edit_id');
        if (blockId) {
            blockId.classList.add('disabled');
            blockId.disabled = true;
        }
    }

    function init() {
        initPublishedTitle();
        initEditIdState();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    document.addEventListener('turbo:frame-load', function(e) {
        if (!e.target || e.target.id !== 'integrated-block-editor-frame') {
            return;
        }

        init();
    });
})();
