import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    safelist: ['btn-reniec', 'btn-sunat', 'btn-sunarp', 'btn-senace', 'btn-mtc', 'chip-general', 'chip-reniec', 'chip-sunat', 'chip-sunarp', 'chip-admin'],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'system-ui', 'ui-sans-serif', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
