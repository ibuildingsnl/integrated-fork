if (typeof publicationSchedule === 'object') {
    const brandColors = {
        'facebook': '#1877f2',
        'linkedin': '#0077b5',
        'x': '#000',
        'instagram': '#e4405f',
        'woodwing': '#f15429',
    };

    for (const publication of publicationSchedule) {
        const column = document.querySelector(
            '.day.column[data-date="' + publication.date + '"]');
        const existingItem = column.querySelector(
            '.calendar-item[data-id="' + publication.id + '"]');

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

        let colorVariable = brandColors.hasOwnProperty(publication.icon) ?
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
                const errorEntry = document.createElement('div');
                errorEntry.className = 'calendar-error';
                errorEntry.innerHTML = '<b>' + publication.name + ' ' +
                    publication.brand_name + '</b>:<br>' + publication.response;
                const calContent = existingItem.querySelector('.calendar-wrap');
                calContent.appendChild(errorEntry);
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
        publicationEntry.href = '/admin/content/' + publication.id;
        publicationEntry.dataset.parentId = publication.id;
        publicationEntry.dataset.time = publication.time;
        publicationEntry.dataset.type = publication.typename;
        publicationEntry.dataset.brands = publication.brand_name;
        publicationEntry.dataset.premium = publication.premium;
        publicationEntry.dataset.sameDate = sameDate ? 'true' : 'false';
        publicationEntry.innerHTML = generatePublicationHTML(publication, colorVariable);

        attachMouseEvents(publicationEntry, colorVariable, publication);
    }

    function generatePublicationHTML(publication, colorVariable) {

        const errorMessage = publication.published === 'failed'
            ? `<div class="calendar-error hidden">${publication.response}</div>`
            : '';

        let postContent = '<div class="post-content hidden">';
        Object.entries(publication.settings).forEach(([key, value]) => {
            if (value && value.length > 0) {
                postContent += `
                <b>${key.charAt(0).toUpperCase() + key.slice(1)}</b>
                <p>${value}</p>
            `;
            }
        });
        postContent += '</div>';

        if (postContent === '<div class="post-content hidden"></div>') {
            postContent = '';
        }

        const postImages = publication.images.length > 0
            ? `<div class="post-images hidden">${publication.images.map(src => `<img src="${src}" alt="">`).join('')}</div>`
            : '';

        const showDetailsButton = (postContent || postImages || errorMessage)
            ? `<button class="show-details-btn"><i class="iconoir-info-circle"></i></button>`
            : '';

        return `<div class="calendar-wrap" style="--color: ${colorVariable}">
                    <div class="calendar-item-heading">
                        <div class="heading-left">
                            <i class="icon iconoir-${publication.icon}" title="${publication.icon}"></i>
                            <span class="publish-time">${publication.display_time}</span>
                            <div class="favicon-wrapper">
                                <div class="channel-favicons">
                                    <div class="channel-favicon" style="background-image:url(${publication.brand_favicon})" title="${publication.brand_name}"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="calendar-item-content">
                        ${publication.title}
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
        if (event.target.classList.contains('show-details-btn')) {
            event.preventDefault();
            const calendarItem = event.target.closest('.calendar-item');
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

    document.addEventListener('DOMContentLoaded', function() {
        const contentTypes = new Set();
        const brands = new Set();

        // Process each calendar item once for efficiency
        document.querySelectorAll('.calendar-item').forEach(item => {
            const type = item.getAttribute('data-type');
            const brandList = item.getAttribute('data-brands').split(',').map(brand => brand.trim());

            if (type) {
                contentTypes.add(type);
            }
            brandList.forEach(brand => {
                if (brand) {
                    brands.add(brand);
                }
            });
        });

        const contentTypeFragment = document.createDocumentFragment();
        contentTypes.forEach(type => appendFilterOption(contentTypeFragment, 'contentType', type));
        document.getElementById('content-types-menu').appendChild(contentTypeFragment);

        const brandFragment = document.createDocumentFragment();
        brands.forEach(brand => appendFilterOption(brandFragment, 'brand', brand));
        document.getElementById('brands-menu').appendChild(brandFragment);
    });

    function appendFilterOption(fragment, filterName, value) {
        const li = document.createElement('li');
        li.className = 'checkbox';
        li.innerHTML = `
        <label class="checkbox-container">
            <input type="checkbox" name="${filterName}" value="${value}">
            <span class="checkmark"></span>
            <div class="facet-wrapper">
                <span class="facet-title">${value}</span>
                <span class="facet-count"></span>
            </div>
        </label>
    `;
        fragment.appendChild(li);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const hasPremiumContent = Array.from(document.querySelectorAll('.calendar-item')).some(item => item.getAttribute('data-premium') === 'true');
        const premiumCheckboxContainer = document.querySelector('.premium-checkbox');

        if (!hasPremiumContent) {
            premiumCheckboxContainer.style.display = 'none';
        } else {
            premiumCheckboxContainer.style.display = '';
        }

        attachFilterEventListeners();
        loadFilterStates();
        applyFilters();
        countAndUpdateFacetCounts();
    });

    function attachFilterEventListeners() {
        document.querySelectorAll('.content-navigator-menu input[name="contentType"]').
            forEach(input => {
                input.addEventListener('change', () => {
                    updateFilterStates();
                    countAndUpdateFacetCounts(false);
                });
            });

        document.querySelectorAll('.content-navigator-menu input[name="brand"]').
            forEach(input => {
                input.addEventListener('change', () => {
                    updateFilterStates();
                    countAndUpdateFacetCounts(false);
                });
            });

        const premiumCheckbox = document.getElementById('premium-checkbox');
        premiumCheckbox.addEventListener('change', () => {
            filterStates.premium = premiumCheckbox.checked;
            saveAndApplyFilters();
        });
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
        localStorage.setItem('filterStates', JSON.stringify({
            contentType: Array.from(filterStates.contentType),
            brand: Array.from(filterStates.brand),
            premium: filterStates.premium,
        }));
    }

    function loadFilterStates() {
        const savedStates = JSON.parse(localStorage.getItem('filterStates'));
        if (savedStates) {
            filterStates.contentType = new Set(savedStates.contentType || []);
            filterStates.brand = new Set(savedStates.brand || []);
            filterStates.premium = 'premium' in savedStates ? savedStates.premium : false;

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
            const brandMatch = item.dataset.brands.split(', ').some(brand => filterStates.brand.has(brand.trim())) || filterStates.brand.size === 0;
            const premiumMatch = filterStates.premium ? item.dataset.premium === 'true' : true;

            // Check for data-same-date condition
            const sameDate = item.dataset.sameDate === 'true';
            const sameDateVisible = sameDate ? filterStates.contentType.has(item.dataset.type) : true;

            const shouldDisplay = typeMatch && brandMatch && premiumMatch && sameDateVisible;

            item.style.display = shouldDisplay ? '' : 'none';
        });
    }

    function countAndUpdateFacetCounts(updateAll = true) {
        const contentTypeCounts = {};
        let brandCounts = {};
        let premiumCount = 0;

        // Determine the currently selected brands
        const selectedBrands = new Set([...document.querySelectorAll('[name="brand"]:checked')].map(input => input.value));
        const countAllBrands = selectedBrands.size === 0;

        document.querySelectorAll('.calendar-item').forEach(item => {
            const isVisible = item.style.display !== 'none';
            const itemBrands = item.getAttribute('data-brands').split(', ').map(brand => brand.trim());
            const matchesSelectedBrand = itemBrands.some(brand => selectedBrands.has(brand)) || countAllBrands;

            if (matchesSelectedBrand) {
                const type = item.getAttribute('data-type');
                if (type) {
                    contentTypeCounts[type] = (contentTypeCounts[type] || 0) + 1;
                }

                if (isVisible && item.getAttribute('data-premium') === 'true') {
                    premiumCount += 1;
                }

                if (updateAll) {
                    itemBrands.forEach(brand => {
                        brandCounts[brand] = (brandCounts[brand] || 0) + 1;
                    });
                }
            }
        });

        updateFacetDisplay('[name="contentType"]', contentTypeCounts);

        const premiumCheckboxCountDisplay = document.querySelector('.premium-checkbox .facet-count');
        if (premiumCheckboxCountDisplay) {
            premiumCheckboxCountDisplay.textContent = `(${premiumCount})`;
        }

        if (updateAll) {
            updateFacetDisplay('[name="brand"]', brandCounts);
        }
    }

    function updateFacetDisplay(selector, counts) {
        document.querySelectorAll(selector).forEach(input => {
            const count = counts[input.value] || 0;
            const countDisplay = input.closest('.checkbox-container').querySelector('.facet-count');
            if (countDisplay) {
                countDisplay.textContent = `(${count})`;
            }
        });
    }
}
