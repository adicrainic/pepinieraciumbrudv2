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
        // Ink black – primary brand (logo "P" and reversed-S strokes)
        primary: {
          DEFAULT: '#2E2C2A',
          light: '#4A4744',
          dark: '#22201E',
          50: '#F4EFE5',
          100: '#EAE4D8',
          200: '#D6CFC1',
          300: '#ABA498',
          400: '#78726A',
          500: '#2E2C2A',
          600: '#2A2826',
          700: '#262422',
          800: '#22201E',
          900: '#1C1A18',
        },
        // Rose red – accents / highlights / sale
        accent: {
          DEFAULT: '#C8102E',
          light: '#FBE9EC',
          warm: '#C8102E',
          'warm-dark': '#9E0C24',
        },
        rose: {
          50: '#FDF2F4',
          100: '#FBE3E7',
          200: '#F3BAC3',
          500: '#C8102E',
          600: '#A80D26',
          700: '#7F091C',
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
        lg: '2px',
        xl: '3px',
        '2xl': '4px',
        '3xl': '6px',
      },
      boxShadow: {
        soft: '0 10px 30px -14px rgba(46, 44, 42, 0.14)',
        petal: '0 20px 45px -20px rgba(200, 16, 46, 0.28)',
      },
      fontFamily: {
        serif: ['"Cormorant Garamond"', 'Georgia', 'serif'],
        sans: ['Jost', 'system-ui', '-apple-system', 'sans-serif'],
      },
    },
  },
  plugins: [],
} satisfies Config

