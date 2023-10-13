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
        // TODO: add loading indicator

        // Access content directly from the fields
        const title = document.querySelector('#integrated_content_title').value;
        const titleOverride = document.querySelector('#integrated_content_seoMetadata_metaTitle').value;
        const description = document.querySelector('#integrated_content_seoMetadata_metaDescription').value;


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

        // Access TinyMCE content with h1 title
        const content = '<h1>' + title + '</h1>' + tinymce.get('integrated_content_content').getContent();
        const noDivContent = content.replace(/<div/g, '<p').replace(/<\/div>/g, '</p>');

        setPageState((prev) => ({
            ...prev,
            title: titleOverride ? titleOverride : title,
            description: description,
            locale: 'nl_NL',
            content: noDivContent,
            twitterCard: twitterCard(configuration, title, titleOverride, description),
            openGraph: openGraph(configuration, title, titleOverride, description)
        }));


        // TODO: Turn off loading indicator
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
        creator: configuration.brandName,
        url: configuration.baseUrl + configuration.pageUrl,
        image: configuration.featuredImageSrc,
    };
}

function openGraph(configuration, title, titleOverride, description) {
    return {
        type: null,
        title: titleOverride ? titleOverride : title,
        site_name: configuration.brandName,
        locale: configuration.uiLocale,
        description: description,
        url: configuration.baseUrl + configuration.pageUrl,
        image: configuration.featuredImageSrc,
        'image:width': null,
        'image:height': null,
        'image:alt': null,
    };
}


export default usePageContent;
