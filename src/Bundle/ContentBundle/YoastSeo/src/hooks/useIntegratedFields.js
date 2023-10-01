import { useCallback, useRef } from 'react';
import debounce from 'lodash.debounce';
import { useSetRecoilState } from 'recoil';

import { useConfiguration } from '../provider/ConfigurationProvider';
import usePageContent from './usePageContent';
import editorState from '../state/editorState';

const useIntegratedFields = () => {
    const { editorFieldMapping } = useConfiguration();
    const { loadPageContent } = usePageContent();
    const setEditorData = useSetRecoilState(editorState);

    /**
     * Update hidden Integrated editable fields to forward changes to the backend
     */
    const updateIntegratedFields = useCallback(
        (key, data) => {
            console.log(key);
            console.log(data);

            if (key === 'title') {
                key = 'titleOverride';
            }

            editorFieldMapping[key].value = data

            // Request new page content and analysis after changes were applied
            loadPageContent();
        },
        // eslint-disable-next-line react-hooks/exhaustive-deps
        []
    );

    const debouncedUpdateIntegratedFields = useRef(debounce((key, value) => updateIntegratedFields(key, value), 500)).current;

    const updateEditorData = useCallback(
        (key, value) => {
            setEditorData((prev) => ({ ...prev, [key]: value }));
            // Update hidden Integrated fields from the changed values of the editor fields.
            debouncedUpdateIntegratedFields(key, value);
        },
        // eslint-disable-next-line react-hooks/exhaustive-deps
        []
    );

    return { updateEditorData };
};

export default useIntegratedFields;
