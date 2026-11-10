import js from '@eslint/js';
import globals from 'globals';

export default [
	{ ignores: ['assets/build/', 'vendor/', 'node_modules/'] },
	js.configs.recommended,
	{
		languageOptions: {
			ecmaVersion: 2023,
			sourceType: 'module',
			globals: { ...globals.browser },
		},
	},
	{
		files: ['tests/**/*.js', '*.config.js', 'scripts/**/*.mjs'],
		languageOptions: { globals: { ...globals.node } },
	},
];
