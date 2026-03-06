function initChannelDomainsCollection() {
    $('.channel-domains').each(function() {
        var $domains_collection = $(this);

        if ($domains_collection.data('channel-domains-bound')) {
            return;
        }

        $domains_collection.data('channel-domains-bound', true);

        function getPrimaryDomainInput() {
            return $domains_collection.closest('form').find('.primary-domain-input').first();
        }

        function refresh_checked_status() {
            var $primary_domain_input = getPrimaryDomainInput();

            /* if a domain input has entered domain name allow to set it as primary */
            $('.primary-domain-radio', $domains_collection).each(function () {
                var domain_name = $(this).closest('.panel-body').find('input:text').val().trim();

                if (domain_name) {
                    $(this).removeAttr('disabled');

                    /* if one of domains */
                    if ($primary_domain_input.val() == domain_name) {
                        $(this).prop('checked', true).trigger('click');
                    }
                } else {
                    $(this).attr('disabled','disabled').prop('checked', false);
                }
            });

            /* if no selected primary domain, select first */
            if (!$('.primary-domain-radio:checked', $domains_collection).length) {
                $('.primary-domain-radio:first', $domains_collection).prop('checked', true).trigger('click');
            }
        }

        $domains_collection.on('keyup', 'input[type=text]', refresh_checked_status);
        $domains_collection.on('click', '.primary-domain-radio', function() {
            var value = $(this).closest('.panel-body').find('input:text').val();
            getPrimaryDomainInput().val(value);
        });
        $domains_collection.on('change', 'input[type=text]', function () {
            if ($(this).closest('.panel-body').find('.primary-domain-radio').is(":checked")) {
                getPrimaryDomainInput().val($(this).val());
            }
        });

        // Turbo navigation can skip global collection binding; handle domain add/remove safely here.
        $domains_collection.on('click', '[data-addfield="collection"]', function(event) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            var collectionId = $(this).data('collection');
            var $collection = collectionId ? $('#' + collectionId) : $(this).closest('[data-prototype]');

            if (!$collection.length || !$collection.data('prototype')) {
                return false;
            }

            var index = parseInt($collection.data('index'), 10);
            if (Number.isNaN(index)) {
                index = $collection.find('ul li').length;
            }

            var prototype = $collection
                .data('prototype')
                .replace(/__name__/g, index)
                .replace(/__id__/g, $collection.attr('id') + '_' + index);

            $collection.data('index', index + 1);
            $collection.find('> ul').append($('<li></li>').append(prototype));

            refresh_checked_status();

            return false;
        });

        $domains_collection.on('click', '[data-removefield="collection"]', function(event) {
            event.preventDefault();
            $(this).closest('li').remove();
            refresh_checked_status();
            return false;
        });

        refresh_checked_status();
    });
}

$(initChannelDomainsCollection);
document.addEventListener('turbo:load', initChannelDomainsCollection);
