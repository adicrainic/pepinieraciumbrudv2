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
        // Eggshell replaces pure white everywhere (bg-white, text-white, ...)
        white: '#FAF6EE',
        // Rose red – primary brand (rose from the logo, rgb(178 17 17))
        primary: {
          DEFAULT: '#B21111',
          light: '#C92A2A',
          dark: '#7D0C0C',
          50: '#FCF2F2',
          100: '#F7DFDF',
          200: '#F2BDBD',
          300: '#E68585',
          400: '#D13F3F',
          500: '#B21111',
          600: '#970E0E',
          700: '#7D0C0C',
          800: '#620909',
          900: '#470707',
        },
        // Rose red – accents / highlights / sale
        accent: {
          DEFAULT: '#B21111',
          light: '#FCF2F2',
          warm: '#B21111',
          'warm-dark': '#7D0C0C',
        },
        rose: {
          50: '#FCF2F2',
          100: '#F7DFDF',
          200: '#F2BDBD',
          500: '#B21111',
          600: '#970E0E',
          700: '#7D0C0C',
        },
        // Leaf green – freshness, success, small details
        leaf: {
          DEFAULT: '#2F6B3A',
          50: '#F1F7F2',
          100: '#DCEBDF',
          300: '#8DBB97',
          500: '#3E8A4D',
          700: '#2F6B3A',
          900: '#1B3F23',
        },
        surface: {
          DEFAULT: '#FAF6EE',
          warm: '#F1EBDF',
          card: '#FCF9F3',
        },
        border: {
          DEFAULT: '#E2DACB',
          light: '#EDE6D9',
        },
        background: '#FAF6EE',
        text: {
          DEFAULT: '#2E2C2A',
          muted: '#5F5A54',
          light: '#8F8981',
        },
      },
      borderRadius: {
        lg: '6px',
        xl: '10px',
        '2xl': '14px',
        '3xl': '20px',
      },
      boxShadow: {
        soft: '0 10px 30px -14px rgba(178, 17, 17, 0.12)',
        petal: '0 20px 45px -20px rgba(178, 17, 17, 0.28)',
      },
      fontFamily: {
        serif: ['"Cormorant Garamond"', 'Georgia', 'serif'],
        sans: ['Jost', 'system-ui', '-apple-system', 'sans-serif'],
      },
      fontSize: {
        xs: ['0.84375rem', { lineHeight: '1.35rem' }],
        sm: ['0.95rem', { lineHeight: '1.5rem' }],
        base: ['1.0625rem', { lineHeight: '1.65rem' }],
        lg: ['1.1875rem', { lineHeight: '1.75rem' }],
      },
    },
  },
  plugins: [],
} satisfies Config

