import Paper from 'yoastseo/src/values/Paper';
import {ContentAssessor, SeoAssessor} from 'yoastseo';
import scoreToRating from 'yoastseo/src/interpreters/scoreToRating';
import {measureTextWidth} from 'yoastseo/src/helpers';

import Jed from 'jed';

// Create a new Jed instance with your translations
const i18n = new Jed({
    translate: function(str) {
        return str;
    },
    domain: "js-text-analysis",
    locale_data: {
        "js-text-analysis": {
            "": {}
        }
    }
});

let analysis = {
    seo: {
        expanded: false,
        // ...other properties...
    },
    readability: {
        expanded: false,
        // ...other properties...
    },
    // ...other properties...
};

const iconRatingMapping = {
    error: 'circle',
    feedback: 'circle',
    bad: 'frown',
    ok: 'meh',
    good: 'smile',
};

let state = {
    slug: document.getElementById('integrated_content_seoMetadata_metaSlug').value,
    title: document.getElementById('integrated_content_seoMetadata_metaTitle').value,
    description: document.getElementById('integrated_content_seoMetadata_metaDescription').value,
    focusKeyword: document.getElementById('integrated_content_seoMetadata_focusKeyphrase').value,
    pageContent: '<p><strong>Stichting Droomerf is in samenwerking met woningcorporatie BrabantWonen begonnen met het nieuwe woonproject \'De Novice\' in Veghel (Noord-Brabant) voor jongeren tot 35 jaar met een chronische aandoening. Ondertussen wonen al een aantal jongeren in het gloednieuwe gebouw en zijn er nog vijf woningen over. Hier zoekt de stichting bewoners voor.</strong></p>\n' +
        '<p><img class="img-responsive" title="droomerf1" src="/storage/8f79051c567b356da42fec1c6910b62c.jpg" alt="droomerf1" data-integrated-id="8f79051c567b356da42fec1c6910b62c"></p>\n' +
        '<p>In het gebouw zitten 36 appartementen die initieel bedoeld waren voor ouderen met thuiszorg. Omdat bleek dat er geen vraag is vanuit deze doelgroep, stond een groot deel van de woningen sinds de realisatie in 2021 leeg.</p>\n' +
        '<p><strong>Wooneigenschappen</strong></p>\n' +
        '<p>De woningen zijn rolstoeldoorgankelijk en circa 45 vierkante meter groot. Het gebouw heeft een mooi afwerkingsniveau en de woningen zijn zeer energiezuinig. De huur is rond de 650 euro per maand, waardoor de bewoner in aanmerking komt voor huurtoeslag. Dit wordt geregeld door BrabantWonen.</p>\n' +
        '<p><strong>Zelfstandig wonen</strong></p>\n' +
        '<p>Als bewoner ga je hier zelfstandig wonen: in dit geval houdt het in dat je autonoom beslissingen kunt nemen, eventueel in overleg met de zorg. Ambulante hulp of andere vormen van zorg kunnen worden ingeschakeld, zolang jijzelf in staat bent om dit te regelen. Er zijn ook meerdere zorgpartijen in de omgeving die hierbij kunnen ondersteunen.</p>\n' +
        '<p><img class="img-responsive" title="droomerf2" src="/storage/810fcc196d7d3a0e9bd950b6162736f0.jpg" alt="droomerf2" data-integrated-id="810fcc196d7d3a0e9bd950b6162736f0"></p>\n' +
        '<p><strong>Community</strong></p>\n' +
        '<p>Naast een woning biedt stichting Droomerf de bewoners ook de kans om een community te vormen. Dit is het Droomerf-concept: door een fijne community verbetert de kwaliteit van leven van de bewoners en daardoor voelen zij zich beter, met alle positieve gevolgen van dien. Droomerf verwacht dat het voor jongeren een perfecte kans is om de weg naar zelfstandigheid te bewandelen.</p>\n' +
        '<p>Vanuit Stichting Droomerf wordt actief onderzocht of die thesis (dat mensen door een geschikte woning en community zich beter voelen en daardoor minder zorg nodig hebben) klopt, of verbeterd zou moeten worden. Dit doen ze aan de hand van gesprekken met de bewoners.</p>\n' +
        '<p><em>Op de <a href="https://droomerf.com/" target="_blank" rel="noopener">website</a> van Stichting Droomerf kun je je aanmelden voor een woning en meer informatie vinden.</em></p>',
    isAnalyzing: true
};

