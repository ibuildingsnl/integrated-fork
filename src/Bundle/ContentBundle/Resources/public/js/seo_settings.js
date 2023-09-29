// const seoChecks = [
//     {
//         name: 'Keyword Analysis',
//         evaluate: (content, keyword) => {
//             const lowerCaseContent = content.toLowerCase();
//             const lowerCaseKeyword = keyword.toLowerCase();
//             const keywordFrequency = (lowerCaseContent.match(new RegExp('\\b' + lowerCaseKeyword + '\\b', 'g')) || []).length;
//             const totalWords = content.split(/\s+/).length;
//             const keywordDensity = (keywordFrequency / totalWords * 100).toFixed(2);
//
//             // Customize the scoring criteria based on your SEO guidelines
//             if (keywordDensity > 2.5 && keywordDensity < 3.5) {
//                 return { score: 'green', message: `Excellent! The keyword density is ${keywordDensity}%.` };
//             } else if (keywordDensity > 1.5) {
//                 return { score: 'orange', message: `Okay. The keyword density is ${keywordDensity}%.` };
//             } else {
//                 return { score: 'red', message: `Poor. The keyword density is only ${keywordDensity}%.` };
//             }
//         }
//     },
// ];
//
// function analyzeSeo(focusKeyword) {
//     const content = tinyMCE.activeEditor.getContent({ format: 'text' }).toLowerCase();
//     const focusKeywordLower = focusKeyword.toLowerCase();
//
//     const results = seoChecks.map(check => ({
//         name: check.name,
//         result: check.evaluate(content, focusKeywordLower)
//     }));
//
//     renderSeoResults(results);
// }
//
// function renderSeoResults(results) {
//     results.forEach(result => {
//         console.log(result.name + ': ' + result.result.message + ' (Score: ' + result.result.score + ')');
//     });
// }

// analyzeSeo(keyword);





document.addEventListener('DOMContentLoaded', (event) => {
    const seoButton = document.querySelector('.seo-button');
    const seoSettings = document.querySelector(
        '.publication-settings-aside.seo-settings');

    if (seoButton && seoSettings) {
        seoButton.addEventListener('click', (e) => {
            e.preventDefault();
            seoSettings.classList.toggle('show');
            document.querySelectorAll('.editor-overlay').forEach(div => {
                div.classList.toggle('show');
            });
        });
    }
});

document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape' || ev.keyCode === 27) {
        document.querySelectorAll('.publication-settings-aside.show').forEach(div => {
            div.classList.remove('show');
        });
        document.querySelectorAll('.editor-overlay.show').forEach(div => {
            div.classList.remove('show');
        });
    }
});
//
// document.addEventListener('click', function (ev) {
//     if (!ev.target.closest('.publication-settings-aside') && !ev.target.closest('.aside-holder') && !ev.target.closest('#toolbar')) {
//         document.querySelectorAll('.publication-settings-aside.show').forEach(div => {
//             div.classList.remove('show');
//         });
//         document.querySelectorAll('.editor-overlay.show').forEach(div => {
//             div.classList.remove('show');
//         });
//     }
// });
