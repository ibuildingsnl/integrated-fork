import React, {useEffect, useMemo} from 'react';
import {debounce} from 'lodash';
import {__} from '@wordpress/i18n';
import {useRecoilValue, useSetRecoilState} from 'recoil';

// Yoast dependencies
import colors from '@yoast/style-guide/colors.json';
import Loader from '@yoast/components/Loader';
import SvgIcon from '@yoast/components/SvgIcon';
import Tabs from '@yoast/components/Tabs';

// Internal dependencies
import ReadabilityAnalysis from './ReadabilityAnalysis';
import SocialPreviews from './SocialPreviews';
import SeoTab from './SeoTab';
import usePageContent from '../hooks/usePageContent';
import errorState from '../state/errorState';
import editorState from '../state/editorState';
import {useConfiguration} from '../provider/ConfigurationProvider';
import useAnalysis from '../hooks/useAnalysis';
import analysisState from '../state/analysisState';

const IntegratedYoastApp = () => {
    const {configuration} = useConfiguration();
    const {loadPageContent} = usePageContent();
    const {isAnalyzing} = useAnalysis();
    const error = useRecoilValue(errorState);
    const {seoResults, readabilityResults} = useRecoilValue(analysisState);
    const setEditorData = useSetRecoilState(editorState);
    const SeoTabContent = useMemo(() => <SeoTab/>, []);
    const ReadabilityTabContent = useMemo(() => <ReadabilityAnalysis/>, []);
    const SocialPreviewsTabContent = useMemo(() => <SocialPreviews/>, []);

    const debouncedLoadPageContent = useMemo(
        () => debounce(loadPageContent, 300), [loadPageContent]); // 300ms delay

    // Trigger initial page load for analysis
    useEffect(() => {
        setEditorData({
            title: configuration.titleOverride || configuration.title,
            description: configuration.description || '',
            slug: configuration.uriPathSegment,
            url: configuration.pageUrl + configuration.pageUrl,
            focusKeyword: configuration.focusKeyword,
        });
        loadPageContent();

        function handleEditorChange() {
            debouncedLoadPageContent();
        }

        const titleInput = document.querySelector('#integrated_content_title');
        const titleOverrideInput = document.querySelector(
            '#integrated_content_seoMetadata_metaTitle');

        let shouldLinkInputs = true;

        document.addEventListener('editorChange', () => {
            setTimeout(() => {
                if (titleInput.value !== titleOverrideInput.value) {
                    shouldLinkInputs = false;
                } else {
                    shouldLinkInputs = true;
                }
            }, 1000);
        });


        titleInput.addEventListener('input', () => {
            if (shouldLinkInputs) {
                titleOverrideInput.value = titleInput.value;

                setEditorData((prev) => ({
                    ...prev,
                    title: titleOverrideInput.value,
                }));
            }
        });

        const editor = tinymce.get('integrated_content_content');
        if (editor) {
            editor.on('Change', handleEditorChange);
            editor.on('KeyUp', handleEditorChange);  // You might also want to reload content on key up
        }

        return () => {
            if (editor) {
                editor.off('Change', handleEditorChange);
                editor.off('KeyUp', handleEditorChange);
            }
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return (
        <React.Fragment>
            <Loader className={isAnalyzing ? '' : 'yoast-loader--stop'}/>
            {error && <div className="yoast-seo__error">{error}</div>}
            {
                <Tabs
                    items={[
                        {
                            id: 'seo',
                            label: (
                                <React.Fragment>
                                    <SvgIcon
                                        icon={isAnalyzing ?
                                            'loading-spinner' :
                                            seoResults.icon}
                                        color={colors['$color_' +
                                        seoResults.color]}
                                        size="14px"
                                    />
                                    <span>{__('SEO', 'yoast-components')}</span>
                                </React.Fragment>
                            ),
                            content: SeoTabContent,
                        },
                        {
                            id: 'readability',
                            label: (
                                <React.Fragment>
                                    <SvgIcon
                                        icon={isAnalyzing ?
                                            'loading-spinner' :
                                            readabilityResults.icon}
                                        color={colors['$color_' +
                                        readabilityResults.color]}
                                        size="14px"
                                    />
                                    <span>{__('Readability',
                                        'yoast-components')}</span>
                                </React.Fragment>
                            ),
                            content: ReadabilityTabContent,
                        },
                        {
                            id: 'social',
                            label: __('Social', 'yoast-components'),
                            content: SocialPreviewsTabContent,
                        },
                    ]}
                    tabsFontSize="1em"
                    tabsBaseWidth="auto"
                />
            }
        </React.Fragment>
    );
};

export default React.memo(IntegratedYoastApp);
