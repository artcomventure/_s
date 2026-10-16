<?php

function get_brevo_props( $contact_form ) {
	return wp_parse_args( $contact_form->prop( 'brevo' ), [
		'form_url' => ''
	] );
}

/**
 * Validate Brevo form URL: https and Brevo host only (prevents SSRF).
 *
 * @return string Valid URL or empty string.
 */
function sanitize_brevo_form_url( $url ): string {
	$url = esc_url_raw( trim( (string) $url ), ['https'] );
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );

	foreach ( apply_filters( 'cf7_brevo_allowed_hosts', ['sibforms.com', 'brevo.com'] ) as $allowed ) {
		if ( $host === $allowed || str_ends_with( $host, ".$allowed" ) ) return $url;
	}

	return '';
}

/**
 * Registers the Brevo contact form property.
 */
add_filter( 'wpcf7_pre_construct_contact_form_properties', function( $properties, $contact_form ) {
	$properties += ['brevo' => []];
	return $properties;
}, 10, 2 );

/**
 * Builds the editor panel for the Brevo property.
 */
add_filter( 'wpcf7_editor_panels', function( $panels ) {
	$contact_form = WPCF7_ContactForm::get_current();

	$prop = get_brevo_props( $contact_form );

	$panels += [
		'brevo-panel' => [
			'title' => __( 'Brevo', 'cf7' ),
			'callback' => function() use ( $prop ) { ?>
				<h2><?php esc_html_e( 'Connect your contact form to a Brevo newsletter list', 'cf7' ) ?></h2>

				<fieldset>
					<legend></legend>
					<p class="description">
						<label for="wpcf7-brevo-form_url">
							<?php esc_html_e( 'URL to Brevo form', 'cf7' ); ?>
							<input type="text" id="wpcf7-brevo-form_url" name="wpcf7-brevo[form_url]" value="<?php echo esc_attr( $prop['form_url'] ) ?>"
							       class="large-text" />
							<span><?php printf( esc_html__( 'For an OPT IN use the an acceptance tag with %s attribute, e.g. %s', 'cf7' ), '<code>consent_for:brevo</code>', '<code>[acceptance newsletter optional consent_for:brevo]</code>' ); ?></span>
						</label>
					</p>
				</fieldset>
			<?php }
		]
	];

	return $panels;
}, 10, 1 );

/**
 * Saves the Brevo property value.
 */
add_action( 'wpcf7_save_contact_form', function( $contact_form, $args, $context ) {
	// `wpcf7_superglobal_post()` requires CF7 6.0+
	$prop = (array) wp_unslash( $_POST['wpcf7-brevo'] ?? [] );

	$contact_form->set_properties( ['brevo' => [
		'form_url' => sanitize_brevo_form_url( $prop['form_url'] ?? '' ),
	]] );
}, 10, 3 );

add_action( 'wpcf7_submit', function( $contact_form, $result ) {
	if ( $contact_form->in_demo_mode() ) return;
	if ( !($result['posted_data_hash'] ?? '') ) return;
	if ( !in_array( $result['status'] ?? '', ['mail_sent', 'mail_failed'] ) ) return;

	// validate again: values saved before validation existed
	if ( !$form_url = sanitize_brevo_form_url( get_brevo_props( $contact_form )['form_url'] ) ) return;

	$submission = WPCF7_Submission::get_instance();

	foreach ( wpcf7_scan_form_tags( ['type' => 'acceptance'] ) as $tag ) {
		if ( $tag->has_option( 'consent_for:brevo' ) && null == $submission->get_posted_data( $tag->name ) ) {
			return; // no consent
		}
	}

	$params = wpcf7_sendinblue_collect_parameters();
	$params['OPT_IN'] = 1;

	// `wp_safe_remote_post` rejects local/private IPs and limits redirects
	$response = wp_safe_remote_post( $form_url, [
		'timeout' => 5,
		'body' => $params,
	] );

	if ( is_wp_error( $response ) ) {
		error_log( 'Brevo: ' . $response->get_error_message() );
	}
}, 10, 2 );
