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
            publicationEntry.className = 'quick-edit-link calendar-item ' + publication.published + ' ' + publication.type;
            publicationEntry.href = '/admin/content/' + publication.id;
            publicationEntry.dataset.time = publication.time;
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
                content.style = 'border-color: ' + colorVariable + ';margin: 0 -3px 0.5rem -3px;'
            }
        });
        publicationEntry.addEventListener('mouseout', function (ev) {
            document.querySelectorAll('.calendar-item').forEach((i) => i.style = '');
        });
    }
}
