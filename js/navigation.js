/**
 * File navigation.js.
 *
 * Handles toggling the navigation menu for small screens.
 * Dropdown menus (keyboard/touch) are project-specific, e.g. via `:focus-within`.
 */
(function() {

	let $header, $siteNavigation, $button;

	// remove the .toggled class and set aria-expanded to false
	// when the user clicks outside the navigation
	document.addEventListener( 'click', function( e ) {
		let isAnchor = e.target.closest( 'a' );
		isAnchor = isAnchor && isAnchor.hash && isAnchor.pathname === window.location.pathname;

		if ( $button && $button.getAttribute( 'aria-expanded' ) === 'true'
			&& (!$siteNavigation.contains( e.target ) || isAnchor) ) {
			$button.dispatchEvent( new Event( 'click' ) );
		}
	} );

	// close the menu on escape and return focus to the toggle button
	document.addEventListener( 'keydown', function( e ) {
		if ( e.key !== 'Escape' || !$button || $button.getAttribute( 'aria-expanded' ) !== 'true' ) return;

		$button.dispatchEvent( new Event( 'click' ) );
		$button.focus();
	} );

	Behaviours.add( 'navigation', $context => {

		if ( $context !== document && $context.id !== 'masthead' ) return;

		$siteNavigation = $context.querySelector( '#site-navigation' );
		if ( ! $siteNavigation ) return $button = undefined;

		$header = $siteNavigation.closest( '#masthead' );

		// toggle button
		$button = $siteNavigation.getElementsByTagName( 'button' )[ 0 ];
		if ( 'undefined' === typeof $button ) return;

		const $menu = $siteNavigation.getElementsByTagName( 'ul' )[ 0 ];
		if ( 'undefined' === typeof $menu ) {
			$button.style.display = 'none';
			return;
		}

		if ( ! $menu.classList.contains( 'nav-menu' ) ) {
			$menu.classList.add( 'nav-menu' );
		}

		// toggle the .toggled class and the aria-expanded value each time the button is clicked
		$button.addEventListener( 'click', e => {
			const expand = e.isTrusted && $button.getAttribute( 'aria-expanded' ) === 'false';
			$button.setAttribute( 'aria-expanded', expand ? 'true' : 'false' );
			$siteNavigation.classList[expand ? 'add' : 'remove']( 'toggled' );
			document.documentElement.style.overflow = expand ? 'hidden' : '';
		} );

		// menu links in button blocks get the button link class
		for ( const $link of $menu.getElementsByTagName( 'a' ) ) {
			if ( $link.closest( '.wp-block-button' ) )
				$link.classList.add( 'wp-block-button__link' );
		}
	} );

})()
