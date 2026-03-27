import React, { createContext, useContext } from 'react';
import * as PropTypes from 'prop-types';

const ConfigurationContext = createContext(null);
const useConfiguration = () => useContext(ConfigurationContext);

const ConfigurationProvider = ({ children, modalContainer, editorFieldMapping, configuration }) => {
    return (
        <ConfigurationContext.Provider value={{ modalContainer, editorFieldMapping, configuration }}>
            {children}
        </ConfigurationContext.Provider>
    );
};

ConfigurationProvider.propTypes = {
    children: PropTypes.element.isRequired,
    modalContainer: PropTypes.object,
    editorFieldMapping: PropTypes.object.isRequired,
    configuration: PropTypes.shape({
        baseUrl: PropTypes.string.isRequired,
        description: PropTypes.string,
        faviconSrc: PropTypes.string.isRequired,
        featuredImageSrc: PropTypes.string.isRequired,
        focusKeyword: PropTypes.string,
        fieldSelectors: PropTypes.object,
        isAmp: PropTypes.bool.isRequired,
        isCornerstone: PropTypes.bool,
        openGraphFallbackImage: PropTypes.string,
        pageUrl: PropTypes.string.isRequired,
        siteUrl: PropTypes.string.isRequired,
        brandName: PropTypes.string.isRequired,
        channelName: PropTypes.string,
        titleSeparator: PropTypes.string,
        title: PropTypes.string.isRequired,
        titleOverride: PropTypes.string,
        seoPlaceholders: PropTypes.array,
        translationsUrl: PropTypes.string.isRequired,
        twitterFallbackImage: PropTypes.string,
        uiLocale: PropTypes.string.isRequired,
        uriPathSegment: PropTypes.string.isRequired,
        workerUrl: PropTypes.string.isRequired,
    }),
};

export { ConfigurationProvider, useConfiguration };
