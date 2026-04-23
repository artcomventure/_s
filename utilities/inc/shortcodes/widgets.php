<?php

add_shortcode('widgets', function( $attr ) {
	if ( isset($attr[0]) ) $attr += [ 'id' => $attr[0] ];

	$atts = shortcode_atts( [
		'id' => ''
	], $attr );

	global $wp_registered_sidebars;

	// checking for sidebar
	if ( isset( $wp_registered_sidebars[$atts['id']] ) ) {
		ob_start();
		dynamic_sidebar( $atts['id'] );

		if ( $output = ob_get_clean() ) {
			$output = '<div class="wp-block-widgets widget-area-' . $atts['id'] . '">' . $output . '</div>';
		}
	}
	// check for widget
//	else {
//		global $wp_registered_widgets;
//
//		if ( isset($wp_registered_widgets[$atts['id']]) ) {
//			$params = [
//				array_merge(
//					[
//						'before_widget' => '<div class="widget custom-shortcode-widget">',
//						'after_widget'  => '</div>',
//						'before_title'  => '<h2 class="widget-title">',
//						'after_title'   => '</h2>',
//					],
//					$wp_registered_widgets[$atts['id']]['params'][0] ?? []
//				)
//			];
//
//			ob_start();
//
//			if ( is_callable( $params ) ) {
//				call_user_func_array( $params, $wp_registered_widgets[$atts['id']]['params'] );
//			}
//
//			$output = ob_get_clean();
//		}
//	}

	return $output ?? '';
} );
