<?php /**
 * Set translations as shortcode.
 *
 * Either manually `[__ de="DEUTSCHER TEXT" en="ENGLISH TEXT"]`
 * or via translation file (domain) `[__ "TEXT" domain=default]`
 */

add_shortcode( 'l10n', 't9n_shortcode' );
add_shortcode( 't9n', 't9n_shortcode' );
add_shortcode( '__', 't9n_shortcode' );
function t9n_shortcode( $attr ) {
	if ( isset($attr[0]) ) {
		$attr['text'] = $attr[0];
		unset( $attr[0] );
	}

	$atts = shortcode_atts( array(
		'text' => '',
		'domain' => 'default'
	), $attr ) + $attr;

	$locale = explode( '_', get_locale() )[0];
	if ( isset( $atts[$locale] ) ) return $atts[$locale];

	return __( $atts['text'], $atts['domain'] );
}
