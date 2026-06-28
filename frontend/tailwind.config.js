module.exports = {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {},
  },
  // Safelist common color utilities used in templates to ensure dev CSS contains them
  safelist: [
    'bg-blue-700', 'hover:bg-blue-800',
    'text-blue-200', 'text-blue-300', 'text-blue-400',
    'text-gray-900', 'text-gray-400', 'text-gray-600',
    'border-gray-200', 'focus:ring-2', 'focus:ring-blue-500',
    'disabled:opacity-75'
  ],
  plugins: [],
}
