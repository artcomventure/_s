export default {
	extends: [ 'stylelint-config-recommended-scss' ],
	ignoreFiles: [
		'**/node_modules/**',
		'**/build/**',
		// vendor
		'**/module/**',
	],
	rules: {
		// Sass `@import` (until `@use`), not CSS `@import`
		'no-invalid-position-at-import-rule': null,
		// icon fonts have no generic fallback
		'font-family-no-missing-generic-family-keyword': [ true, { ignoreFontFamilies: [ 'dashicons', 'Icont' ] } ],
		// `//` lines inside commented-out blocks
		'scss/comment-no-empty': null,
	},
};
