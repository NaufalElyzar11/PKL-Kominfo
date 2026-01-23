import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                "primary": "#137fec",
                "primary-dark": "#0e62b8",
                "background-light": "#f6f7f8",
                "background-dark": "#101922",
                "electric-blue": "#2E5BFF",
                "lime-green": "#86EFAC",
                "soft-orange": "#FFB347",
                "hub-grey": "#F3F4F6",
            },
            fontFamily: {
                "display": ["Public Sans", "Poppins", "sans-serif"],
                "body": ["Public Sans", "Lato", "sans-serif"],
            },
            borderRadius: {
                "DEFAULT": "0.25rem",
                "lg": "0.5rem",
                "xl": "0.75rem",
                "full": "9999px",
                "hub": "2.5rem",
                "card": "1.75rem"
            },
        },
    },
    plugins: [
        forms,
    ],
}
