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

const usePageContent = () => {
    const {configuration} = useConfiguration();
    const [isLoading, setIsLoading] = useRecoilState(pageIsLoadingState);
    const [firstPageLoadComplete, setFirstPageLoadComplete] = useRecoilState(
        firstPageLoadCompleteState);
    const [faviconSrc, setFaviconSrc] = useRecoilState(faviconSrcState);
    const setPageState = useSetRecoilState(parsedPageState);
    const setTitleTemplate = useSetRecoilState(titleTemplateState);
    const setError = useSetRecoilState(errorState);

    const loadPageContent = useCallback(() => {
        if (isLoading) return;

        setIsLoading(true);

        // Access content directly from the fields
        const title = document.querySelector('#integrated_content_title').value;
        const titleOverride = document.querySelector(
            '#integrated_content_seoMetadata_metaTitle').value;
        const description = document.querySelector(
            '#integrated_content_seoMetadata_metaDescription').value;

        if (!firstPageLoadComplete) {
            setFaviconSrc(configuration.faviconSrc);
            setFirstPageLoadComplete(true);

            const pageTitle = configuration.titleOverride || configuration.title;
            const brandName = configuration.brandName;
            const fullTitle = pageTitle + ' - ' + brandName.replace(" Website", "");

            if (fullTitle.indexOf(pageTitle) >= 0) {
                setTitleTemplate(fullTitle.replace(pageTitle, '{title}'));
            }
        }

        // Access TinyMCE content
        const content = tinymce.get('integrated_content_content').getContent();

        setPageState((prev) => ({
            ...prev,
            title: titleOverride ? titleOverride : title,
            description: description,
            locale: 'nl_NL',
            content: content,
            twitterCard: twitterCard(configuration, title, titleOverride, description),
            openGraph: openGraph(configuration, title, titleOverride, description)
        }));

        setIsLoading(false);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [isLoading, firstPageLoadComplete]);

    const debouncedLoadPageContent = useRef(
        debounce(() => loadPageContent(), 3000)).current;

    return {loadPageContent: debouncedLoadPageContent};
};

function twitterCard(configuration, title, titleOverride, description) {
    return {
        card: null,
        title: titleOverride ? titleOverride : title,
        site: configuration.baseUrl,
        description: description,
        creator: null,
        url: configuration.baseUrl + configuration.pageUrl,
        image: configuration.featuredImageSrc,
    };
}

function openGraph(configuration, title, titleOverride, description) {
    return {
        type: null,
        title: titleOverride ? titleOverride : title,
        site_name: configuration.brandName.replace(" Website", ""),
        locale: configuration.uiLocale,
        description: description,
        url: configuration.baseUrl + configuration.pageUrl,
        image: configuration.featuredImageSrc,
        'image:width': null,
        'image:height': null,
        'image:alt': null,
    };
}

//
//
// /**
//  * Checks the page content for a favicon meta tag and will try to retrieve it.
//  * If it fails it will try to load the favicon from the default src.
//  * If it fails it will instruct the yoast component to use it's default.
//  *
//  * @returns {Promise<void>}
//  */
// const updateFavicon = useCallback(async (faviconMetaTagSrc) => {
//     if (faviconMetaTagSrc) {
//         let response = await fetch(faviconMetaTagSrc);
//
//         if (response.ok) return setFaviconSrc(faviconMetaTagSrc);
//     }
//
//     let response = await fetch(faviconSrc);
//     if (response.ok) setFaviconSrc(faviconSrc);
//     // eslint-disable-next-line react-hooks/exhaustive-deps
// }, []);
//
// /**
//  * Fetch new content from page preview and trigger analysis at the end.
//  */
// const loadPageContent = useCallback(() => {
//     if (isLoading) return;
//
//     setIsLoading(true);
//
//     fetch(configuration.previewUrl)
//         .then((response) => {
//             if (!response || !response.ok) {
//                 throw new Error(
//                     `Failed fetching preview for Yoast SEO analysis: ${response.status} ${response.statusText}`
//                 );
//             }
//             return response.text();
//         })
//         .then((documentContent) => {
//             const pageParser = new PageParser(documentContent, configuration.contentSelector);
//
//             if (!firstPageLoadComplete) {
//                 updateFavicon(pageParser.faviconSrc);
//                 setFirstPageLoadComplete(true);
//
//                 const pageTitle = configuration.titleOverride || configuration.title;
//                 if (pageParser.title.indexOf(pageTitle) >= 0) {
//                     setTitleTemplate(pageParser.title.replace(pageTitle, '{title}'));
//                 }
//             }
//             setPageState((prev) => ({
//                 ...prev,
//                 title: pageParser.title,
//                 description: pageParser.description,
//                 locale: pageParser.locale,
//                 content: pageParser.pageContent,
//                 twitterCard: pageParser.twitterCard,
//                 openGraph: pageParser.openGraph,
//             }));
//         })
//         .catch((error) => {
//             setError(error.message);
//             console.error(error, 'An error occurred while loading the preview');
//         })
//         .finally(() => {
//             setIsLoading(false);
//         });
//     // eslint-disable-next-line react-hooks/exhaustive-deps
// }, [isLoading, firstPageLoadComplete]);
//
// const debouncedLoadPageContent = useRef(debounce(() => loadPageContent(), 3000)).current;
//
// return { loadPageContent: debouncedLoadPageContent };
// };

export default usePageContent;
