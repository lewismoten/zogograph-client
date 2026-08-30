export default {
  plugins: ['@prettier/plugin-php'],
  printWidth: 80,
  tabWidth: 2,
  singleQuote: true,
  useTabs: false,
  overrides: [
    {
      files: '*.php',
      options: {
        tabWidth: 4,
      },
    },
  ],
};
