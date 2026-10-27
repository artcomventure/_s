import { defineConfig } from 'eslint/config';
import tseslint from 'typescript-eslint';
import base from './utilities/eslint.base.mjs';

export default defineConfig( [
	base,
	{
		files: [ '**/*.{js,ts}' ],
		languageOptions: {
			globals: {
				// third party
				CCM: 'readonly',
				doSliders: 'readonly',
			},
		},
	},
	{
		files: [ '**/*.ts' ],
		extends: [ tseslint.configs.recommended ],
		rules: { '@typescript-eslint/no-unused-vars': [ 'error', { args: 'none', caughtErrors: 'none' } ] },
	},
] );
