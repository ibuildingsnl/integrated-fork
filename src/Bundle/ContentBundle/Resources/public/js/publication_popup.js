const formActions = document.querySelector('.form-actions-extra');
// @todo adapt
const publishActions = document.querySelectorAll('.publishActions .publish_form');

if (publishActions.length > 0) {
    // add top-bar menu item
    const publish = document.createElement('div');
    publish.className = 'publish';
    publish.innerHTML = '<ul class="options-no-bg">\n' +
        '    <li class="dropdown close-outside" data-underlay="no">\n' +
        '        <a data-toggle="dropdown" role="button" class="btn btn-dropdown">\n' +
        '            <span>Publish</span>\n' +
        '            <i class="iconoir-nav-arrow-down"></i>\n' +
        '        </a>\n' +
        '        <ul aria-labelledby="drop_2" role="menu" class="dropdown-menu alt" id="menu_publish">\n' +
        '        </ul>\n' +
        '    </li>\n' +
        '</ul>';
    formActions.prepend(publish);

    // add publishing options to newly added dropdown menu
    const menu = document.querySelector('#menu_publish');
    publishActions.forEach(function (action) {
        const item = document.createElement('li');
        menu.appendChild(item);
        const a = document.createElement('a');
        a.className = 'popup-link';
        a.innerText = action.querySelector('label').innerText;
        a.addEventListener('click', function () {
            // open popup
            publishActions.forEach((a) => a.querySelector('.publishable').className = 'publishable');
            action.querySelector('.publishable').className = 'publishable display';
            document.querySelector('.publishActions').style = 'position: fixed; ' +
                'height: 100%; ' +
                'left:0; ' +
                'right: 0; ' +
                'top: 0; ' +
                'background: rgba(0, 0, 0, 0.4)';
            action.querySelectorAll('.aside-item-list').forEach((img) => img.style = 'display: block; min-height: 40px; height: fit-content;');
            const textarea = action.querySelector('textarea');
            if (textarea) {
                textarea.value = getPublishIntro(160);
            }
            const title = action.querySelector('[name$="[title]"]');
            if (title) {
                title.value = document.querySelector('#integrated_content_title')?.value;
            }
            const image = action.querySelector('[image]');
            if (image) {
                //@todo assign featured image
                image.value = document.querySelector('[featuredImage]')?.value;
            }
        });
        item.appendChild(a);
        action.querySelector('[for$="_use"]').style.display = 'none';
        action.querySelector('.button-ok')?.addEventListener(
            'click',
            function () {
                action.querySelector('[name$="[use]"]').click();
                document.querySelector('#integrated_content_actions_save')?.click();
            }
        );
    });

    // close on outside click
    // @todo remove global event trigger from media gallery to turn this back on
    // document.addEventListener('click', function (e) {
    //     if (!e.target.closest('.publishable') && !e.target.closest('.popup-link')) {
    //         closePublishPopup();
    //     }
    // });
    document.querySelectorAll('.button-cancel').forEach(
        (e) => e.addEventListener('click', closePublishPopup)
    );
}

function closePublishPopup() {
    publishActions.forEach((a) => a.querySelector('.publishable').className = 'publishable');
    document.querySelector('.publishActions').style = '';
}

function cropPublishIntro(fullIntro, maxLength) {
    const length = fullIntro.indexOf("\n");
    return fullIntro.substring(0, Math.min(length, maxLength)) + (length > maxLength ? '...' : '');
}

function getPublishIntro(maxLength) {
    return cropPublishIntro(tinymce.get("integrated_content_content").getContent({ format: "text" }), maxLength);
}