async function refreshAnalysis() {
    try {
        const { title, description, slug, focusKeyword, pageContent } = state;
        let paper = new Paper(pageContent, {
            keyword: focusKeyword,
            description: description,
            title: title,
            titleWidth: measureTextWidth(title),
            url: slug,
            contentAnalysisActive: true,
            keywordAnalysisActive: true,
            locale: 'NL_nl',
            permalink: '',
        });

        // create instances of the SEOAssessor and ContentAssessor
        const seoAssessor = new SeoAssessor(i18n);
        const contentAssessor = new ContentAssessor(i18n);

        // assess the paper
        seoAssessor.assess(paper);
        contentAssessor.assess(paper);

        // get the results
        const seoResults = seoAssessor.results;
        const readabilityResults = contentAssessor.results;

        state.isAnalyzing = false;

        console.log(seoResults);
        console.log(readabilityResults);

        // const seoScore = seoResults.result.seo[''].score;
        const seoRating = 6;
        // const readabilityScore = readabilityResults.result.readability.score;
        const readabilityRating = 8;

        analysis = {
            title: title,
            description: description,
            pageContent: pageContent,
            isAnalyzing: state.isAnalyzing,
            seo: {
                ...analysis.seo,
                score: seoRating,
                results: parseResults(seoResults),
            },
            readability: {
                ...analysis.readability,
                score: readabilityRating,
                results: parseResults(readabilityResults),
            },
        };

        render(analysis);

    } catch (error) {
        console.error(error, 'An error occurred while analyzing the page');
    }
}

function parseResults(results) {
    return results.reduce((obj, result) => {
        if (result.text) {
            obj[result._identifier] = {
                id: result._identifier,
                rating: scoreToRating(result.score),
                score: result.score,
                text: result.text,
                hasMarks: result._hasMarks,
                marker: result.marks.map((mark) => {
                    mark._properties.marked = addBlankTargets(mark._properties.marked);
                    return mark;
                }),
            };
        }
        return obj;
    }, {});
}

function addBlankTargets(link) {
    return (""+link).replace(/<a\s+href=/gi, '<a target="_blank" href=');
}

function groupResultsByRating(results, filter = []) {

    console.log(results);

    let groupedResults = {
        'bad': [],
        'ok': [],
        'good': [],
        'feedback': [],
    };

    Object.values(results).forEach(result => {
        if (filter.indexOf(result.identifier) === -1 && result.rating in groupedResults) {
            groupedResults[result.rating].push(result);
        }
    });

    return groupedResults;
}

// Call refreshAnalysis when needed
refreshAnalysis();

function createIconButton(iconState) {
    const iconButton = document.createElement('button');
    iconButton.className = 'rightSideBar__toggleBtn';
    const iconElement = document.createElement('i');
    iconElement.className = iconState;
    iconButton.appendChild(iconElement);
    return iconButton;
}

function renderTextElement(heading, text) {
    const li = document.createElement('li');
    const title = document.createElement('strong');
    title.textContent = heading;
    const value = document.createElement('p');
    value.textContent = text || 'Not available';
    li.appendChild(title);
    li.appendChild(value);
    return li;
}

function renderOverallScore(label, rating) {
    const ratingStyle = scoreToRating(rating / 10);
    const iconType = iconRatingMapping[ratingStyle];

    const scoreElement = document.createElement('span');
    scoreElement.className = 'yoastInfoView__score';

    const iconElement = document.createElement('i');
    iconElement.className = `icon ${iconType}`;
    scoreElement.appendChild(iconElement);

    const labelText = document.createTextNode(` ${label}`);
    scoreElement.appendChild(labelText);

    return scoreElement;
}

function renderResults(results, filter = []) {
    // Assuming i18nRegistry and groupResultsByRating are available in this scope
    let groupedResults = groupResultsByRating(results, filter);

    let listItem = document.createElement('li');
    listItem.className = 'yoastInfoView__item';

    let titleDiv = document.createElement('div');
    titleDiv.className = 'yoastInfoView__title';
    titleDiv.innerText = 'Analysis results';
    listItem.appendChild(titleDiv);

    ['bad', 'ok', 'good'].forEach(rating => {
        if (groupedResults[rating].length > 0) {
            let resultGroup = renderResultGroup(
              `${rating} Results`,
                groupedResults[rating]
            );
            listItem.appendChild(resultGroup);
        }
    });
    return listItem;
}


function handleExpandContentClick() {
    analysis.readability.expanded = !analysis.readability.expanded;
    toggleVisibility('readability-results', analysis.readability.expanded);
    render(analysis);  // Assuming options is accessible
}

function handleExpandSeoClick() {
    analysis.seo.expanded = !analysis.seo.expanded;
    toggleVisibility('seo-results', analysis.seo.expanded);
    render(analysis);  // Assuming options is accessible
}

