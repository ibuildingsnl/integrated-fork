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
    const debouncedLoadPageContent = useRef(debounce(() => loadPageContent(), 500)).current;

    /**
     * Update hidden Integrated editable fields to forward changes to the backend
     */
    const updateIntegratedFields = useCallback(
        (key, data) => {

            if (key === 'title') {
                key = 'titleOverride';
            }

            editorFieldMapping[key].value = data

            // Keep the real form field current so a quick save cannot outrun the debounce.
            debouncedLoadPageContent();
        },
        // eslint-disable-next-line react-hooks/exhaustive-deps
        []
    );

    const updateEditorData = useCallback(
        (key, value) => {
            setEditorData((prev) => ({ ...prev, [key]: value }));
            // Update hidden Integrated fields immediately so the submitted form carries the latest value.
            updateIntegratedFields(key, value);
        },
        // eslint-disable-next-line react-hooks/exhaustive-deps
        []
    );

    return { updateEditorData };
};

export default useIntegratedFields;
