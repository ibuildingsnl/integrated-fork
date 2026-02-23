(function initIntegratedCommentBundle() {
    let initialized = false;

    let bootstrap = function() {
        if (initialized || typeof window.jQuery === 'undefined') {
            return initialized;
        }

        initialized = true;
        let $ = window.jQuery;

        $(function () {

    /**
     * Show add comment button
     * @param {string} fieldName
     * @param {object} position
     * @param {jQuery} $parent
     * @param {jQuery} $container
     */
    let showCommentButton = function(fieldName, position, $parent, $container) {
        let $div = $('<div class="add-comment-button btn btn-dark-green hold">Add a Comment</div>');

        $div.mousedown(function(e) {
            newComment(fieldName, position, $parent, $container);
        });

        removeControls();

        $container.append($div);

        positionElement($div, position);
    };

    /**
     * @param {string} fieldName
     * @param {object} position
     * @param {jQuery} $parent
     * @param {jQuery} $container
     */
    let newComment = function (fieldName, position, $parent, $container) {
        $.ajax({
            type: 'GET',
            url: integrated_comment_urls.new.replace('__content__', $('.content-form').data('content-id')).replace('__field__', fieldName),
            success: function(response) {
                showModalComment(response,position, $parent,  $container);
            }
        });
    };

    /**
     * @param {string} commentId
     * @param {object} position
     * @param {jQuery} $parent
     * @param {jQuery} $container
     */
    let showAddedComment = function(commentId, position, $parent, $container) {
        $.ajax({
            type: 'GET',
            url: integrated_comment_urls.get.replace('__comment__', commentId),
            success: function(response) {
                showModalComment(response, position, $parent, $container);
            }
        });
    };

    /**
     * @param {string} response
     * @param {object} position
     * @param {jQuery} $parent
     * @param {jQuery} $container
     */
    let showModalComment = function(response, position, $parent, $container) {
        removeControls();

        let $modal = $(response);
        $container.append($modal);

        positionElement($modal, position);

        $('.fancy_tinymce').
            append('<div class="modal-backdrop fade in"></div>');

        $('form', $modal).bind('submit', function(e){
            e.preventDefault();

            postComment($(this), $parent);
        });

    };

    /**
     * @param {jQuery} $form
     * @param {jQuery} $parent
     */
    let postComment = function ($form, $parent) {
        $.ajax({
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            url: $form.attr("action"),
            success: function (response) {
                removeControls();

                if ($parent) {
                    if (!$parent.data('comment-id')) {
                        $parent.data('comment-id', response.id);
                    }

                    createAddedCommentLine($parent);
                } else {
                    //apply integrated_comment format, adds span tag with data-comment-id
                    tinymce.activeEditor.formatter.apply('integrated_comment', {value : response.id});
                }
            }
        });
    };

    /**
     * Add comment icon in the label of an integrated field
     * @param $parent
     */
    let createAddedCommentLine = function($parent) {
        let commentId = $parent.data('comment-id');


        let $label = false;
        if ($parent.parent().hasClass("editor-item-list-container")) {
            $parent.closest('.editor-item-wrapper').addClass('comment-set');
        } else if ($parent.parent().hasClass("aside-item-list-container")) {
            $parent.closest('.aside-item-wrapper').addClass('comment-set');
        } else if ($parent.parent().parent().hasClass("title_tinymce")) {
            $parent.closest('.form-item').addClass('comment-set');
        } else {
            $label = $parent.closest('.form-group').find('label');
            $parent.closest('.form-group').addClass('comment-set');
        }
        // remove existing
        $('.added-comment-line[data-comment-id="' + commentId + '"]').remove();

        if (commentId) {
            let comment = $('<div>').addClass('added-comment-line')
                .attr('data-comment-id', commentId).data('comment-id', commentId)
                .data('parent', $parent)
                .append($('<i>').addClass('iconoir-message-text'));
            if ($label) {
                //if input has label place it inside label
                $label.append(comment)
            } else {
                //else place it after input
                $parent.after(comment);
            }
        }
    };

    /**
     * @param {jQuery} $element
     * @returns {{top: *, left: *}}
     */
    let calculatePosition = function ($element) {
        let offset = $element.offset();
        return {top: (offset.top + $element.outerHeight()), left: offset.left};
    };

    /**
     * positions element with provided position
     *
     * @param {jQuery} $element
     * @param {object} position
     */
    let positionElement = function ($element, position) {
        $element.css({
            'z-index': 9999,
            position: 'absolute',
            top: position.top,
            left: position.left,
        });
    };

    /**
     * Removes all buttons and modals related to comment bundle
     */
    let removeControls = function() {
        $('.comment-holder, .add-comment-button, .modal-backdrop').remove();
    };

    /**
     * @param {string} fullName
     * @returns {string}
     */
    let getFieldName = function(fullName) {
        return /\[(.+)\]$/.exec(fullName)[1];
    };

    function removeCommentButton() {
        $('.add-comment-button').remove();
    }

    //
    //
    // Event listeners
    //
    //

    /**
     * Eventlistener for selecting input and textarea fields
     */
    $('input:text, textarea').bind('select', function (e) {
        if (e.target.selectionStart != e.target.selectionEnd) {
            let position = calculatePosition($(this));
            let $container =  $('body');

            if ($(this).data('comment-id') !== undefined) {
                showAddedComment($(this).data('comment-id'), position, $(this), $container);
            } else {
                showCommentButton(getFieldName($(this).attr('name')), position, $(this), $container);
            }
        }
    }).mousedown(function() {
        removeCommentButton();
    }).focusout(function() {
        removeCommentButton();
    });

    /**
     * Add a commentIcon for all existing comments
     */
    $('[data-comment-id]').each(function() {
        createAddedCommentLine($(this));
    });

    /**
     * Check if someone clicks on a comment icon, if so show the comment modal
     */
    $(document).on('click', '.added-comment-line', function (e) {
        e.preventDefault();

        showAddedComment($(this).data('comment-id'), calculatePosition($(this)), $(this).data('parent'), $('body'));
    });

    /**
     * Check if delete button is clicked in modal
     */
    $(document).on('click', '.comment-holder .delete-comment', function (e) {
        e.preventDefault();

        $.get($(this).attr('href'), function (data) {
            removeControls();

            $('.added-comment-line[data-comment-id="' + data.id + '"]').remove();
            if (tinymce.activeEditor != undefined) {
                $('.integrated-comment[data-comment-id="' + data.id + '"]', tinymce.activeEditor.getDoc()).contents().unwrap();
            }

            let element = $('[data-comment-id="' + data.id + '"]');
            element.closest('[class*="item-wrapper"]').removeClass('comment-set');
            if (element.parent().parent().hasClass("title_tinymce")) {
                element.parent().parent().removeClass('comment-set');
            }
            element.removeAttr('data-comment-id').removeData('comment-id');
        });
        return false;
    });

    /**
     * Remove comment modal
     */
    $(document).on('click', '.comment-holder .cancel-comment .iconoir-xmark', function(e) {
        e.preventDefault();

        removeControls();

        return false;
    });

    /**
     * Remove comment modal
     */
    $(document).on('click', '.fancy_tinymce .modal-backdrop', function(e) {
        e.preventDefault();

        removeControls();

        return false;
    });

    //
    //
    // tinymce custom part
    //
    //

    let tinyCommentCheckSelect = function (e) {
        if ($(e.target).hasClass('hold')) {
            return;
        }

        removeControls();

        let editor = tinymce.activeEditor;
        let selectionContent = editor.selection.getContent();
        let $container = $(editor.getContainer().parentNode);
        let position = tinymcePosition();

        if ($(e.target).hasClass('integrated-comment') && selectionContent == '') {
            showAddedComment($(e.target).data('comment-id'), position, null, $container);

            return;
        } else if ($(e.rangeParent).hasClass('integrated-comment') && selectionContent == '') {
            showAddedComment($(e.rangeParent).data('comment-id'), position, null, $container);

            return;
        } else if (selectionContent == '') {
            return;
        }

        let fieldName = getFieldName($(editor.getElement()).attr('name'));

        showCommentButton(fieldName, position, null, $container);
    };

    /**
     * @returns {{left: *, top: *}}
     */
    let tinymcePosition = function () {
        let rectangle = tinymce.activeEditor.selection.getSel().getRangeAt(0).getBoundingClientRect();
        let $container = $(tinymce.activeEditor.getContainer().parentNode);
        let toolbarHeight = $('.tox-editor-header', $container).outerHeight();
        let tinyTitle = $('.title_tinymce_input').height();

        //bottom position + toolbarheight + 5 pixel margin + TinyMCE overlay title
        return {left: rectangle.left, top: (rectangle.bottom + toolbarHeight + 5 + tinyTitle)};
    };

    /**
     * Add event listeners to tinymce after tinymce is loaded
     */
    let tinymceInit = function () {
        if (typeof tinymce == 'undefined' || tinymce.activeEditor == undefined || tinymce.activeEditor.formatter == undefined) {
            return;
        }
        clearInterval(waitForTiny);

        tinymce.activeEditor.formatter.register('integrated_comment', {inline : 'span', 'classes' : 'integrated-comment', attributes: {'data-comment-id' : '%value'}});
        tinymce.activeEditor.on('click', tinyCommentCheckSelect);
        tinymce.activeEditor.on('keypress', tinyCommentCheckSelect);
        tinymce.activeEditor.on('focusout', removeCommentButton);
        tinymce.activeEditor.dom.loadCSS("/bundles/integratedcomment/css/comments.css");
    };

            let waitForTiny = setInterval(tinymceInit, 100);
        });

        return true;
    };

    if (bootstrap()) {
        return;
    }

    let retries = 0;
    let timer = setInterval(function () {
        retries += 1;
        if (bootstrap() || retries >= 200) {
            clearInterval(timer);
        }
    }, 50);
})();
