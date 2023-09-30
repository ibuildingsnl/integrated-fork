import React from 'react';
import ReactDOM from 'react-dom';
import { ThemeProvider } from 'styled-components';
import { RecoilRoot } from 'recoil';

import './Main.scss';
import IntegratedYoastApp from './components/IntegratedYoastApp';
import { I18nProvider } from './provider/I18nProvider';
import { ConfigurationProvider } from './provider/ConfigurationProvider';

/**
 * This script creates the app for the Yoast preview mode in Integrated CMS
 */
((document, window) => {
    const applicationContainer = document.querySelector('#yoast-app');
    const modalContainer = document.querySelector('.publication-settings-aside.seo-settings');
    const snippetEditorContainer = document.querySelector('.seo-analysis-container');
    const titleField = snippetEditorContainer.getElementById('#integrated_content_title');
    const titleOverrideField = snippetEditorContainer.getElementById('#integrated_content_seoMetadata_metaTitle');
    const descriptionField = snippetEditorContainer.getElementById('#integrated_content_seoMetadata_metaDescription');
    const uriPathSegmentField = snippetEditorContainer.getElementById('#integrated_content_seoMetadata_metaSlug');
    const focusKeywordField = snippetEditorContainer.getElementById('#integrated_content_seoMetadata_focusKeyphrase');

    const editorFieldMapping = {
        title: titleField,
        titleOverride: titleOverrideField,
        focusKeyword: focusKeywordField,
        description: descriptionField,
        slug: uriPathSegmentField,
    };

    /**
     * After everything is loaded initialize providers and app
     */
    window.onload = () => {
        const configuration = JSON.parse(applicationContainer.dataset.configuration);

        ReactDOM.render(
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
            </I18nProvider>,
            applicationContainer
        );
    };
})(document, window);
