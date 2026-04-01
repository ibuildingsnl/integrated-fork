import React, {useCallback, useMemo, useState} from 'react';
import {useRecoilValue} from 'recoil';
import {__} from '@wordpress/i18n';

// Yoast dependencies
import KeywordInput from 'yoast-components/composites/Plugin/Shared/components/KeywordInput';
import SnippetEditor from '@yoast/search-metadata-previews/snippet-editor/SnippetEditor';
import {
    DEFAULT_MODE,
    MODE_DESKTOP,
} from '@yoast/search-metadata-previews/snippet-preview/constants';

// Internal dependencies
import SeoAnalysis from './SeoAnalysis';
import {useConfiguration} from '../provider/ConfigurationProvider';
import useIntegratedFields from '../hooks/useIntegratedFields';
import titleTemplateState from '../state/titleTemplateState';
import faviconSrcState from '../state/faviconSrcState';
import editorState from '../state/editorState';
import {
    containsSeoPlaceholder,
    mapSeoPlaceholdersToReplacementVariables,
    resolveSeoPlaceholders,
} from '../helper/seoPlaceholders';

const SeoTab = () => {
    const {configuration} = useConfiguration();
    const titleTemplate = useRecoilValue(titleTemplateState);
    const faviconSrc = useRecoilValue(faviconSrcState);
    const editorData = useRecoilValue(editorState);
    const {updateEditorData} = useIntegratedFields();
    const [mode, setMode] = useState(DEFAULT_MODE);
    const replacementValues = useMemo(
        () => ({
            title: configuration.title,
            siteTitle: configuration.brandName,
            separator: configuration.titleSeparator,
            slug: editorData.slug || configuration.uriPathSegment,
            channel: configuration.channelName || configuration.brandName,
        }),
        [configuration.brandName, configuration.channelName, configuration.title, configuration.titleSeparator, configuration.uriPathSegment, editorData.slug],
    );
    const replacementVariables = useMemo(
        () => mapSeoPlaceholdersToReplacementVariables(configuration, replacementValues),
        [configuration, replacementValues],
    );

    const onUpdateKeyword = useCallback(
        (value = '') => {
            updateEditorData('focusKeyword', value);
        },
        [updateEditorData],
    );

    const onEditorChange = useCallback(
        (key, data) => {
            if (key === 'mode') {
                setMode(data);
            } else {
                updateEditorData(key, data);
            }

            const event = new CustomEvent('editorChange');
            document.dispatchEvent(event);
        },
        [updateEditorData],
    );

    /**
     * Process field values before giving them to the preview.
     * We modify the title in the preview according to the template we generated in the constructor.
     */
    const mapEditorDataToPreview = useCallback(
        ({title, description, url}) => {
            const resolvedTitle = resolveSeoPlaceholders(title, replacementValues);
            const resolvedDescription = resolveSeoPlaceholders(description, replacementValues);

            return {
                title: containsSeoPlaceholder(title) ? resolvedTitle : titleTemplate.replace('{title}', resolvedTitle),
                url: configuration.baseUrl + configuration.pageUrl,
                description: resolvedDescription,
            };
        },
        // eslint-disable-next-line react-hooks/exhaustive-deps
        [configuration.baseUrl, configuration.brandName, configuration.channelName, configuration.pageUrl, configuration.title, configuration.titleSeparator, configuration.uriPathSegment, editorData.slug, titleTemplate],
    );

    return (
        <React.Fragment>
            <div className="yoast-seo-keyphrase-editor-wrapper">
                <KeywordInput
                    id="focus-keyphrase"
                    keyword={editorData.focusKeyword}
                    onChange={onUpdateKeyword}
                    onRemoveKeyword={onUpdateKeyword}
                    label={__('Focus keyphrase', 'yoast-components')}
                    ariaLabel={__('Focus keyphrase', 'yoast-components')}
                />
            </div>
            <div className="yoast-seo-snippet-editor-wrapper">
                <SnippetEditor
                    isAmp={configuration.isAmp}
                    // Provide the initial state
                    data={{
                        title: editorData.title,
                        description: editorData.description,
                        slug: editorData.slug,
                    }}
                    siteName={configuration.brandName}
                    locale={configuration.uiLocale}
                    keyword={editorData.focusKeyword}
                    onChange={onEditorChange}
                    hasPaperStyle={false}
                    mode={mode}
                    baseUrl={configuration.baseUrl}
                    faviconSrc={faviconSrc}
                    mobileImageSrc={configuration.featuredImageSrc}
                    replacementVariables={replacementVariables}
                    mapEditorDataToPreview={mapEditorDataToPreview}
                />
            </div>
            <SeoAnalysis/>
        </React.Fragment>
    );
};

export default React.memo(SeoTab);
