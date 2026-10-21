import { defineConfig, globalIgnores } from 'eslint/config';
import js from '@eslint/js';
import globals from 'globals';

// wp-scripts sources (ESM + JSX)
const modules = [
	'**/src/**/*.js',
	'inc/featured-images/block.js',
	'inc/posts-list/inc/block/index.js',
	'js/libs/air-datepicker/app.js',
	'js/libs/dotlottie-wc/app.js',
	'js/libs/dotlottie-wc/inc/block/block.js',
	'js/libs/odometer/format.js',
];

export default defineConfig( [
	globalIgnores( [
		'**/*.min.js',
		'**/node_modules/',
		'**/build/',
		// vendor
		'**/module/',
		'inc/video/videojs/video.js',
		'inc/video/videojs/lang/',
		'js/libs/spa/htmx/',
		'js/polyfills/',
	] ),
	{
		files: [ '**/*.js' ],
		extends: [ js.configs.recommended ],
		languageOptions: {
			sourceType: 'script',
			globals: {
				...globals.browser,
				// utilities/js
				Alter: 'readonly',
				Behaviours: 'readonly',
				BREAKPOINTS: 'readonly',
				utlts: 'readonly',
				uuid: 'readonly',
				// WordPress
				wp: 'readonly',
				jQuery: 'readonly',
				ajaxurl: 'readonly',
				mejs: 'readonly',
				mejsL10n: 'readonly',
				_wpmejsSettings: 'readonly',
				wpcf7: 'readonly',
				// localized data
				customSelectVars: 'readonly',
				gmaps: 'readonly',
				mailSentOkRedirect: 'readonly',
				pjax: 'readonly',
				// third party
				autosize: 'readonly',
				customSelect: 'readonly',
				google: 'readonly',
				Odometer: 'readonly',
				Pjax: 'readonly',
				ScrollTrigger: 'readonly',
				videojs: 'readonly',
			},
		},
		// unused params document callback signatures; silent catches are intended
		rules: {
			'no-unused-vars': [ 'error', { args: 'none', caughtErrors: 'none' } ],
			'no-empty': [ 'error', { allowEmptyCatch: true } ],
		},
	},
	{
		// UMD wrapper
		files: [ 'js/cache.js' ],
		languageOptions: { globals: { define: 'readonly', module: 'readonly' } },
	},
	{
		files: modules,
		languageOptions: {
			sourceType: 'module',
			parserOptions: { ecmaFeatures: { jsx: true } },
		},
	},
	{
		files: [ '**/*.mjs' ],
		extends: [ js.configs.recommended ],
		languageOptions: { globals: globals.node },
	},
] );