function toggleVisibility(elementId, isVisible) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.display = isVisible ? 'block' : 'none';
    }
}

// Function to create a rating paragraph element
function renderRating(result, id) {
    if (result) {
        const pElement = document.createElement('p');
        pElement.className = 'yoastInfoView__content';
        pElement.title = result.id;  // Note: translation function is not available in this vanilla JS version

        const iElement = document.createElement('i');
        iElement.className = 'yoastInfoView__rating_circle yoastInfoView__rating_' + result.rating;
        pElement.appendChild(iElement);

        const spanElement = document.createElement('span');
        spanElement.innerHTML = result.text;
        pElement.appendChild(spanElement);

        return pElement;
    }
    return null;
}

// Function to create a result group div element
function renderResultGroup(heading, results) {
    console.log(heading);
    console.log(results);
    const resultGroupDiv = document.createElement('div');
    resultGroupDiv.className = 'yoastInfoView__result_group';

    const spanElement = document.createElement('span');
    const iconElement = document.createElement('i');
    iconElement.className = 'caret-down';  // Note: Icon component is not available in this vanilla JS version
    spanElement.appendChild(iconElement);
    spanElement.appendChild(document.createTextNode(` ${heading} (${results.length})`));
    resultGroupDiv.appendChild(spanElement);

    results.forEach((result, index) => {
        const ratingElement = renderRating(result, index);
        if (ratingElement) {
            resultGroupDiv.appendChild(ratingElement);
        }
    });

    return resultGroupDiv;
}

function render(analysis) {

    console.log(analysis);

    const ulElement = document.createElement('ul');
    ulElement.className = 'yoastInfoView';

    const contentResultsIconState = analysis.readability.expanded ? 'chevron-circle-up' : 'chevron-circle-down';
    const seoResultsIconState = analysis.seo.expanded ? 'chevron-circle-up' : 'chevron-circle-down';

    const snippetPreviewButtonLi = document.createElement('li');
    snippetPreviewButtonLi.className = 'yoastInfoView__item';
    // snippetPreviewButtonLi.appendChild(SnippetPreviewButton());
    ulElement.appendChild(snippetPreviewButtonLi);

    if (!analysis.isAnalyzing) {

        document.querySelector('.seo-title-text').innerHTML = analysis.title;
        let parentElement = document.querySelector('.meta-description-details');
        let dateElement = parentElement ? parentElement.querySelector('.date') : null;

        if (parentElement && dateElement) {
            let dateHTML = dateElement.outerHTML;
            parentElement.innerHTML = dateHTML + analysis.description;
        }
    }

    if (!analysis.isAnalyzing) {
        const seoLi = document.createElement('li');
        seoLi.className = 'yoastInfoView__item';
        const seoHeadingDiv = document.createElement('div');
        seoHeadingDiv.className = 'yoastInfoView__heading';
        seoHeadingDiv.onclick = handleExpandSeoClick;
        seoHeadingDiv.appendChild(renderOverallScore('Focus Keyphrase', analysis.seo.score));
        seoHeadingDiv.appendChild(createIconButton(seoResultsIconState));
        seoLi.appendChild(seoHeadingDiv);
        ulElement.appendChild(seoLi);
        if (analysis.seo.expanded) {
            ulElement.appendChild(renderResults(analysis.seo.results));
        }

        const contentLi = document.createElement('li');
        contentLi.className = 'yoastInfoView__item';
        const contentHeadingDiv = document.createElement('div');
        contentHeadingDiv.className = 'yoastInfoView__heading';
        contentHeadingDiv.onclick = handleExpandContentClick;
        contentHeadingDiv.appendChild(renderOverallScore('Readability analysis', analysis.readability.score));
        contentHeadingDiv.appendChild(createIconButton(contentResultsIconState));
        contentLi.appendChild(contentHeadingDiv);
        ulElement.appendChild(contentLi);
        if (analysis.readability.expanded) {
            ulElement.appendChild(renderResults(analysis.readability.results));
        }
    }

    if (analysis.isAnalyzing) {
        const loadingLi = document.createElement('li');
        loadingLi.style.textAlign = 'center';
        const loadingIcon = document.createElement('i');
        loadingIcon.className = 'spinner';
        loadingIcon.setAttribute('spin', 'true');
        loadingLi.appendChild(loadingIcon);
        loadingLi.appendChild(document.createTextNode(' Loading…'));
        ulElement.appendChild(loadingLi);
    }

    const container = document.getElementById('seo-analysis-container');
    container.innerHTML = '';
    container.appendChild(ulElement);
}

