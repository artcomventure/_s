<?php

define( 'UTILITIES_DIRECTORY', dirname( __FILE__ ) );
define( 'UTILITIES_DIRECTORY_URI', get_template_directory_uri() . '/utilities' );

// t9n
add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'utilities', UTILITIES_DIRECTORY . '/languages' );
} );

// enable shortcodes for titles
add_filter( 'the_title', 'do_shortcode' );
add_filter( 'widget_title', 'do_shortcode' );

add_action( 'wp_head', function() {
	// set css properties
	// `--root-width` ... for CSS' fluid calculation
	// `--scrollbar-width` ... to calculate vw right
	// run it _first_ in `<head>` to immediately set value ?>
    <script>(function() { function setProperties() {
            document.documentElement.style.setProperty( '--root-width', `${document.documentElement.clientWidth}` );
            document.documentElement.style.setProperty( '--scrollbar-width', `${window.innerWidth - document.documentElement.clientWidth}px` );
        } setProperties() || new ResizeObserver( setProperties ).observe( document.documentElement )
        })();</script>
<?php }, -1 );

// make _utilities_ also available in admin area
add_action( 'admin_enqueue_scripts', 'utilities_register_scripts' );
function utilities_register_scripts(): void {
	wp_register_script( 'behaviours', UTILITIES_DIRECTORY_URI . '/js/behaviours.js', ['alter'], '2.0.0', true );
	wp_register_script( 'alter', UTILITIES_DIRECTORY_URI . '/js/alter.js', [], '1.0.0', true );
	wp_register_script( 'cache', UTILITIES_DIRECTORY_URI . '/js/cache.js', [], '1.3.1', true );
	wp_register_script( 'bouncer', UTILITIES_DIRECTORY_URI . '/js/libs/bouncer.min.js', [], '1.4.6', true );

	wp_register_script( 'helpers', UTILITIES_DIRECTORY_URI . '/js/helpers.js', ['behaviours', 'alter'], false, true );
	wp_register_script( 'external-links', UTILITIES_DIRECTORY_URI . '/js/external-links.js', ['behaviours'], false, true );
	wp_register_script( 'inline-svg', UTILITIES_DIRECTORY_URI . '/js/inline-svg.js', ['behaviours'], false, true );
	wp_register_script( 'custom-width', UTILITIES_DIRECTORY_URI . '/js/custom-width.js', ['behaviours'], false, true );
	wp_register_script( 'in-viewport', UTILITIES_DIRECTORY_URI . '/js/in-viewport.js', [], false, true );
	wp_register_script( 'file-drop', UTILITIES_DIRECTORY_URI . '/js/file-drop.js', ['alter', 'wp-i18n'], false, true );
	wp_set_script_translations( 'file-drop', 'utilities', UTILITIES_DIRECTORY . '/languages/' );

	wp_register_style( 'admin-bar-ux', UTILITIES_DIRECTORY_URI . '/css/admin-bar.css' );
	wp_register_script( 'css-breakpoints', UTILITIES_DIRECTORY_URI . '/js/BREAKPOINTS.js', [], false, true );
}

add_action( 'wp_enqueue_scripts', function() {
	utilities_register_scripts();

	wp_enqueue_script( 'helpers' );
	wp_enqueue_script( 'external-links' );
	wp_enqueue_script( 'inline-svg' );
	wp_enqueue_script( 'custom-width' );
	wp_enqueue_script( 'in-viewport' );

	wp_enqueue_style( 'admin-bar-ux' );
	wp_enqueue_script( 'css-breakpoints' );
}, 9 );

add_action( 'admin_enqueue_scripts', function() {
	wp_enqueue_style( 'utilities-admin', UTILITIES_DIRECTORY_URI . '/css/admin.css' );
} );


// change i18n json file name to `LOCALE-HASH.json` like created by `wp i18n make-json`
add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	if ( $file && $domain == 'utilities' ) {
		if ( $handle == 'file-drop' ) {
			$md5 = md5('js/file-drop.js' );
			$file = preg_replace( '/\/' . $domain . '-([^-]+)-.+\.json$/', "/$1-{$md5}.json", $file );
		}
	}

	return $file;
}, 10, 3 );

// auto include utilities files
require_once UTILITIES_DIRECTORY . '/auto-include-files.php';
auto_include_files( UTILITIES_DIRECTORY . '/inc' );
auto_include_files( UTILITIES_DIRECTORY . '/js/libs' );
auto_include_files( UTILITIES_DIRECTORY . '/inc/plugins' );
auto_include_files( UTILITIES_DIRECTORY . '/inc/shortcodes' );
// auto include theme's /inc files
// TODO: theme vs child theme
auto_include_files( get_template_directory() . '/inc' );
