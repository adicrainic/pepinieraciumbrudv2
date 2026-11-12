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
          DEFAULT: '#2E8B57',
          light: '#4CB57A',
          dark: '#1C5B36',
          50: '#F0FDF4',
          100: '#DCFCE7',
        },
        accent: {
          DEFAULT: '#61C98B',
          warm: '#DC7E4A',
          'warm-dark': '#BC6332'
        },
        sidebar: '#1C5B36',
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

