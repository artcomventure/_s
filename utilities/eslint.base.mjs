/**
 * Shared ESLint base of theme and utilities: `defineConfig( [ base, …own blocks ] )`.
 * Opt-in globals: `import base, { wordpressGlobals } from …` → `globals: { ...wordpressGlobals }`.
 */

import { defineConfig, globalIgnores } from 'eslint/config';
import js from '@eslint/js';
import globals from 'globals';

// opt-in sets: only defined when the script/module is loaded, so not part of the base
export const wordpressGlobals = {
	jQuery: 'readonly',
	ajaxurl: 'readonly',
	mejs: 'readonly',
	mejsL10n: 'readonly',
	_wpmejsSettings: 'readonly',
	wpcf7: 'readonly',
};

// data localized by utilities modules
export const localizedGlobals = {
	customSelectVars: 'readonly',
	gmaps: 'readonly',
	mailSentOkRedirect: 'readonly',
	pjax: 'readonly',
};

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
				utlts: 'readonly',
				uuid: 'readonly',
				// libs registered by utilities (script dependency still required)
				autosize: 'readonly',
				customSelect: 'readonly',
				gsap: 'readonly',
				// gsap plugins (see utilities/js/libs/gsap/gsap.php)
				CSSRulePlugin: 'readonly',
				CustomEase: 'readonly',
				Draggable: 'readonly',
				EasePack: 'readonly',
				EaselPlugin: 'readonly',
				ExpoScaleEase: 'readonly',
				Flip: 'readonly',
				MotionPathPlugin: 'readonly',
				Observer: 'readonly',
				PixiPlugin: 'readonly',
				RoughEase: 'readonly',
				ScrollToPlugin: 'readonly',
				ScrollTrigger: 'readonly',
				SlowMo: 'readonly',
				TextPlugin: 'readonly',
				// other libs
				Odometer: 'readonly',
				Pjax: 'readonly',
				videojs: 'readonly',
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
