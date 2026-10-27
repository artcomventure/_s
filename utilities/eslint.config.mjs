import { defineConfig, globalIgnores } from 'eslint/config';
import base from './eslint.base.mjs';

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
	base,
	globalIgnores( [
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
		languageOptions: {
			globals: {
				// utilities/js
				utlts: 'readonly',
				uuid: 'readonly',
				// WordPress
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
] );
