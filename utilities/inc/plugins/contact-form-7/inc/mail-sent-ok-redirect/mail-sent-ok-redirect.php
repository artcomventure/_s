<?php /**
 * Redirect user on successful form submission.
 *
 * This feature is included in "CF7 Apps" since 3.2.0.
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( !is_plugin_active( 'contact-form-7-honeypot/honeypot.php' ) ) return;

// check if the feature is enabled
if ( get_option( 'cf7apps_settings', [] )['cf7-redirection']['is_enabled'] ?? false ) return;

// ... otherwise we keep our custom solution:

add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( !str_contains( $hook, 'wpcf7' ) ) return;

	wp_enqueue_script( 'mail-sent-ok-redirect-admin',
		CF7_DIRECTORY_URI . '/inc/mail-sent-ok-redirect/admin.js',
		array( 'suggest' ), false, true
	);

	wp_set_script_translations( 'mail-sent-ok-redirect-admin', 'cf7', CF7_DIRECTORY . '/languages/' );
} );

// change i18n json file name to `LOCALE-HASH.json` like created by `wp i18n make-json`
add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	if ( $file && $domain == 'cf7' ) {
		if ( $handle == 'mail-sent-ok-redirect-admin' ) {
			$md5 = md5('inc/mail-sent-ok-redirect/admin.js' );
			$file = preg_replace( '/\/(' . $domain . '-[^-]+)-.+\.json$/', "/$1-{$md5}.json", $file );
		}
	}

	return $file;
}, 10, 3 );

add_action( 'wp_enqueue_scripts', function() {
	foreach (
		get_posts( array(
			'numberposts' => -1,
			'post_type' => 'wpcf7_contact_form'
		) ) as $form
	) if ( wp_http_validate_url( $redirect = WPCF7_ContactForm::get_instance( $form )->message( 'mail_sent_ok' ) ) ) {
		$redirects[ $form->ID ] = $redirect;
	}

	if ( empty( $redirects ) ) return;

	wp_enqueue_script( 'mail-sent-ok-redirect',
		CF7_DIRECTORY_URI . '/inc/mail-sent-ok-redirect/app.js',
		[], false, true
	);

	wp_localize_script( 'mail-sent-ok-redirect', 'wpcf7MailSentOkRedirects', $redirects );

	wp_enqueue_style( 'mail-sent-ok-redirect', CF7_DIRECTORY_URI . '/inc/mail-sent-ok-redirect/style.css' );
} );

// URL suggestions
add_action( 'wp_ajax_suggest-link-ajax', function() {
	$args = array();

	if ( isset( $_GET['q'] ) ) {
		$args['s'] = wp_unslash( $_GET['q'] );
	}

	$args['pagenum'] = ! empty( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;

	if ( ! class_exists( '_WP_Editors', false ) ) {
		require( ABSPATH . WPINC . '/class-wp-editor.php' );
	}

	$results = _WP_Editors::wp_link_query( $args );

	if ( ! isset( $results ) ) {
		wp_die( 0 );
	}

	$results = array_map( function( $result ) {
		return $result['permalink'];
	}, $results );

	echo implode( "\n", $results );

	wp_die();
}, 1 );
