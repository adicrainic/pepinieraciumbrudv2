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
        primary: {
          DEFAULT: '#2D6A4F',
          light: '#40916C',
          dark: '#1B4332',
          50: '#E8F5E9',
          100: '#C8E6C9',
          500: '#2D6A4F',
          700: '#1B4332',
        },
        accent: {
          DEFAULT: '#74C69D',
          light: '#95D5B2',
          warm: '#D4A373',
          'warm-dark': '#BC6C25',
        },
        surface: {
          DEFAULT: '#FFFFFF',
          warm: '#F5F0E8',
          card: '#FCFCF9',
        },
        border: {
          DEFAULT: '#E8E0D5',
          light: '#F0EBE1',
        },
        background: '#FAFAF8',
        text: {
          DEFAULT: '#1A1A1A',
          muted: '#6B6B6B',
          light: '#9B9B9B',
        },
      },
      fontFamily: {
        serif: ['"Playfair Display"', 'Georgia', 'serif'],
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      },
    },
  },
  plugins: [],
} satisfies Config

