<?php /**
 * Redirect user on successful form submission.
 *
 * This feature is included in "CF7 Apps" since 3.2.0.
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'contact-form-7-honeypot/honeypot.php' ) ) {
	// check if the feature is enabled
	if ( get_option( 'cf7apps_settings', [] )['cf7-redirection']['is_enabled'] ?? false ) return;
}

// ... otherwise we keep our custom solution:

add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( !str_contains( $hook, 'wpcf7' ) ) return;

	wp_enqueue_script( 'mail-sent-ok-redirect-admin',
		UTLTS_PLUGINS_CONTACTFORM7_DIRECTORY_URI . '/inc/mail-sent-ok-redirect/admin.js',
		array( 'suggest' ), false, true
	);

	wp_set_script_translations( 'mail-sent-ok-redirect-admin', 'cf7', UTLTS_PLUGINS_CONTACTFORM7_DIRECTORY . '/languages/' );

	wp_localize_script( 'mail-sent-ok-redirect-admin', 'mailSentOkRedirect', [
		'suggestUrl' => add_query_arg( [
			'action' => 'cf7-suggest-link',
			'_wpnonce' => wp_create_nonce( 'cf7-suggest-link' ),
			'page' => 1,
		], admin_url( 'admin-ajax.php' ) ),
	] );
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

// redirect URL of the current form, if `mail_sent_ok` message is an URL
function get_wpcf7_mail_sent_ok_redirect(): ?string {
	$form = WPCF7_ContactForm::get_current();
	if ( !$form ) return null;

	return wp_http_validate_url( $redirect = $form->message( 'mail_sent_ok' ) ) ? $redirect : null;
}

// redirect URL on the form itself (no global map, works with pjax)
add_filter( 'wpcf7_form_additional_atts', function( $atts ) {
	if ( $redirect = get_wpcf7_mail_sent_ok_redirect() ) {
		$atts['data-redirect'] = esc_url_raw( $redirect );
	}

	return $atts;
} );

add_filter( 'wpcf7_form_class_attr', function( $class ) {
	return get_wpcf7_mail_sent_ok_redirect() ? "$class redirect-on-mail-sent-ok" : $class;
} );

// always enqueued: with pjax, forms may appear after navigation
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'mail-sent-ok-redirect',
		UTLTS_PLUGINS_CONTACTFORM7_DIRECTORY_URI . '/inc/mail-sent-ok-redirect/app.js',
		[], false, ['in_footer' => true, 'strategy' => 'defer']
	);

	wp_enqueue_style( 'mail-sent-ok-redirect', UTLTS_PLUGINS_CONTACTFORM7_DIRECTORY_URI . '/inc/mail-sent-ok-redirect/style.css' );
} );

// URL suggestions (form editors only)
add_action( 'wp_ajax_cf7-suggest-link', function() {
	check_ajax_referer( 'cf7-suggest-link' );
	if ( !current_user_can( 'wpcf7_edit_contact_forms' ) ) wp_die( -1, 403 );

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

	echo implode( "\n", array_map( 'esc_url', $results ) );

	wp_die();
} );
