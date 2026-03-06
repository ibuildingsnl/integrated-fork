if (typeof window.publicationSchedule === 'object') {
    const publicationSchedule = Array.isArray(window.publicationSchedule) ? window.publicationSchedule : [];
    const newsletterSchedule = Array.isArray(window.newsletterSchedule) ? window.newsletterSchedule : [];
    const FILTER_STATE_KEY = 'contentCalendar.filterStates.v1';
    const brandColors = {
        'facebook': '#1877f2',
        'linkedin': '#0077b5',
        'x': '#000',
        'instagram': '#e4405f',
        'woodwing': '#f15429',
    };

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, (char) => {
            const entities = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            };

            return entities[char] || char;
        });
    }

    function sanitizeIcon(value) {
        return String(value ?? '').replace(/[^a-z0-9-]/gi, '');
    }

    function normalizeBooleanFlag(value) {
        if (value === true || value === false) {
            return value;
        }

        if (typeof value === 'number') {
            return value === 1;
        }

        if (typeof value === 'string') {
            const normalized = value.trim().toLowerCase();
            return normalized === 'true' || normalized === '1' || normalized === 'yes' || normalized === 'on';
        }

        return false;
    }

    function isItemPremium(item) {
        return normalizeBooleanFlag(item.getAttribute('data-premium'));
    }

    function renderPublicationScheduleEntries() {
        document.querySelectorAll('.calendar-item[data-schedule-entry="publication"]').forEach((item) => item.remove());

        for (const publication of publicationSchedule) {
            const publicationId = String(publication?.id ?? '').trim();
            if (publicationId === '') {
                continue;
            }

            const column = document.querySelector(
                '.day.column[data-date="' + publication.date + '"]');
            if (!column) {
                continue;
            }

            const existingItem = column.querySelector(
                '.calendar-item[data-id="' + publicationId + '"]');

            let shouldAddIcon = false;
            let sameDate = false;

            if (existingItem) {
                const publishTimeText = existingItem.querySelector(
                    '.publish-time').textContent;

                shouldAddIcon = true;
                if (publishTimeText === publication.display_time) {
                    sameDate = true;
                }
            }

            const colorVariable = brandColors.hasOwnProperty(publication.icon) ?
                brandColors[publication.icon] :
                '#000000';

            if (shouldAddIcon) {
                const headingLeftDiv = existingItem.querySelector('.heading-left');
                const iconClass = 'icon iconoir-' + publication.icon;
                const existingIcon = headingLeftDiv.querySelector(
                    `.${iconClass.replace(/\s/g, '.')}`);

                if (!existingIcon) {
                    const newIcon = document.createElement('i');
                    newIcon.className = iconClass;

                    newIcon.title = publication.name;
                    const firstIcon = headingLeftDiv.querySelector('i.icon');

                    if (firstIcon) {
                        firstIcon.insertAdjacentElement('afterend', newIcon);
                    } else {
                        headingLeftDiv.appendChild(newIcon);
                    }
                }

                if (publication.published === 'failed') {
                    const calContent = existingItem.querySelector('.calendar-wrap');
                    const publicationErrorKey = publicationId + '|' + publication.name + '|' + publication.brand_name;
                    const hasExistingError = Array.from(calContent.querySelectorAll('.calendar-error')).some(
                        (entry) => entry.dataset.publicationKey === publicationErrorKey
                    );

                    if (!hasExistingError) {
                        const errorEntry = document.createElement('div');
                        errorEntry.className = 'calendar-error';
                        errorEntry.dataset.publicationKey = publicationErrorKey;
                        errorEntry.innerHTML = '<b>' + escapeHtml(publication.name) + ' ' +
                            escapeHtml(publication.brand_name) + '</b>:<br>' + escapeHtml(publication.response);
                        calContent.appendChild(errorEntry);
                    }
                }
            }

            let before = null;

            for (const calendarItem of column.querySelectorAll('a.calendar-item')) {
                if (calendarItem.dataset.time > publication.time) {
                    before = calendarItem;
                    break;
                }
            }

            const publicationEntry = document.createElement('a');
            column.insertBefore(publicationEntry, before);
            publicationEntry.className = 'quick-edit-link calendar-item ' + publication.published + ' ' + publication.typename;
            publicationEntry.href = '/admin/content/' + encodeURIComponent(publicationId);
            publicationEntry.dataset.scheduleEntry = 'publication';
            publicationEntry.dataset.parentId = publicationId;
            publicationEntry.dataset.time = publication.time;
            publicationEntry.dataset.type = publication.typename;
            publicationEntry.dataset.brands = publication.brand_name;
            publicationEntry.dataset.premium = normalizeBooleanFlag(publication.premium) ? 'true' : 'false';
            publicationEntry.dataset.sameDate = sameDate ? 'true' : 'false';
            publicationEntry.innerHTML = generatePublicationHTML(publication, colorVariable);

            attachMouseEvents(publicationEntry, colorVariable, publication);
        }
    }

    function generatePublicationHTML(publication, colorVariable) {
        const safeIcon = sanitizeIcon(publication.icon);
        const safeDisplayTime = escapeHtml(publication.display_time);
        const safeBrandName = escapeHtml(publication.brand_name);
        const safePublicationTitle = escapeHtml(publication.title);
        const safeErrorResponse = escapeHtml(publication.response);

        const errorMessage = publication.published === 'failed'
            ? `<div class="calendar-error hidden">${safeErrorResponse}</div>`
            : '';

        let postContent = '<div class="post-content hidden">';
        Object.entries(publication.settings).forEach(([key, value]) => {
            if (value && value.length > 0) {
                postContent += `
                <b>${escapeHtml(key.charAt(0).toUpperCase() + key.slice(1))}</b>
                <p>${escapeHtml(value)}</p>
            `;
            }
        });
        postContent += '</div>';

        if (postContent === '<div class="post-content hidden"></div>') {
            postContent = '';
        }

        const postImages = publication.images.length > 0
            ? `<div class="post-images hidden">${publication.images.map(src => `<img src="${escapeHtml(src)}" alt="">`).join('')}</div>`
            : '';

        const showDetailsButton = (postContent || postImages || errorMessage)
            ? `<button class="show-details-btn"><i class="iconoir-info-circle"></i></button>`
            : '';

        return `<div class="calendar-wrap" style="--color: ${colorVariable}">
                    <div class="calendar-item-heading">
                        <div class="heading-left">
                            <i class="icon iconoir-${safeIcon}" title="${safeIcon}"></i>
                            <span class="publish-time">${safeDisplayTime}</span>
                            <div class="favicon-wrapper">
                                <div class="channel-favicons">
                                    <div class="channel-favicon" style="background-image:url(${escapeHtml(publication.brand_favicon)})" title="${safeBrandName}"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="calendar-item-content">
                        ${safePublicationTitle}
                        ${showDetailsButton}
                    </div>
                    ${postContent}
                    ${postImages}
                    ${errorMessage}
                </div>`;
    }

    function attachMouseEvents(publicationEntry, colorVariable, publication) {
        publicationEntry.addEventListener('mouseover', function(ev) {
            const content = document.querySelector(
                '.calendar-item[data-id="' + publication.id + '"]');
            if (content) {
                content.style.borderColor = colorVariable;
                content.style.margin = '0 -3px 0.5rem -3px';
            }
        });
        publicationEntry.addEventListener('mouseout', function(ev) {
            const items = document.querySelectorAll('.calendar-item');
            items.forEach((i) => {
                i.style.borderColor = '';
                i.style.margin = '';
            });
        });
    }

    document.addEventListener('click', function(event) {
        const detailsButton = event.target.closest('.show-details-btn');
        if (detailsButton) {
            event.preventDefault();
            const calendarItem = detailsButton.closest('.calendar-item');
            const elementsToToggle = calendarItem.querySelectorAll('.calendar-error, .post-content, .post-images');
            elementsToToggle.forEach(element => {
                element.classList.toggle('hidden');
            });
        }
    });

    let filterStates = {
        contentType: new Set(),
        brand: new Set(),
        premium: false,
    };

    function buildPublicationBrandLookup() {
        const lookup = new Map();

        publicationSchedule.forEach((publication) => {
            if (!publication || publication.id === undefined || publication.id === null) {
                return;
            }

            const publicationId = String(publication.id).trim();
            if (publicationId === '') {
                return;
            }

            const brandName = typeof publication.brand_name === 'string' ? publication.brand_name.trim() : '';
            if (brandName === '') {
                return;
            }

            const existing = lookup.get(publicationId) || new Set();
            existing.add(brandName);
            lookup.set(publicationId, existing);
        });

        return lookup;
    }

    const publicationBrandLookup = buildPublicationBrandLookup();

    function getItemBrands(item) {
        const brandsRaw = item.getAttribute('data-brands') || '';
        const direct = brandsRaw.split(',').map((brand) => brand.trim()).filter(Boolean);
        if (direct.length > 0) {
            return direct;
        }

        const itemId = (item.getAttribute('data-id') || '').trim();
        if (!itemId || !publicationBrandLookup.has(itemId)) {
            return [];
        }

        return Array.from(publicationBrandLookup.get(itemId));
    }

    function hydrateCalendarItemBrandsFromPublicationData() {
        document.querySelectorAll('.calendar-item').forEach((item) => {
            const existing = (item.getAttribute('data-brands') || '').trim();
            if (existing !== '') {
                return;
            }

            const brands = getItemBrands(item);
            if (brands.length > 0) {
                item.dataset.brands = brands.join(', ');
            }
        });
    }

    function initCalendarFilterOptions() {
        const contentTypesMenu = document.getElementById('content-types-menu');
        const brandsMenu = document.getElementById('brands-menu');
        if (!contentTypesMenu || !brandsMenu) {
            return;
        }

        contentTypesMenu.innerHTML = '';
        brandsMenu.innerHTML = '';

        const contentTypes = new Set();
        const brands = new Set();

        // Process each calendar item once for efficiency
        document.querySelectorAll('.calendar-item').forEach(item => {
            const type = item.getAttribute('data-type');
            const brandList = getItemBrands(item);

            if (type) {
                contentTypes.add(type);
            }
            brandList.forEach(brand => {
                if (brand) {
                    brands.add(brand);
                }
            });
        });

        // Fallback: keep brand filter usable even when base Solr items have empty data-brands.
        publicationSchedule.forEach(publication => {
            if (publication && typeof publication.brand_name === 'string' && publication.brand_name.trim() !== '') {
                brands.add(publication.brand_name.trim());
            }
        });

        newsletterSchedule.forEach(newsletter => {
            if (newsletter && typeof newsletter.brand_name === 'string' && newsletter.brand_name.trim() !== '') {
                brands.add(newsletter.brand_name.trim());
            }
        });

        const contentTypeFragment = document.createDocumentFragment();
        contentTypes.forEach(type => appendFilterOption(contentTypeFragment, 'contentType', type));
        contentTypesMenu.appendChild(contentTypeFragment);

        const brandFragment = document.createDocumentFragment();
        brands.forEach(brand => appendFilterOption(brandFragment, 'brand', brand));
        brandsMenu.appendChild(brandFragment);
    }

    function appendFilterOption(fragment, filterName, value) {
        const li = document.createElement('li');
        li.className = 'checkbox';
        const label = document.createElement('label');
        label.className = 'checkbox-container';

        const input = document.createElement('input');
        input.type = 'checkbox';
        input.name = filterName;
        input.value = value;

        const checkmark = document.createElement('span');
        checkmark.className = 'checkmark';

        const facetWrapper = document.createElement('div');
        facetWrapper.className = 'facet-wrapper';

        const facetTitle = document.createElement('span');
        facetTitle.className = 'facet-title';
        facetTitle.textContent = value;

        const facetCount = document.createElement('span');
        facetCount.className = 'facet-count';

        facetWrapper.appendChild(facetTitle);
        facetWrapper.appendChild(facetCount);
        label.appendChild(input);
        label.appendChild(checkmark);
        label.appendChild(facetWrapper);
        li.appendChild(label);
        fragment.appendChild(li);
    }

    function initCalendarFilters() {
        if (!document.querySelector('.calendar-view.calendar-week')) {
            return;
        }

        renderPublicationScheduleEntries();
        hydrateCalendarItemBrandsFromPublicationData();
        initCalendarFilterOptions();

        const hasPremiumContent = Array.from(document.querySelectorAll('.calendar-item')).some(item => isItemPremium(item));
        const premiumCheckboxContainer = document.querySelector('.premium-checkbox');
        if (!premiumCheckboxContainer) {
            return;
        }

        if (!hasPremiumContent) {
            premiumCheckboxContainer.style.display = 'none';
        } else {
            premiumCheckboxContainer.style.display = '';
        }

        attachFilterEventListeners();
        loadFilterStates();
        applyFilters();
        countAndUpdateFacetCounts();
    }

    function attachFilterEventListeners() {
        document.querySelectorAll('.content-navigator-menu input[name="contentType"]').
            forEach(input => {
                if (input.dataset.boundCalendarFilter) {
                    return;
                }
                input.addEventListener('change', () => {
                    updateFilterStates();
                    countAndUpdateFacetCounts();
                });
                input.dataset.boundCalendarFilter = 'true';
            });

        document.querySelectorAll('.content-navigator-menu input[name="brand"]').
            forEach(input => {
                if (input.dataset.boundCalendarFilter) {
                    return;
                }
                input.addEventListener('change', () => {
                    updateFilterStates();
                    countAndUpdateFacetCounts();
                });
                input.dataset.boundCalendarFilter = 'true';
            });

        const premiumCheckbox = document.getElementById('premium-checkbox');
        if (!premiumCheckbox) {
            return;
        }
        if (premiumCheckbox.dataset.boundCalendarFilter) {
            return;
        }
        premiumCheckbox.addEventListener('change', () => {
            filterStates.premium = premiumCheckbox.checked;
            saveAndApplyFilters();
        });
        premiumCheckbox.dataset.boundCalendarFilter = 'true';
    }

    function updateFilterStates() {
        filterStates.contentType = new Set([...document.querySelectorAll('.calendar-filter.contenttypes [name="contentType"]:checked')].map(input => input.value));
        filterStates.brand = new Set([...document.querySelectorAll('.calendar-filter.brands [name="brand"]:checked')].map(input => input.value));
        saveAndApplyFilters();
    }

    function saveAndApplyFilters() {
        saveFilterStates();
        applyFilters();
    }

    function saveFilterStates() {
        localStorage.setItem(FILTER_STATE_KEY, JSON.stringify({
            contentType: Array.from(filterStates.contentType),
            brand: Array.from(filterStates.brand),
            premium: filterStates.premium,
        }));
    }

    function loadFilterStates() {
        let savedStates = null;
        try {
            savedStates = JSON.parse(localStorage.getItem(FILTER_STATE_KEY));
        } catch (error) {
            savedStates = null;
        }
        if (savedStates) {
            filterStates.contentType = new Set(savedStates.contentType || []);
            filterStates.brand = new Set(savedStates.brand || []);
            filterStates.premium = 'premium' in savedStates ? normalizeBooleanFlag(savedStates.premium) : false;

            document.querySelectorAll('[name="contentType"]').forEach(input => {
                input.checked = filterStates.contentType.has(input.value);
            });
            document.querySelectorAll('[name="brand"]').forEach(input => {
                input.checked = filterStates.brand.has(input.value);
            });
            const premiumCheckbox = document.getElementById('premium-checkbox');
            if (premiumCheckbox) premiumCheckbox.checked = filterStates.premium;
        }
    }

    function applyFilters() {
        document.querySelectorAll('.calendar-item').forEach(item => {
            const typeMatch = filterStates.contentType.has(item.dataset.type) || filterStates.contentType.size === 0;
            const brandList = getItemBrands(item);
            const brandMatch = brandList.some((brand) => filterStates.brand.has(brand)) || filterStates.brand.size === 0;
            const premiumMatch = filterStates.premium ? isItemPremium(item) : true;

            // Check for data-same-date condition
            const sameDate = item.dataset.sameDate === 'true';
            const sameDateVisible = sameDate ? filterStates.contentType.has(item.dataset.type) : true;

            const shouldDisplay = typeMatch && brandMatch && premiumMatch && sameDateVisible;

            item.style.display = shouldDisplay ? '' : 'none';
        });
    }

    function countAndUpdateFacetCounts() {
        const contentTypeCounts = {};
        const brandCounts = {};
        let premiumCount = 0;

        const selectedContentTypes = new Set([...document.querySelectorAll('[name="contentType"]:checked')].map(input => input.value));
        const selectedBrands = new Set([...document.querySelectorAll('[name="brand"]:checked')].map(input => input.value));
        const hasSelectedContentTypes = selectedContentTypes.size > 0;
        const hasSelectedBrands = selectedBrands.size > 0;

        document.querySelectorAll('.calendar-item').forEach(item => {
            const isVisible = item.style.display !== 'none';
            const itemBrands = getItemBrands(item);
            const type = item.getAttribute('data-type');
            const isPremium = isItemPremium(item);
            const premiumMatches = filterStates.premium ? isPremium : true;

            const brandMatchesSelection = !hasSelectedBrands || itemBrands.some(brand => selectedBrands.has(brand));
            const typeMatchesSelection = !hasSelectedContentTypes || selectedContentTypes.has(type);

            if (premiumMatches && brandMatchesSelection) {
                if (type) {
                    contentTypeCounts[type] = (contentTypeCounts[type] || 0) + 1;
                }
            }

            if (premiumMatches && typeMatchesSelection) {
                itemBrands.forEach(brand => {
                    brandCounts[brand] = (brandCounts[brand] || 0) + 1;
                });
            }

            if (isVisible && isPremium) {
                premiumCount += 1;
            }
        });

        updateFacetDisplay('[name="contentType"]', contentTypeCounts, false);

        const premiumCheckboxCountDisplay = document.querySelector('.premium-checkbox .facet-count');
        if (premiumCheckboxCountDisplay) {
            premiumCheckboxCountDisplay.textContent = `(${premiumCount})`;
        }

        updateFacetDisplay('[name="brand"]', brandCounts, true);
    }

    function updateFacetDisplay(selector, counts, hideZero = false) {
        document.querySelectorAll(selector).forEach(input => {
            const count = counts[input.value] || 0;
            const option = input.closest('li.checkbox');
            const countDisplay = input.closest('.checkbox-container').querySelector('.facet-count');
            if (countDisplay) {
                countDisplay.textContent = `(${count})`;
            }

            if (option && hideZero) {
                option.style.display = (count === 0 && !input.checked) ? 'none' : '';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', initCalendarFilters);
    document.addEventListener('turbo:load', initCalendarFilters);
    document.addEventListener('turbo:render', initCalendarFilters);
    document.addEventListener('content-calendar:items-changed', initCalendarFilters);

    if (document.readyState !== 'loading') {
        initCalendarFilters();
    }
}
