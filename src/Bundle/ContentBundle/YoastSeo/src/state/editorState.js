import { atom } from 'recoil';

const editorState = atom({
    key: 'editorState',
    default: {
        title: '',
        sourceTitle: '',
        description: '',
        slug: '',
        url: '',
        focusKeyword: '',
    },
});

export default editorState;
