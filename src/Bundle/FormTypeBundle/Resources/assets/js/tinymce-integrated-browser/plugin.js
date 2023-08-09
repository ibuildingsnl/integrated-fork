import tinymce from 'tinymce';

import * as Options from './api/options';
import * as Buttons from './ui/buttons';

tinymce.PluginManager.add('integratedbrowser', (editor) => {
    Options.register(editor);
    Buttons.register(editor);
});

