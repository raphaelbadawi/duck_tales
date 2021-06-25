module.exports = {
  purge: {
    enabled: process.env.NODE_ENV === 'production',
    content: ["./templates/**/*.html.twig"],
  },
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {},
  },
  variants: {
    extend: {},
  },
  plugins: [],
};
