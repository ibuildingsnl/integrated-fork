(function () {
    function parseDataValue(select) {
        var data = select.getAttribute('data-value') || '';

        if (!data) {
            return [];
        }

        try {
            var parsed = JSON.parse(data);

            if (Array.isArray(parsed)) {
                return parsed.map(String);
            }

            if (parsed && typeof parsed === 'object') {
                return Object.keys(parsed);
            }
        } catch (e) {
            // Keep fallback behavior for malformed data.
        }

        return [];
    }

    function setSelectedOption(select, id, title, selected) {
        var option = select.querySelector('option[value="' + CSS.escape(id) + '"]');

        if (selected) {
            if (!option) {
                option = document.createElement('option');
                option.value = id;
                option.textContent = title || id;
                option.selected = true;
                select.appendChild(option);
            } else {
                option.selected = true;
            }

            return;
        }

        if (option) {
            option.remove();
        }
    }

    function syncSelection(container) {
        var select = container.querySelector('select.relation-items[data-taxonomy-popup="1"]');
        if (!select) {
            return;
        }

        container.querySelectorAll('.categories_checkboxes input[type="checkbox"]').forEach(function (checkbox) {
            var title = checkbox.closest('li') ? checkbox.closest('li').dataset.title : checkbox.dataset.id;
            setSelectedOption(select, checkbox.dataset.id, title, checkbox.checked);
        });
    }

    function updatePills(container) {
        var pills = container.querySelector('.enabled_categories_pills');
        if (!pills) {
            return;
        }

        pills.innerHTML = '';

        container.querySelectorAll('.categories_checkboxes input[type="checkbox"]:checked').forEach(function (checkbox) {
            var categoryItem = checkbox.closest('li');
            if (!categoryItem || categoryItem.dataset.tabHidden === '1' || categoryItem.style.display === 'none') {
                return;
            }

            var node = document.createElement('div');
            node.className = 'active_category';
            node.textContent = categoryItem.dataset.fulltitle || categoryItem.dataset.title || checkbox.dataset.id;
            pills.appendChild(node);
        });
    }

    function isPopupOpen(container) {
        var popup = container.querySelector('.category_wrapper');
        return !!(popup && popup.classList.contains('show'));
    }

    function showAllItems(container) {
        container.querySelectorAll('.categories_checkboxes li').forEach(function (item) {
            item.dataset.tabHidden = '0';
            item.style.display = '';
        });
    }

    function applyTabFilter(container, tab) {
        if (!isPopupOpen(container)) {
            showAllItems(container);
            return;
        }

        var linkedChannel = tab ? (tab.dataset.linkedchannel || '') : '';

        container.querySelectorAll('.categories_checkboxes li').forEach(function (item) {
            if (!linkedChannel || item.dataset.linkedchannel === linkedChannel) {
                item.dataset.tabHidden = '0';
                item.style.display = '';
            } else {
                item.dataset.tabHidden = '1';
                item.style.display = 'none';
            }
        });
    }

    function setupSearch(container) {
        var input = container.querySelector('.list-search');
        if (!input) {
            return;
        }

        input.addEventListener('input', function () {
            if (!isPopupOpen(container)) {
                showAllItems(container);
                return;
            }

            var query = input.value.trim().toLowerCase();
            container.querySelectorAll('.categories_checkboxes li').forEach(function (item) {
                if (item.dataset.tabHidden === '1') {
                    item.style.display = 'none';
                    return;
                }

                var haystack = ((item.dataset.fulltitle || '') + ' ' + (item.dataset.title || '')).toLowerCase();
                if (!query || haystack.indexOf(query) !== -1) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    function togglePopup(container, show) {
        var popup = container.querySelector('.category_wrapper');
        var backdrop = container.querySelector('.taxonomy_backdrop');

        if (!popup || !backdrop) {
            return;
        }

        var shouldShow = typeof show === 'boolean' ? show : !popup.classList.contains('show');
        popup.classList.toggle('show', shouldShow);
        backdrop.classList.toggle('hide', !shouldShow);
        document.body.classList.toggle('popup-open', shouldShow);

        if (shouldShow) {
            var activeTab = container.querySelector('.categories_tabs .category_tab.active');
            applyTabFilter(container, activeTab);
        } else {
            showAllItems(container);
        }

        updatePills(container);
    }

    function initializeContainer(container) {
        if (container.dataset.bulkTaxonomyBound === '1') {
            return;
        }

        var select = container.querySelector('select.relation-items[data-taxonomy-popup="1"]');
        if (!select) {
            return;
        }

        var selected = parseDataValue(select);
        container.querySelectorAll('.categories_checkboxes input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.checked = selected.includes(checkbox.dataset.id);
            checkbox.addEventListener('change', function () {
                syncSelection(container);
                updatePills(container);
            });
        });

        var tabs = Array.from(container.querySelectorAll('.categories_tabs .category_tab'));
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (item) {
                    item.classList.remove('active');
                });
                tab.classList.add('active');
                applyTabFilter(container, tab);
                updatePills(container);
            });
        });

        var initialTab = tabs.find(function (tab) {
            return tab.classList.contains('active');
        }) || tabs[0] || null;
        applyTabFilter(container, initialTab);

        var toggler = container.querySelector('.togglefullscreen');
        if (toggler) {
            toggler.addEventListener('click', function () {
                togglePopup(container);
            });
        }

        var backdrop = container.querySelector('.taxonomy_backdrop');
        if (backdrop) {
            backdrop.addEventListener('click', function () {
                togglePopup(container, false);
            });
        }

        setupSearch(container);
        syncSelection(container);
        updatePills(container);

        container.dataset.bulkTaxonomyBound = '1';
    }

    function initBulkTaxonomyCategory() {
        document.querySelectorAll('.bulk-taxonomy-category').forEach(initializeContainer);
    }

    document.addEventListener('DOMContentLoaded', initBulkTaxonomyCategory);
    document.addEventListener('turbo:load', initBulkTaxonomyCategory);
})();
