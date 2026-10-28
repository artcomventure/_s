import { defineConfig, globalIgnores } from 'eslint/config';
import base, { localizedGlobals, wordpressGlobals } from './eslint.base.mjs';

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
				...wordpressGlobals,
				...localizedGlobals,
				// third party
				google: 'readonly',
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
