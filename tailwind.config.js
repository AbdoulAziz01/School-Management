/** @type {import('tailwindcss').Config} */
const colors = require('tailwindcss/colors');

export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#f59e0b',
          dark: '#d97706',
          light: '#fbbf24',
        },
        secondary: {
          DEFAULT: '#d97706',
          light: '#f59e0b',
        },
        accent: {
          DEFAULT: '#f59e0b',
          dark: '#d97706',
        },
        success: colors.green,
        warning: colors.amber,
        danger: colors.red,
        info: colors.blue,
      },
      fontFamily: {
        sans: ['Figtree', 'sans-serif'],
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}
