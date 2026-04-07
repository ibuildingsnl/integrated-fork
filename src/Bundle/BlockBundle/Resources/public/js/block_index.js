(function () {
    function initBlockIndexInteractions() {
        if (window.__integratedBlockIndexInteractionsBound) {
            return;
        }
        window.__integratedBlockIndexInteractionsBound = true;

        function bindFilterFormAutoSubmit() {
            if (document.body.dataset.boundFilterAutoSubmit === 'true') {
                return;
            }

            document.addEventListener('change', function (event) {
                var target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }

                var form = target.closest('form[name="integrated_block_filter"]');
                if (!form) {
                    return;
                }

                if (target.matches('input[name="integrated_block_filter[q]"]')) {
                    return;
                }

                form.requestSubmit ? form.requestSubmit() : form.submit();
            });

            document.body.dataset.boundFilterAutoSubmit = 'true';
        }

        function bindFilterQueryAutoSubmit() {
            if (document.body.dataset.boundFilterQueryAutoSubmit === 'true') {
                return;
            }

            document.addEventListener('input', function (event) {
                var target = event.target;
                if (!(target instanceof Element) || !target.matches('input[name="integrated_block_filter[q]"]')) {
                    return;
                }

                var form = target.closest('form[name="integrated_block_filter"]');
                if (!form) {
                    return;
                }

                var debounceTimer = form.__integratedFilterDebounceTimer || null;
                if (debounceTimer) {
                    window.clearTimeout(debounceTimer);
                }

                debounceTimer = window.setTimeout(function () {
                    form.requestSubmit ? form.requestSubmit() : form.submit();
                }, 250);

                form.__integratedFilterDebounceTimer = debounceTimer;
            });

            document.body.dataset.boundFilterQueryAutoSubmit = 'true';
        }

        function normalizeBlockFilterSearchValue(value) {
            return String(value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toUpperCase();
        }

        function getBlockFilterOptionItems(container) {
            var items = [];
            container.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(function (input) {
                var item = input.closest('li, .checkbox, .radio, .form-check, .choice');
                if (!item) {
                    item = input.closest('label') || input.parentElement;
                }
                if (item && items.indexOf(item) === -1 && !item.closest('.aside-item-search')) {
                    items.push(item);
                }
            });

            return items;
        }

        function filterBlockFilterOptions(wrapper, query) {
            var container = wrapper.querySelector('.aside-item-list-container');
            if (!container) {
                return;
            }

            var normalizedQuery = normalizeBlockFilterSearchValue(query);
            getBlockFilterOptionItems(container).forEach(function (item) {
                var text = normalizeBlockFilterSearchValue(item.textContent || '');
                item.style.display = text.indexOf(normalizedQuery) !== -1 ? '' : 'none';
            });
        }

        function initBlockFilterSidebarSearch() {
            document.querySelectorAll('.aside-item-wrapper[data-block-filter-search="1"]').forEach(function (wrapper) {
                if (wrapper.dataset.boundBlockFilterSearch === 'true') {
                    return;
                }

                var container = wrapper.querySelector('.aside-item-list-container');
                if (!container) {
                    return;
                }

                var searchWrapper = document.createElement('div');
                searchWrapper.className = 'aside-item-search';
                searchWrapper.setAttribute('data-block-filter-search-input', '1');

                var icon = document.createElement('i');
                icon.className = 'iconoir-search';

                var input = document.createElement('input');
                input.type = 'text';
                input.className = 'list-search';
                input.placeholder = wrapper.dataset.blockFilterSearchPlaceholder || '';
                input.setAttribute('data-bound-list-search', 'true');

                input.addEventListener('input', function () {
                    filterBlockFilterOptions(wrapper, input.value);
                });
                input.addEventListener('change', function () {
                    filterBlockFilterOptions(wrapper, input.value);
                });

                searchWrapper.appendChild(icon);
                searchWrapper.appendChild(input);
                container.insertBefore(searchWrapper, container.firstChild);
                wrapper.dataset.boundBlockFilterSearch = 'true';
            });
        }

        function closeOtherJqueryPopovers($triggers, currentElement) {
            $triggers.not(currentElement).popover('hide');
        }

        function initUsagePopovers() {
            var selector = '[data-toggle="popover"]';

            if (window.jQuery && typeof window.jQuery.fn.popover === 'function') {
                var $triggers = window.jQuery(selector);
                if ($triggers.length === 0) {
                    return;
                }

                $triggers.popover();
                $triggers.off('show.bs.popover.integrated').on('show.bs.popover.integrated', function () {
                    closeOtherJqueryPopovers($triggers, this);
                });

                window.jQuery(document)
                    .off('click.integratedBlockUsagePopover')
                    .on('click.integratedBlockUsagePopover', function (event) {
                        var $target = window.jQuery(event.target);
                        if ($target.closest(selector).length === 0 && $target.closest('.popover').length === 0) {
                            $triggers.popover('hide');
                        }
                    });

                return;
            }

            if (window.bootstrap && typeof window.bootstrap.Popover === 'function') {
                var triggers = Array.from(document.querySelectorAll(selector));
                if (triggers.length === 0) {
                    return;
                }

                triggers.forEach(function (trigger) {
                    if (trigger.dataset.boundPopover === 'true') {
                        return;
                    }

                    new window.bootstrap.Popover(trigger);
                    trigger.addEventListener('show.bs.popover', function () {
                        triggers.forEach(function (other) {
                            if (other === trigger) {
                                return;
                            }

                            var instance = window.bootstrap.Popover.getInstance(other);
                            if (instance) {
                                instance.hide();
                            }
                        });
                    });
                    trigger.dataset.boundPopover = 'true';
                });

                if (!document.body.dataset.boundBlockUsagePopoverOutsideClick) {
                    document.addEventListener('click', function (event) {
                        if (event.target.closest(selector) || event.target.closest('.popover')) {
                            return;
                        }

                        triggers.forEach(function (trigger) {
                            var instance = window.bootstrap.Popover.getInstance(trigger);
                            if (instance) {
                                instance.hide();
                            }
                        });
                    });
                    document.body.dataset.boundBlockUsagePopoverOutsideClick = 'true';
                }
            }
        }

        function init() {
            bindFilterFormAutoSubmit();
            bindFilterQueryAutoSubmit();
            initBlockFilterSidebarSearch();
            initUsagePopovers();
        }

        document.addEventListener('DOMContentLoaded', init);
        document.addEventListener('turbo:load', init);
    }

    initBlockIndexInteractions();
})();
