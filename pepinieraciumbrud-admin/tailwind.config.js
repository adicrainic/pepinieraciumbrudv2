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
          DEFAULT: '#2D6A4F',
          light: '#40916C',
          dark: '#1B4332',
          50: '#E8F5E9',
          100: '#C8E6C9',
        },
        accent: {
          DEFAULT: '#74C69D',
          warm: '#D4A373',
          'warm-dark': '#BC6C25'
        },
        sidebar: '#1B4332',
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

