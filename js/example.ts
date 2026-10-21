/**
 * Example: TypeScript with utilities globals (`Behaviours`, `Alter`, `BREAKPOINTS`).
 *
 * Built to `example.min.js` – enqueue that file directly (there is no `example.js`).
 */
(function() {

	interface ExampleOptions {
		selector: string;
		className: string;
	}

	// let other scripts alter the options: `Alter.add( 'example:options', options => ( { ...options, className: 'is-active' } ) )`
	const options = Alter.do<ExampleOptions>( 'example:options', {
		selector: '[data-example]',
		className: 'is-example',
	} );

	Behaviours.add( 'example', $context => {
		$context.querySelectorAll<HTMLElement>( options.selector ).forEach( $element => {
			$element.classList.add( options.className );
			$element.classList.toggle( 'is-mobile', BREAKPOINTS.down( 'md' ) );
		} );
	} );

})();
