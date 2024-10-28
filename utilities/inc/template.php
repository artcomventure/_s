<?php

/**
 * Retrieves an array of the class names for the html element.
 *
 * @param string|string[] $css_class Optional. Space-separated string or array of class names
 *                                   to add to the class list. Default empty.
 * @return string[] Array of class names.
 */
function get_html_class( $css_class = '' ) {
	$classes = [];

	if ( ! empty( $css_class ) ) {
		if ( ! is_array( $css_class ) ) {
			$css_class = preg_split( '#\s+#', $css_class );
		}
		$classes = array_merge( $classes, $css_class );
	} else {
		// Ensure that we always coerce class to being an array.
		$css_class = [];
	}

	$classes = array_map( 'esc_attr', $classes );

	/**
	 * Filters the list of CSS html class names for the current post or page.
	 *
	 * @param string[] $classes   An array of body class names.
	 * @param string[] $css_class An array of additional class names added to the body.
	 */
	$classes = apply_filters( 'html_class', $classes, $css_class );

	return array_unique( array_filter( $classes ) );
}

/**
 * Displays the class names for the html element.
 *
 * @param string|string[] $css_class Optional. Space-separated string or array of class names
 *                                   to add to the class list. Default empty.
 */
function html_class( $css_class = '' ) {

	// Separates class names with a single space, collates class names for html element.
	echo 'class="' . esc_attr( implode( ' ', get_html_class( $css_class ) ) ) . '"';
}

/**
 * Retrieves an array of the class names for the html element.
 *
 * @param string|string[] $css_class Optional. Space-separated string or array of class names
 *                                   to add to the class list. Default empty.
 *
 * @return string[] Array of class names.
 */
function get_header_class( $css_class = '', $post = null ) {
	$post = get_post( $post );

	$classes = [];

	if ( ! empty( $css_class ) ) {
		if ( ! is_array( $css_class ) ) {
			$css_class = preg_split( '#\s+#', $css_class );
		}
		$classes = array_merge( $classes, $css_class );
	} else {
		// Ensure that we always coerce class to being an array.
		$css_class = [];
	}

	$classes = array_map( 'esc_attr', $classes );

	/**
	 * Filters the list of CSS html class names for the current post or page.
	 *
	 * @param string[] $classes An array of body class names.
	 * @param string[] $css_class An array of additional class names added to the body.
	 */
	$classes = apply_filters( 'header_class', $classes, $css_class, $post->ID );

	return array_unique( array_filter( $classes ) );
}

/**
 * Displays the class names for the html element.
 *
 * @param string|string[] $css_class Optional. Space-separated string or array of class names
 *                                   to add to the class list. Default empty.
 */
function header_class( $css_class = '' ) {

	// Separates class names with a single space, collates class names for html element.
	echo 'class="' . esc_attr( implode( ' ', get_header_class( $css_class ) ) ) . '"';
}

