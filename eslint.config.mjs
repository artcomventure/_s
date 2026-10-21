import { defineConfig, globalIgnores } from 'eslint/config';
import js from '@eslint/js';
import globals from 'globals';
import tseslint from 'typescript-eslint';

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
				// third party
				CCM: 'readonly',
				doSliders: 'readonly',
			},
		},
		// unused params document callback signatures; silent catches are intended
		rules: {
			'no-unused-vars': [ 'error', { args: 'none', caughtErrors: 'none' } ],
			'no-empty': [ 'error', { allowEmptyCatch: true } ],
		},
	},
	{
		files: [ '**/*.ts' ],
		extends: [ tseslint.configs.recommended ],
		rules: { '@typescript-eslint/no-unused-vars': [ 'error', { args: 'none', caughtErrors: 'none' } ] },
	},
	{
		files: [ '**/*.mjs' ],
		extends: [ js.configs.recommended ],
		languageOptions: { globals: globals.node },
	},
] );
