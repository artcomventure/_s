<?php

function get_brevo_props( $contact_form ) {
	return wp_parse_args( $contact_form->prop( 'brevo' ), [
		'form_url' => ''
	] );
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
			'title' => __( 'Brevo', 'contact-form-7' ),
			'callback' => function() use ( $prop ) { ?>
				<h2><?php esc_html_e( __( 'Connect your contact form to a Brevo newsletter list', 'cf7' ) ) ?></h2>

				<fieldset>
					<legend></legend>
					<p class="description">
						<label for="wpcf7-brevo-form_url">
							<?php esc_html_e( 'URL to Brevo form', 'cf7' ); ?>
							<input type="text" id="wpcf7-brevo-form_url" name="wpcf7-brevo[form_url]" value="<?php esc_attr_e( $prop['form_url'] ) ?>"
							       class="large-text" />
							<span><?php printf( esc_html( 'For an OPT IN use the an acceptance tag with %s attribute, e.g. %s', 'cf7' ), '<code>consent_for:brevo</code>', '<code>[acceptance newsletter optional consent_for:brevo]</code>' ); ?></span>
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
	$prop = wp_parse_args(
		(array) wpcf7_superglobal_post( 'wpcf7-brevo', [] ),
		['form_url' => '']
	);

	$contact_form->set_properties( ['brevo' => $prop,] );
}, 10, 3 );

add_action( 'wpcf7_submit', function( $contact_form, $result ) {
	if ( $contact_form->in_demo_mode() ) return;
	if ( !($result['posted_data_hash'] ?? '') ) return;
	if ( !in_array( $result['status'] ?? '', ['mail_sent', 'mail_failed'] ) ) return;

	$prop = get_brevo_props( $contact_form );
	if ( !$prop['form_url'] ) return;

	$submission = WPCF7_Submission::get_instance();

	foreach ( wpcf7_scan_form_tags( ['type' => 'acceptance'] ) as $tag ) {
		if ( $tag->has_option( 'consent_for:brevo' ) && null == $submission->get_posted_data( $tag->name ) ) {
			return; // no consent
		}
	}

	$params = wpcf7_sendinblue_collect_parameters();
	$params['OPT_IN'] = 1;

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $prop['form_url'] );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $params );
	curl_exec( $ch );
	curl_close( $ch );
}, 10, 2 );
