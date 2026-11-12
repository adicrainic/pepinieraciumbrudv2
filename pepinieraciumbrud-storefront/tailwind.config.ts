import type { Config } from 'tailwindcss'

export default {
  content: [
    './components/**/*.{js,vue,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './plugins/**/*.{js,ts}',
    './app.vue',
    './error.vue',
  ],
  theme: {
    extend: {
      colors: {
        // Fresh botanical garden green – primary brand / CTAs
        primary: {
          DEFAULT: '#2E8B57',
          light: '#4CB57A',
          dark: '#1C5B36',
          50: '#F0FDF4',
          100: '#DCFCE7',
          200: '#BBF7D0',
          300: '#86EFAC',
          400: '#4ADE80',
          500: '#2E8B57',
          600: '#247545',
          700: '#1C5B36',
          800: '#16482B',
          900: '#0F331F',
        },
        // Soft rose & terracotta accents
        accent: {
          DEFAULT: '#F48B9F',
          light: '#FCECEF',
          warm: '#DC7E4A',
          'warm-dark': '#BC6332',
        },
        rose: {
          50: '#FFF1F4',
          100: '#FFE0E7',
          200: '#F9C0CE',
          500: '#E04873',
          600: '#C42E59',
          700: '#9B1A3F',
        },
        leaf: {
          DEFAULT: '#2E8B57',
          50: '#F0FDF4',
          100: '#DCFCE7',
          300: '#86EFAC',
          500: '#4CB57A',
          700: '#1C5B36',
          900: '#0F331F',
        },
        surface: {
          DEFAULT: '#FFFFFF',
          warm: '#F3F8F5',
          card: '#FFFFFF',
        },
        border: {
          DEFAULT: '#E1ECE4',
          light: '#EDF5F0',
        },
        background: '#FAFCFA',
        text: {
          DEFAULT: '#192C20',
          muted: '#526A5A',
          light: '#829C8B',
        },
      },
      boxShadow: {
        soft: '0 10px 30px -14px rgba(28, 91, 54, 0.12)',
        petal: '0 20px 45px -20px rgba(28, 91, 54, 0.22)',
      },
      fontFamily: {
        serif: ['"Playfair Display"', 'Georgia', 'serif'],
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      },
    },
  },
  plugins: [],
} satisfies Config

