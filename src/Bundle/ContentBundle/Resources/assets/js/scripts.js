$('.svg-img').each(function () {
    $(this).attr('src', ($(this).attr('data-png-src')));
});

function initCommonUi() {
    resetSubmitButtons(document);
    //Placeholders Fix
    $('input, textarea').placeholder();

    $('.search-form .form-control').off('focus.commonUi blur.commonUi');
    $('.search-form .form-control').on('focus.commonUi', function () {
        if ($(window).width() >= 768) {
            $(this).closest('.nav-form-inner').addClass('full-width');
        }
    });
    $(".search-form .form-control").on('blur.commonUi', function () {
        $(this).closest('.nav-form-inner').removeClass('full-width');
    });

    //Select 2 initial
    $(".basic-multiple").each(function() {
        if (!$(this).hasClass('select2-hidden-accessible')) {
            $(this).select2();
        }
    });

    $('select.select2').each(function() {
        if (!$(this).hasClass('select2-hidden-accessible')) {
            $(this).select2({
                placeholder: $(this).data('placeholder')
            });
        }
    });

    $('button[type="submit"]').off('click.commonUi').on('click.commonUi', function() {
        // Let Turbo handle submission; button state is managed via turbo events.
    });
}

$(document).ready(initCommonUi);
document.addEventListener('turbo:load', initCommonUi);
document.addEventListener('turbo:before-cache', function() {
    resetSubmitButtons(document);
});

function resetSubmitButtons(root) {
    const $root = root ? $(root) : $(document);
    $root.find('button[type="submit"].button-submitted').each(function() {
        $(this).removeClass('button-submitted');
        $(this).find('.button-submitted-icon').remove();
        $(this).removeAttr('disabled');
    });
}

document.addEventListener('turbo:submit-end', function(event) {
    if (event.detail && event.detail.success) {
        resetSubmitButtons(event.target);
    }
});

document.addEventListener('turbo:render', function() {
    resetSubmitButtons(document);
});

document.addEventListener('turbo:submit-start', function(event) {
    const form = event.target;
    if (!form) {
        return;
    }
    const submitter = event.detail && event.detail.formSubmission && event.detail.formSubmission.submitter
        ? event.detail.formSubmission.submitter
        : null;
    const $form = $(form);
    $form.find('button[type="submit"]').addClass('button-submitted').attr('disabled', 'disabled');
    if (submitter) {
        const $submitter = $(submitter);
        if (!$submitter.find('.button-submitted-icon').length) {
            $submitter.prepend('<i class="iconoir-refresh-double button-submitted-icon" aria-hidden="true"></i>');
        }
    }
});

function initTypeahead() {
    var suggestions = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.whitespace,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: '/admin/suggestions/%QUERY',
            wildcard: '%QUERY',
            transform: transform
        }
    });

    var elm = $('.search-form .form-control');

    if (!elm.length || elm.data('typeahead-initialized') || elm.closest('.twitter-typeahead').length) {
        return;
    }

    elm.typeahead({highlight: true}, {
        name: 'suggestions',
        source: suggestions,
        limit: Infinity,
        display: display,
        templates: {
            suggestion: Handlebars.compile(
                '{{#if type.suggestion }}' +
                    '<div class="tt-suggestion-term"><div class="tt-suggestion-head">{{data}}</div></div>' +
                '{{/if}}' +
                '{{#if type.media_gallery }}' +
                    '<div class="tt-suggestion-term">' +
                        '<a href="{{data.url}}">Show result in media gallery: {{data.query}}</a>' +
                    '</div>' +
                '{{/if}}' +
                '{{#if type.result }}' +
                    '<div class="tt-suggestion-result">' +
                        '{{#if data.open_in_media_gallery }}' +
                            '<div class="media-preview">\\n' +
                            '<img src="{{data.image_string}}">\\n' +
                            '</div>' +
                            '<div class="tt-result-wrapper"><div><a href="{{data.media_gallery_url}}">{{data.title}}</a></div>' +
                        '{{else}}' +
                            '<div class="tt-result-wrapper"><div><a href="{{data.url}}">{{data.title}}</a></div>' +
                        '{{/if}}' +
                        '<ul>' +
                            '<li>{{data.type}}</li>' +
                            '<li>{{data.published}}</li>' +
                        '</ul>' +
                    '</div></div>' +
                '{{/if}}'
            )
        }
    });

    elm.data('typeahead-initialized', true);

    // this event listener will add a divider between terms and results.
    elm.bind('typeahead:render', function (e, suggestions, async, dataset) {
        var first = elm.parent('.twitter-typeahead').find('.tt-dataset-suggestions .tt-suggestion-term').first();

        if (!first.is(':first-child')) {
            first.before('<div role="separator" class="tt-divider"></div>');
        }
    });

    // redirect to the edit page when a result is selected.
    elm.bind('typeahead:select', function(e, suggestion) {
        if (suggestion.type.result) {
            window.location.href = suggestion.data.url;
        } else {
            elm.parents('form').submit();
        }
    });

    function transform(response) {
        var results = [];

        let media_item_in_results = false

        if ($.isArray(response.results)) {
            $.each(response.results, function () {
                var data = this;

                if (data.class === 'Image' || data.class === 'Video' || data.class === 'File') {
                    if (media_item_in_results == false) {
                        results.unshift({
                            type: { suggestion: false, result: false, media_gallery: true },
                            data: {
                                url: '/admin/media?q=' + response.query,
                                query: response.query
                            }
                        });
                        media_item_in_results = true
                    }

                    data.open_in_media_gallery = true
                    data.media_gallery_url = '/admin/media?ids=' + data.id
                }

                if (data.published) {
                    data.published = moment(data.published).format('lll');
                }

                results.push({
                    type: { suggestion: false, result: true },
                    data: data
                });
            });
        }

        if ($.isArray(response.suggestions)) {
            $.each(response.suggestions, function (index, value) {
                results.push({
                    type: { suggestion: true, result: false },
                    data: value
                });
            });
        }

        return results;
    }

    function display(item) {
        if (item.type.suggestion) {
            return item.data;
        }

        if (item.type.result) {
            return item.data.title;
        }

        return '';
    }
}

$(document).ready(initTypeahead);
document.addEventListener('turbo:load', initTypeahead);

function initAlertDismiss() {
    $(document).off('click.alertDismiss', '.alert.alert-dismissible .close')
        .on('click.alertDismiss', '.alert.alert-dismissible .close', function() {
            $(this).parent().remove();
        });
}

function initParentSelectHandlers() {
    var selectElement = $('#integrated_content_parent_id');
    if (!selectElement.length) {
        return;
    }

    if (selectElement.find('option:selected').length > 0) {
        $('.aside-item-wrapper.channels').hide();
        $('.aside-item-wrapper.brands').hide();
    }

    selectElement.off('select2:select.parentSelect select2:unselect.parentSelect');
    selectElement.on('select2:select.parentSelect', function(e) {
        $('.aside-item-wrapper.channels').hide();
        $('.aside-item-wrapper.brands').hide();
    });

    selectElement.on('select2:unselect.parentSelect', function(e) {
        $('.aside-item-wrapper.channels').show();
        $('.aside-item-wrapper.brands').show();
    });
}

$(document).ready(function() {
    initAlertDismiss();
    initParentSelectHandlers();
});
document.addEventListener('turbo:load', function() {
    initAlertDismiss();
    initParentSelectHandlers();
});
