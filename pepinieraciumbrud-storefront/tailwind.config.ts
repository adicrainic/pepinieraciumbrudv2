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
        // Forest green – primary brand / CTAs
        primary: {
          DEFAULT: '#2E6B3A',
          light: '#4C8C57',
          dark: '#143D24',
          50: '#EFF6EF',
          100: '#DCEBDC',
          200: '#B9D7BC',
          500: '#2E6B3A',
          700: '#22522C',
        },
        // Soft rose – accent highlights (roses stay a feature, not the brand)
        accent: {
          DEFAULT: '#F2A7B8',
          light: '#F9D3DC',
          warm: '#D4A373',
          'warm-dark': '#B8844F',
        },
        rose: {
          50: '#FFF1F4',
          100: '#FFE0E7',
          200: '#F9C0CE',
          500: '#D6336C',
          600: '#B9224F',
          700: '#8F1A3D',
        },
        leaf: {
          DEFAULT: '#2E6B3A',
          50: '#EFF6EF',
          100: '#DCEBDC',
          300: '#8FC199',
          500: '#4C8C57',
          700: '#2E6B3A',
          900: '#0F2E1A',
        },
        surface: {
          DEFAULT: '#FFFFFF',
          warm: '#F4F1E8',
          card: '#FDFCF8',
        },
        border: {
          DEFAULT: '#E4E3D6',
          light: '#EEEDE2',
        },
        background: '#FAF9F4',
        text: {
          DEFAULT: '#1D2B21',
          muted: '#627066',
          light: '#98A39B',
        },
      },
      boxShadow: {
        soft: '0 10px 30px -14px rgba(20, 61, 36, 0.22)',
        petal: '0 22px 45px -20px rgba(20, 61, 36, 0.4)',
      },
      fontFamily: {
        serif: ['"Playfair Display"', 'Georgia', 'serif'],
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      },
    },
  },
  plugins: [],
} satisfies Config

