import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                // Palet "Local Trust & Energetic" — E-Wedu
                brand: {
                    primary: '#1E3A8A', // biru tua  — kepercayaan, institusi
                    accent: '#F97316', // oranye    — energi, ajakan aksi
                    background: '#F8FAFC', // latar terang netral
                    text: '#0F172A', // teks utama kontras tinggi
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
