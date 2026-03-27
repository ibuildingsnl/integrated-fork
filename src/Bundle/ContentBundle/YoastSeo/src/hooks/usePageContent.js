import {useCallback, useRef} from 'react';
import {useRecoilState, useSetRecoilState} from 'recoil';
import debounce from 'lodash.debounce';
import {useConfiguration} from '../provider/ConfigurationProvider';
import parsedPageState from '../state/parsedPageState';
import faviconSrcState from '../state/faviconSrcState';
import titleTemplateState from '../state/titleTemplateState';
import firstPageLoadCompleteState from '../state/firstPageLoadCompleteState';
import pageIsLoadingState from '../state/pageIsLoadingState';
import errorState from '../state/errorState';
import {resolveSeoPlaceholders} from '../helper/seoPlaceholders';

const usePageContent = () => {
    const {configuration, editorFieldMapping} = useConfiguration();
    const [isLoading, setIsLoading] = useRecoilState(pageIsLoadingState);
    const [firstPageLoadComplete, setFirstPageLoadComplete] = useRecoilState(
        firstPageLoadCompleteState);
    const [faviconSrc, setFaviconSrc] = useRecoilState(faviconSrcState);
    const setPageState = useSetRecoilState(parsedPageState);
    const setTitleTemplate = useSetRecoilState(titleTemplateState);
    const setError = useSetRecoilState(errorState);
    const analysisContentRef = useRef(null);

    const loadPageContent = useCallback(async () => {
        if (isLoading) return;

        setIsLoading(true);
        // TODO: add loading indicator

        // Access content directly from the fields
        const title = editorFieldMapping.title ? editorFieldMapping.title.value : configuration.title || '';
        const titleOverride = editorFieldMapping.titleOverride ? editorFieldMapping.titleOverride.value : configuration.titleOverride || '';
        const description = editorFieldMapping.description ? editorFieldMapping.description.value : configuration.description || '';
        const replacementValues = {
            title: title,
            siteTitle: configuration.brandName,
            separator: configuration.titleSeparator,
            slug: editorFieldMapping.slug ? editorFieldMapping.slug.value : configuration.uriPathSegment,
            channel: configuration.channelName || configuration.brandName,
        };


        if (!firstPageLoadComplete) {
            setFaviconSrc(configuration.faviconSrc);
            setFirstPageLoadComplete(true);

            const pageTitle = configuration.titleOverride || configuration.title;
            const brandName = configuration.brandName;
            const fullTitle = pageTitle + ' - ' + brandName;

            if (fullTitle.indexOf(pageTitle) >= 0) {
                setTitleTemplate(fullTitle.replace(pageTitle, '{title}'));
            }
        }

        let bodyContent = '';
        if (editorFieldMapping.content) {
            const editor = window.tinymce ? tinymce.get(editorFieldMapping.content.id) : null;
            bodyContent = editor ? editor.getContent() : (editorFieldMapping.content.value || '');
        } else if (configuration.analysisContentUrl) {
            if (analysisContentRef.current === null) {
                try {
                    const response = await fetch(configuration.analysisContentUrl, {
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error(`Unable to fetch page analysis content: ${response.status}`);
                    }

                    const payload = await response.json();
                    analysisContentRef.current = payload.content || '';
                } catch (error) {
                    console.error(error);
                    setError('Unable to load page content for SEO analysis.');
                    analysisContentRef.current = '';
                }
            }

            bodyContent = analysisContentRef.current || '';
        }

        // Use the title as minimum content source when no rich editor exists, like pages.
        const content = '<h1>' + title + '</h1>' + bodyContent;
        const noDivContent = content.replace(/<div/g, '<p').replace(/<\/div>/g, '</p>');

        setPageState((prev) => ({
            ...prev,
            title: resolveSeoPlaceholders(titleOverride ? titleOverride : title, replacementValues),
            description: resolveSeoPlaceholders(description, replacementValues),
            locale: 'nl_NL',
            content: noDivContent,
            twitterCard: twitterCard(configuration, title, titleOverride, description, replacementValues),
            openGraph: openGraph(configuration, title, titleOverride, description, replacementValues)
        }));


        // TODO: Turn off loading indicator
        setIsLoading(false);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [isLoading, firstPageLoadComplete]);

    const debouncedLoadPageContent = useRef(
        debounce(() => loadPageContent(), 3000)).current;

    return {loadPageContent: debouncedLoadPageContent};
};

function twitterCard(configuration, title, titleOverride, description, replacementValues) {
    return {
        card: null,
        title: resolveSeoPlaceholders(titleOverride ? titleOverride : title, replacementValues),
        site: configuration.baseUrl,
        description: resolveSeoPlaceholders(description, replacementValues),
        creator: configuration.brandName,
        url: configuration.baseUrl + configuration.pageUrl,
        image: configuration.featuredImageSrc,
    };
}

function openGraph(configuration, title, titleOverride, description, replacementValues) {
    return {
        type: null,
        title: resolveSeoPlaceholders(titleOverride ? titleOverride : title, replacementValues),
        site_name: configuration.brandName,
        locale: configuration.uiLocale,
        description: resolveSeoPlaceholders(description, replacementValues),
        url: configuration.baseUrl + configuration.pageUrl,
        image: configuration.featuredImageSrc,
        'image:width': null,
        'image:height': null,
        'image:alt': null,
    };
}


export default usePageContent;
