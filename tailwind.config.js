/** @type {import('tailwindcss').Config} */
export default {
   darkMode: 'class',   // ← tambahkan
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './app/**/*.php',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
};
