<?php

define( 'GUTENBERG_DIRECTORY', dirname( __FILE__ ) );
define( 'GUTENBERG_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/gutenberg' );

add_action( 'after_setup_theme', function() {
	add_theme_support( 'editor-styles' );
	// relative path from `functions.php`
	add_editor_style(  './utilities/inc/gutenberg/editor-style.css' );
}, 11 );

// add `?ver=FILEMTIME` to editor-style.css
add_filter( 'tiny_mce_before_init', function( $mce_init ) {
	$mce_init['cache_suffix'] = 'ver=' . filemtime(GUTENBERG_DIRECTORY . '/editor-style.css');
	return $mce_init;
} );

add_action( 'after_setup_theme', function() {
    add_theme_support( 'align-wide' );

	// more settings see `THEME/theme.json`
	// https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-json/
} );

// block editor
add_action( 'init', function() {
	// automatically load dependencies and version
	$asset_file = include( GUTENBERG_DIRECTORY . '/build/index.asset.php' );

	wp_register_script(
		'gutenberg-be-js',
		GUTENBERG_DIRECTORY_URI . '/build/index.js',
		$asset_file['dependencies'],
		$asset_file['version']
	);

	// enqueue
	register_block_type( 'gutenberg/stuff', array(
		'editor_script' => 'gutenberg-be-js',
		array(
			'attributes' => array(
				'background-color' => array(
					'type' => 'string',
					'default' => get_background_color(),
				)
			)
		)
	) );
} );

// remove `wp-container-{id}` from all blocks
// original @see `wp-includes/block-supports/layout.php:wp_render_layout_support_flag()`
remove_filter( 'render_block', 'wp_render_layout_support_flag', 10, 2 );
add_filter( 'render_block', function( $block_content, $block ) {
	$block_type     = WP_Block_Type_Registry::get_instance()->get_registered( $block['blockName'] );
	$support_layout = block_has_support( $block_type, array( '__experimentalLayout' ), false );

	if ( ! $support_layout ) {
		return $block_content;
	}

	$block_gap             = wp_get_global_settings( array( 'spacing', 'blockGap' ) );
	$default_layout        = wp_get_global_settings( array( 'layout' ) );
	$has_block_gap_support = isset( $block_gap ) ? null !== $block_gap : false;
	$default_block_layout  = _wp_array_get( $block_type->supports, array( '__experimentalLayout', 'default' ), array() );
	$used_layout           = isset( $block['attrs']['layout'] ) ? $block['attrs']['layout'] : $default_block_layout;
	if ( isset( $used_layout['inherit'] ) && $used_layout['inherit'] ) {
		if ( ! $default_layout ) {
			return $block_content;
		}
		$used_layout = $default_layout;
	}

	$class_names     = array();
	$container_class = wp_unique_id( 'wp-container-' );
//	$class_names[]   = $container_class;

	// The following section was added to reintroduce a small set of layout classnames that were
	// removed in the 5.9 release (https://github.com/WordPress/gutenberg/issues/38719). It is
	// not intended to provide an extended set of classes to match all block layout attributes
	// here.
	if ( ! empty( $block['attrs']['layout']['orientation'] ) ) {
		$class_names[] = 'is-' . sanitize_title( $block['attrs']['layout']['orientation'] );
	}

	if ( ! empty( $block['attrs']['layout']['justifyContent'] ) ) {
		$class_names[] = 'is-content-justification-' . sanitize_title( $block['attrs']['layout']['justifyContent'] );
	}

	if ( ! empty( $block['attrs']['layout']['flexWrap'] ) && 'nowrap' === $block['attrs']['layout']['flexWrap'] ) {
		$class_names[] = 'is-nowrap';
	}

	$gap_value = _wp_array_get( $block, array( 'attrs', 'style', 'spacing', 'blockGap' ) );
	// Skip if gap value contains unsupported characters.
	// Regex for CSS value borrowed from `safecss_filter_attr`, and used here
	// because we only want to match against the value, not the CSS attribute.
	if ( is_array( $gap_value ) ) {
		foreach ( $gap_value as $key => $value ) {
			$gap_value[ $key ] = $value && preg_match( '%[\\\(&=}]|/\*%', $value ) ? null : $value;
		}
	} else {
		$gap_value = $gap_value && preg_match( '%[\\\(&=}]|/\*%', $gap_value ) ? null : $gap_value;
	}

	$fallback_gap_value = _wp_array_get( $block_type->supports, array( 'spacing', 'blockGap', '__experimentalDefault' ), '0.5em' );

	// If a block's block.json skips serialization for spacing or spacing.blockGap,
	// don't apply the user-defined value to the styles.
	$should_skip_gap_serialization = wp_should_skip_block_supports_serialization( $block_type, 'spacing', 'blockGap' );
	$style                         = wp_get_layout_style( ".$container_class", $used_layout, $has_block_gap_support, $gap_value, $should_skip_gap_serialization, $fallback_gap_value );
	// This assumes the hook only applies to blocks with a single wrapper.
	// I think this is a reasonable limitation for that particular hook.
	$content = preg_replace(
		'/' . preg_quote( 'class="', '/' ) . '/',
		'class="' . esc_attr( implode( ' ', $class_names ) ) . ' ',
		$block_content,
		1
	);

//	wp_enqueue_block_support_styles( $style );

	return $content;
}, 10, 2 );

add_action( 'rest_api_init', function() {
	register_rest_route('gutenberg/v1', '/getBackgroundColor', [
		'method' => 'GET',
		'permission_callback' => function () {
			return current_user_can('edit_posts' );
		},
		'callback' => function() {
			return rest_ensure_response( get_background_color() );
		}
	] );
} );

// auto include /inc files
auto_include_files( dirname( __FILE__ ) . '/inc' );
