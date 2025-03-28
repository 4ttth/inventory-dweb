 /** @type {import('tailwindcss').Config} */
 export default {
  content: ["./src/**/*.{html,js,css,php}"],
  theme: {
    extend: {},
  },
  plugins: [],
}

module.exports = {
    theme: {
      extend: {
        transitionProperty: {
          'scale': 'transform',
        }
      }
    }
  }