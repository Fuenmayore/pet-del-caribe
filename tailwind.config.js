import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // AGREGAMOS LOS COLORES AQUÍ
            colors: {
                pet: {
                    blue: '#318fb5',
                    green: '#8bc53f',
                    dark: '#2a7a9c', // Una variante un poco más oscura para efectos hover
                },
            },
        },
    },

    plugins: [forms],
};