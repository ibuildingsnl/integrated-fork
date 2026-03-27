import React, { useEffect, useState } from 'react';
import ReactDOM from 'react-dom';
import { ThemeProvider } from 'styled-components';
import { RecoilRoot } from 'recoil';
import 'draft-js-mention-plugin/lib/plugin.css';
import './seo-editor.css';

import IntegratedYoastApp from './components/IntegratedYoastApp';
import { I18nProvider } from './provider/I18nProvider';
import { ConfigurationProvider } from './provider/ConfigurationProvider';

function AppInitializer() {
    const [isReady, setIsReady] = useState(false);
    const applicationContainer = document.querySelector('#yoast-app');
    const configuration = JSON.parse(applicationContainer.dataset.configuration || '{}');
    const fieldSelectors = configuration.fieldSelectors || {};
    const selectField = (key) => fieldSelectors[key] ? document.querySelector(fieldSelectors[key]) : null;
    const modalContainer = document.querySelector('.publication-settings-aside.seo-settings');
    const snippetEditorContainer = document.querySelector('.seo-metadata-fields');
    const titleField = selectField('title');
    const uriPathSegmentField = selectField('slug');
    const titleOverrideField = selectField('titleOverride');
    const descriptionField = selectField('description');
    const focusKeywordField = selectField('focusKeyword');
    const seoScoreField = selectField('seoScore');
    const readabilityScoreField = selectField('readabilityScore');
    const contentField = selectField('content');

    if (!snippetEditorContainer || !titleField || !uriPathSegmentField || !titleOverrideField || !descriptionField || !focusKeywordField) {
        return null;
    }

    const editorFieldMapping = {
        title: titleField,
        titleOverride: titleOverrideField,
        focusKeyword: focusKeywordField,
        description: descriptionField,
        slug: uriPathSegmentField,
        seoScore: seoScoreField,
        readabilityScore: readabilityScoreField,
        content: contentField,
    };

    useEffect(() => {
        if (!contentField) {
            setIsReady(true);

            return undefined;
        }

        const editor = window.tinymce ? tinymce.get(contentField.id) : null;
        if (editor) {
            setIsReady(true);

            return undefined;
        }

        function handleTinyMCELoaded() {
            setIsReady(true);
        }

        window.addEventListener('tinyMCEInitialized', handleTinyMCELoaded);

        return () => {
            // Cleanup
            window.removeEventListener('tinyMCEInitialized', handleTinyMCELoaded);
        };
    }, [contentField]);

    if (!isReady) {
        return null; // or render a loading spinner, etc.
    }

    return (
        <I18nProvider translationsUrl={configuration.translationsUrl}>
            <ConfigurationProvider
                modalContainer={modalContainer}
                editorFieldMapping={editorFieldMapping}
                configuration={configuration}
            >
                <ThemeProvider theme={{ isRtl: false }}>
                    <RecoilRoot>
                        <IntegratedYoastApp />
                    </RecoilRoot>
                </ThemeProvider>
            </ConfigurationProvider>
        </I18nProvider>
    );
}

const applicationContainer = document.querySelector('#yoast-app');

if (applicationContainer) {
    ReactDOM.render(<AppInitializer />, applicationContainer);
}
