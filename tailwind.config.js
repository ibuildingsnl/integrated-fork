/** @type {import('tailwindcss').Config} */
module.exports = {
    future: {
        removeDeprecatedGapUtilities: true,
        purgeLayersByDefault: true,
    },
    content: [
        './src/**/*.{twig, js, php}',
    ],
    theme: {
        extend: {
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
