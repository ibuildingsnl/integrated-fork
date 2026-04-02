import React, {useEffect, useMemo, useRef} from 'react';
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

const META_DESCRIPTION_MAX_LENGTH = 156;

const IntegratedYoastApp = () => {
    const {configuration, editorFieldMapping} = useConfiguration();
    const {loadPageContent} = usePageContent();
    const {isAnalyzing} = useAnalysis();
    const error = useRecoilValue(errorState);
    const {seoResults, readabilityResults} = useRecoilValue(analysisState);
    const setEditorData = useSetRecoilState(editorState);
    const descriptionManuallyEditedRef = useRef(false);
    const SeoTabContent = useMemo(() => <SeoTab/>, []);
    const ReadabilityTabContent = useMemo(() => <ReadabilityAnalysis/>, []);
    const SocialPreviewsTabContent = useMemo(() => <SocialPreviews/>, []);

    const debouncedLoadPageContent = useMemo(
        () => debounce(loadPageContent, 300), [loadPageContent]); // 300ms delay

    // Trigger initial page load for analysis
    useEffect(() => {
        const sourceTitle = getFieldValue(editorFieldMapping.title) || configuration.title || '';
        const initialTitle = resolveInitialTitle(editorFieldMapping, configuration);
        const initialDescription = editorFieldMapping.description ? editorFieldMapping.description.value : (configuration.description || '');
        const generatedDescription = buildAutoDescription(editorFieldMapping, configuration);
        const resolvedDescription = initialDescription || generatedDescription;

        descriptionManuallyEditedRef.current = initialDescription.trim() !== '';

        setEditorData({
            title: initialTitle,
            sourceTitle: sourceTitle,
            description: resolvedDescription,
            slug: editorFieldMapping.slug ? editorFieldMapping.slug.value : configuration.uriPathSegment,
            url: configuration.pageUrl,
            focusKeyword: editorFieldMapping.focusKeyword ? editorFieldMapping.focusKeyword.value : configuration.focusKeyword,
        });

        if (editorFieldMapping.description && editorFieldMapping.description.value !== resolvedDescription) {
            editorFieldMapping.description.value = resolvedDescription;
        }

        loadPageContent();

        const syncAutoDescription = () => {
            if (descriptionManuallyEditedRef.current || !editorFieldMapping.description) {
                return;
            }

            const nextDescription = buildAutoDescription(editorFieldMapping, configuration);

            setEditorData((prev) => {
                if (prev.description === nextDescription) {
                    return prev;
                }

                return {
                    ...prev,
                    description: nextDescription,
                };
            });

            if (editorFieldMapping.description.value !== nextDescription) {
                editorFieldMapping.description.value = nextDescription;
            }
        };

        function handleEditorChange() {
            syncAutoDescription();
            debouncedLoadPageContent();
        }

        const titleInput = editorFieldMapping.title;
        const titleOverrideInput = editorFieldMapping.titleOverride;
        const introInput = editorFieldMapping.intro;
        const pageDescriptionInput = editorFieldMapping.pageDescription;

        let shouldLinkInputs = shouldLinkTitles(titleInput, titleOverrideInput);

        const syncTitleOverride = () => {
            if (!titleInput || !titleOverrideInput) {
                return;
            }

            const nextSourceTitle = titleInput.value || '';
            const shouldSyncTitle = titleOverrideInput.value.trim() === '' || shouldLinkInputs;

            setEditorData((prev) => {
                let next = prev;
                let hasChanges = false;

                if (prev.sourceTitle !== nextSourceTitle) {
                    next = {
                        ...next,
                        sourceTitle: nextSourceTitle,
                    };
                    hasChanges = true;
                }

                if (!shouldSyncTitle) {
                    return hasChanges ? next : prev;
                }

                if (titleOverrideInput.value !== nextSourceTitle) {
                    titleOverrideInput.value = nextSourceTitle;
                }

                if (next.title === nextSourceTitle) {
                    return hasChanges ? next : prev;
                }

                next = {
                    ...next,
                    title: nextSourceTitle,
                };
                hasChanges = true;

                if (!hasChanges) {
                    return prev;
                }

                return next;
            });
        };

        const handleEditorLinkState = () => {
            setTimeout(() => {
                shouldLinkInputs = shouldLinkTitles(titleInput, titleOverrideInput);
                syncTitleOverride();
            }, 1000);
        };

        const handleDescriptionLinkState = (event) => {
            if (!event.detail || event.detail.key !== 'description') {
                return;
            }

            const descriptionValue = typeof event.detail.value === 'string' ? event.detail.value : '';
            descriptionManuallyEditedRef.current = descriptionValue.trim() !== '';

            if (!descriptionManuallyEditedRef.current) {
                syncAutoDescription();
            }
        };

        document.addEventListener('editorChange', handleEditorLinkState);
        document.addEventListener('editorChange', handleDescriptionLinkState);

        if (titleInput && titleOverrideInput) {
            titleInput.addEventListener('input', syncTitleOverride);
            titleInput.addEventListener('change', syncTitleOverride);
            titleInput.addEventListener('keyup', syncTitleOverride);
            syncTitleOverride();
        }

        if (introInput) {
            introInput.addEventListener('input', handleEditorChange);
        }

        if (pageDescriptionInput) {
            pageDescriptionInput.addEventListener('input', handleEditorChange);
        }

        const editor = editorFieldMapping.content && window.tinymce ? tinymce.get(editorFieldMapping.content.id) : null;
        if (editor) {
            editor.on('Change', handleEditorChange);
            editor.on('KeyUp', handleEditorChange);  // You might also want to reload content on key up
        } else if (editorFieldMapping.content) {
            editorFieldMapping.content.addEventListener('input', handleEditorChange);
        }

        return () => {
            document.removeEventListener('editorChange', handleEditorLinkState);
            document.removeEventListener('editorChange', handleDescriptionLinkState);
            if (titleInput && titleOverrideInput) {
                titleInput.removeEventListener('input', syncTitleOverride);
                titleInput.removeEventListener('change', syncTitleOverride);
                titleInput.removeEventListener('keyup', syncTitleOverride);
            }
            if (introInput) {
                introInput.removeEventListener('input', handleEditorChange);
            }
            if (pageDescriptionInput) {
                pageDescriptionInput.removeEventListener('input', handleEditorChange);
            }
            if (editor) {
                editor.off('Change', handleEditorChange);
                editor.off('KeyUp', handleEditorChange);
            } else if (editorFieldMapping.content) {
                editorFieldMapping.content.removeEventListener('input', handleEditorChange);
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

function buildAutoDescription(editorFieldMapping, configuration) {
    const intro = getPlainTextValue(editorFieldMapping.intro);
    const pageDescription = getPlainTextValue(editorFieldMapping.pageDescription);
    const content = getPlainTextValue(editorFieldMapping.content);
    const fallback = intro || pageDescription || content || configuration.description || '';

    if (fallback.length <= META_DESCRIPTION_MAX_LENGTH) {
        return fallback;
    }

    return fallback.slice(0, META_DESCRIPTION_MAX_LENGTH).trim();
}

function resolveInitialTitle(editorFieldMapping, configuration) {
    const titleOverride = getFieldValue(editorFieldMapping.titleOverride).trim();
    if (titleOverride) {
        return titleOverride;
    }

    const title = getFieldValue(editorFieldMapping.title).trim();
    if (title) {
        return title;
    }

    return (configuration.titleOverride || configuration.title || '').trim();
}

function shouldLinkTitles(titleInput, titleOverrideInput) {
    if (!titleInput || !titleOverrideInput) {
        return false;
    }

    return titleOverrideInput.value.trim() === '' || titleInput.value === titleOverrideInput.value;
}

function getPlainTextValue(field) {
    const rawValue = getFieldValue(field);

    if (!rawValue) {
        return '';
    }

    const parserNode = document.createElement('div');
    parserNode.innerHTML = rawValue;

    return (parserNode.textContent || parserNode.innerText || '').replace(/\s+/g, ' ').trim();
}

function getFieldValue(field) {
    if (!field) {
        return '';
    }

    const editor = window.tinymce ? tinymce.get(field.id) : null;

    if (editor) {
        return editor.getContent() || '';
    }

    return field.value || '';
}

export default React.memo(IntegratedYoastApp);
