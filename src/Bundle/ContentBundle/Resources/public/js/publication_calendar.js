if (typeof publicationSchedule === 'object') {
    const brandColors = {
        'facebook': '#1877f2',
        'linkedin': '#0077b5',
        'x': '#000',
        'instagram': '#e4405f',
        'woodwing': '#f15429',
    };

    for (const publication of publicationSchedule) {
        const column = document.querySelector('.day.column[data-date="' + publication.date + '"]');
        const existingItem = column.querySelector('.calendar-item[data-id="' + publication.id + '"]');

        let shouldAddIcon = false;

        if (existingItem) {
            const publishTimeText = existingItem.querySelector('.publish-time').textContent;

            if (publishTimeText === publication.display_time) {
                shouldAddIcon = true;
            }
        }

        let colorVariable = brandColors.hasOwnProperty(publication.icon) ? brandColors[publication.icon] : '#000000';

        if (shouldAddIcon) {
            const headingLeftDiv = existingItem.querySelector('.heading-left');
            const iconClass = 'icon iconoir-' + publication.icon;
            const existingIcon = headingLeftDiv.querySelector(`.${iconClass.replace(/\s/g, '.')}`);

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
                errorEntry.innerHTML = '<b>' + publication.name + ' ' + publication.brand_name + '</b>:<br>' + publication.response;
                const calContent = existingItem.querySelector('.calendar-wrap');
                calContent.appendChild(errorEntry);
            }
        } else {
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
            publicationEntry.dataset.time = publication.time;
            publicationEntry.dataset.type = publication.typename;
            publicationEntry.dataset.brands = publication.brand_name;
            publicationEntry.dataset.premium = publication.premium;
            publicationEntry.innerHTML = generatePublicationHTML(publication, colorVariable);

            attachMouseEvents(publicationEntry, colorVariable, publication);
        }
    }

    function generatePublicationHTML(publication, colorVariable) {

        const errorMessage = publication.published === 'failed'
            ? `<div class="calendar-error">${publication.response}</div>`
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
                    </div>
                    ${errorMessage}
                </div>`;
    }

    function attachMouseEvents(publicationEntry, colorVariable, publication) {
        publicationEntry.addEventListener('mouseover', function(ev) {
            const content = document.querySelector('.calendar-item[data-id="'+publication.id+'"]');
            if (content) {
                content.style.borderColor = colorVariable;
                content.style.margin = '0 -3px 0.5rem -3px';
            }
        });
        publicationEntry.addEventListener('mouseout', function (ev) {
            const items = document.querySelectorAll('.calendar-item');
            items.forEach((i) => {
                i.style.borderColor = '';
                i.style.margin = '';
            });
        });
    }

    let filterStates = {
        contentType: new Set(),
        brand: new Set(),
        premium: true,
    };

    document.addEventListener("DOMContentLoaded", function() {
        const contentTypes = new Set();
        document.querySelectorAll('.calendar-item').forEach(item => {
            contentTypes.add(item.getAttribute('data-type'));
        });
        const contentTypesMenu = document.getElementById('content-types-menu');
        contentTypes.forEach(type => {
            contentTypesMenu.innerHTML += `<li class="checkbox">
            <label class="checkbox-container">
                <input type="checkbox" name="contentType" value="${type}">
                <span class="checkmark"></span>
                <div class="facet-wrappper">
                    <span class="facet-title">${type}</span>
                    <span class="facet-count"></span>
                </div>
            </label>
        </li>`;
        });

        const brands = new Set();
        document.querySelectorAll('.calendar-item').forEach(item => {
            item.getAttribute('data-brands').split(', ').forEach(brand => {
                const trimmedBrand = brand.trim();
                if (trimmedBrand) {
                    brands.add(trimmedBrand);
                }
            });
        });
        const brandsMenu = document.getElementById('brands-menu');
        brands.forEach(brand => {
            brandsMenu.innerHTML += `<li class="checkbox">
            <label class="checkbox-container">
                <input type="checkbox" name="brand" value="${brand}">
                <span class="checkmark"></span>
                <div class="facet-wrappper">
                    <span class="facet-title">${brand}</span>
                    <span class="facet-count"></span>
                </div>
            </label>
        </li>`;
        });

    });

    document.addEventListener("DOMContentLoaded", function() {
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
        document.querySelectorAll('[name="contentType"], [name="brand"]').forEach(input => {
            input.addEventListener('change', updateFilterStates);
        });

        const premiumCheckbox = document.getElementById('premium-checkbox');
        premiumCheckbox.checked = filterStates.premium;
        premiumCheckbox.addEventListener('change', () => {
            filterStates.premium = premiumCheckbox.checked;
            saveAndApplyFilters();
        });
    }

    function updateFilterStates() {
        filterStates.contentType = new Set([...document.querySelectorAll('[name="contentType"]:checked')].map(input => input.value));
        filterStates.brand = new Set([...document.querySelectorAll('[name="brand"]:checked')].map(input => input.value));
        saveAndApplyFilters();
    }

    function saveAndApplyFilters() {
        saveFilterStates();
        applyFilters();
        countAndUpdateFacetCounts();
    }

    function saveFilterStates() {
        localStorage.setItem('filterStates', JSON.stringify({
            contentType: Array.from(filterStates.contentType),
            brand: Array.from(filterStates.brand),
            premium: filterStates.premium
        }));
    }

    function loadFilterStates() {
        const savedStates = JSON.parse(localStorage.getItem('filterStates'));

        console.log(savedStates);

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
            const typeMatch = filterStates.contentType.size === 0 || filterStates.contentType.has(item.getAttribute('data-type'));
            const brandMatch = filterStates.brand.size === 0 || item.getAttribute('data-brands').split(', ').some(brand => filterStates.brand.has(brand.trim()));
            const premiumMatch = filterStates.premium ? item.getAttribute('data-premium') === 'true' : true;

            item.style.display = (typeMatch && brandMatch && premiumMatch) ? '' : 'none';
        });
    }

    function countAndUpdateFacetCounts() {
        const contentTypeCounts = {};
        const brandCounts = {};
        let premiumCount = 0;

        document.querySelectorAll('.calendar-item').forEach(item => {
            if (item.style.display === 'none') return;

            const type = item.getAttribute('data-type');
            if (type) {
                contentTypeCounts[type] = (contentTypeCounts[type] || 0) + 1;
            }

            const brands = item.getAttribute('data-brands').split(', ').map(brand => brand.trim());
            brands.forEach(brand => {
                brandCounts[brand] = (brandCounts[brand] || 0) + 1;
            });

            if (item.getAttribute('data-premium') === 'true') {
                premiumCount += 1;
            }
        });

        document.querySelectorAll('[name="contentType"]').forEach(input => {
            const count = contentTypeCounts[input.value] || 0;
            const countDisplay = input.closest('.checkbox-container').querySelector('.facet-count');
            if (countDisplay) {
                countDisplay.textContent = `(${count})`;
            }
        });

        document.querySelectorAll('[name="brand"]').forEach(input => {
            const count = brandCounts[input.value] || 0;
            const countDisplay = input.closest('.checkbox-container').querySelector('.facet-count');
            if (countDisplay) {
                countDisplay.textContent = `(${count})`;
            }
        });

        const premiumCheckboxCountDisplay = document.querySelector('.premium-checkbox .facet-count');
        if (premiumCheckboxCountDisplay) {
            premiumCheckboxCountDisplay.textContent = `(${premiumCount})`;
        }
    }

}
