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
          DEFAULT: '#B21111',
          light: '#C92A2A',
          dark: '#7D0C0C',
          50: '#FCF2F2',
          100: '#F7DFDF',
        },
        accent: {
          DEFAULT: '#B21111',
          warm: '#B21111',
          'warm-dark': '#7D0C0C'
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

