/**
 * Shared ESLint base of theme and utilities: `defineConfig( [ base, …own blocks ] )`.
 */

import { defineConfig, globalIgnores } from 'eslint/config';
import js from '@eslint/js';
import globals from 'globals';

export default defineConfig( [
	globalIgnores( [ '**/*.min.js', '**/node_modules/' ] ),
	{
		files: [ '**/*.{js,ts}' ],
		extends: [ js.configs.recommended ],
		languageOptions: {
			sourceType: 'script',
			globals: {
				...globals.browser,
				// utilities/js
				Alter: 'readonly',
				Behaviours: 'readonly',
				BREAKPOINTS: 'readonly',
				// WordPress
				wp: 'readonly',
			},
		},
		// unused params document callback signatures; silent catches are intended
		rules: {
			'no-unused-vars': [ 'error', { args: 'none', caughtErrors: 'none' } ],
			'no-empty': [ 'error', { allowEmptyCatch: true } ],
		},
	},
	{
		files: [ '**/*.mjs' ],
		extends: [ js.configs.recommended ],
		languageOptions: { globals: globals.node },
	},
	{
		// Playwright: callbacks of `page.evaluate()` run in the browser
		files: [ '**/tests/**/*.mjs' ],
		languageOptions: { globals: { ...globals.node, ...globals.browser } },
	},
] );
