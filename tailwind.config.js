/** @type {import('tailwindcss').Config} */
module.exports = {
    future: {
        removeDeprecatedGapUtilities: true,
        purgeLayersByDefault: true,
    },
    content: [
        './src/**/*.{twig, js, php}',
    ],
    safelist: process.env.NODE_ENV === 'development' ? [{pattern: /.*/}] : ['^sm:w-', '^md:w-', '^lg:w-', '^xl:w-', '^2xl:w-', '^3xl:w-', '^4xl:w-' ],
    theme: {
        extend: {
            colors: {
                'bloem-gray': '#f2f2f2',
                'bloem-darkgray': '#d8d8d8',
                'bloem-grayblue': '#6299ae',
                'bloem-lightblue': '#e0ebef',
                'bloem-lightyellow': '#fff9e4',
                'bloem-blue': 'rgb(0, 97, 255)',
                'bloem-blue-light': 'rgb(232, 242, 255)',
                'bloem-red': 'rgb(226, 24, 58)',
                'bloem-red-light': 'rgb(241, 82, 99)',
                'bloem-blue-dark': 'rgb(2, 51, 86)',
                'bloem-red-white': 'rgb(252, 230, 235)',
                'bloem-boomverzorging': 'rgb(52, 168, 83)',
                'bloem-boomverzorging-alt': 'rgb(233, 246, 237)',
                'bloem-groenbeheer': 'rgb(185, 123, 242)',
                'bloem-groenbeheer-alt': 'rgb(248, 239, 253)',
                'bloem-invasieve-exoten': 'rgb(252, 64, 48)',
                'bloem-invasieve-exoten-alt': 'rgb(252, 236, 233)',
                'bloem-klimaatadaptatie': 'rgb(74, 194, 221)',
                'bloem-klimaatadaptatie-alt': 'rgb(234, 248, 251)',
                'bloem-sortiment': 'rgb(255, 186, 0)',
                'bloem-sortiment-alt': 'rgb(255, 248, 229)',
                'bloem-ziekten-en-plagen': 'rgb(55, 121, 240)',
                'bloem-ziekten-en-plagen-alt': 'rgb(235, 241, 253)',
            },
            screens: {
                '3xl': '1768px',
                '4xl': '1921px',
            },
            spacing: {
                '128': '32rem',
                '144': '36rem',
            },
            height: {
                '128': '32rem',
            },
        },
        filter: { // defaults to {}
            'none': 'none',
            'grayscale': 'grayscale(1)',
            'invert': 'invert(1)',
            'sepia': 'sepia(1)',
        },
        backdropFilter: { // defaults to {}
            'none': 'none',
            'blur': 'blur(20px)',
        },
    },
    variants: {
        extend: {},
        filter: ['responsive'],
        backdropFilter: ['responsive'],
        textColor: ['responsive', 'hover', 'focus', 'visited'],
    },
    plugins: [
        ({addUtilities}) => {
            const utils = {
                '.translate-x-half': {
                    transform: 'translateX(50%)',
                },
            };
            addUtilities(utils, ['responsive']);
        },
    ],
};
