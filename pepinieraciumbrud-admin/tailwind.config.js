/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#111111',
          light: '#3A3A3A',
          dark: '#000000',
          50: '#F6F6F5',
          100: '#EBEBEA',
        },
        accent: {
          DEFAULT: '#C8102E',
          warm: '#C8102E',
          'warm-dark': '#9E0C24'
        },
        sidebar: '#0A0A0A',
        border: '#E5E7EB',
        background: '#F9FAFB',
        surface: '#FFFFFF'
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      }
    },
  },
  plugins: [],
}

