/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.{blade.php,js}',
    './app/**/*.php',
  ],
  theme: {
    extend: {
      colors: {
        // Extend with custom colors if needed
      },
      fontFamily: {
        sans: ['system-ui', 'sans-serif'],
      },
      animation: {
        'spin': 'spin 1s linear infinite',
        'bounce': 'bounce 1s infinite',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
