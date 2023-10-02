import React, { useEffect, useState } from 'react';
import ReactDOM from 'react-dom';
import { ThemeProvider } from 'styled-components';
import { RecoilRoot } from 'recoil';

import './Main.scss';
import IntegratedYoastApp from './components/IntegratedYoastApp';
import { I18nProvider } from './provider/I18nProvider';
import { ConfigurationProvider } from './provider/ConfigurationProvider';

function AppInitializer() {
    const [isReady, setIsReady] = useState(false);
    const modalContainer = document.querySelector('.publication-settings-aside.seo-settings');
    const applicationContainer = document.querySelector('#yoast-app');
    const snippetEditorContainer = document.querySelector('.seo-snippet-editor');
    const titleField = document.querySelector('#integrated_content_title');
    const uriPathSegmentField = document.querySelector('#integrated_content_slug');
    const titleOverrideField = snippetEditorContainer.querySelector('#integrated_content_seoMetadata_metaTitle');
    const descriptionField = snippetEditorContainer.querySelector('#integrated_content_seoMetadata_metaDescription');
    const focusKeywordField = snippetEditorContainer.querySelector('#integrated_content_seoMetadata_focusKeyphrase');

    const editorFieldMapping = {
        title: titleField,
        titleOverride: titleOverrideField,
        focusKeyword: focusKeywordField,
        description: descriptionField,
        slug: uriPathSegmentField,
    };

    useEffect(() => {
        function handleTinyMCELoaded() {
            setIsReady(true);
        }

        window.addEventListener('tinyMCEInitialized', handleTinyMCELoaded);

        return () => {
            // Cleanup
            window.removeEventListener('tinyMCEInitialized', handleTinyMCELoaded);
        };
    }, []);

    if (!isReady) {
        return null; // or render a loading spinner, etc.
    }

    const configuration = JSON.parse(applicationContainer.dataset.configuration);

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
ReactDOM.render(<AppInitializer />, applicationContainer);
