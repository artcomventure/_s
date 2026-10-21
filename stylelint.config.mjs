export default {
	extends: [ 'stylelint-config-recommended-scss' ],
	ignoreFiles: [ '**/node_modules/**', 'utilities/**' ],
	rules: {
		// Sass `@import` (until `@use`), not CSS `@import`
		'no-invalid-position-at-import-rule': null,
		// empty rules are placeholders in this template; Sass drops them
		'block-no-empty': null,
		'font-family-no-missing-generic-family-keyword': [ true, { ignoreFontFamilies: [ 'dashicons', 'Icont' ] } ],
	},
};
