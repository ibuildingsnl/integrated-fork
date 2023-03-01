if (typeof(window.previewUrl) !== 'undefined') {
    const frame = document.createElement('div');
    frame.innerHTML = '' +
        '<div class="editor-item-wrapper show">\n' +
        '    <div class="editor-item-header">\n' +
        '        <h3 class="editor-item-title">\n' +
        '            <span class="editor-item-icon">Preview</span>\n' +
        '        </h3>\n' +
        '        <i class="iconoir-nav-arrow-up"></i>\n' +
        '    </div>\n' +
        '    <div class="editor-item-list">\n' +
        '        <div class="editor-item-list-container">\n' +
        '            <iframe class="editor-preview" src="' + window.previewUrl + '"></iframe>\n' +
        '        </div>\n' +
        '    </div>\n' +
        '</div>\n';
    document.querySelector('section.editor').appendChild(frame);
}
