<?php

add_filter( 'security_checks', function ( $checks ) {
	$cf7 = is_plugin_active( 'contact-form-7/wp-contact-form-7.php' );

	if ( $cf7 ) {
		$service = WPCF7_RECAPTCHA::get_instance();
		if ( $reCAPTCHA = $service->is_active() ) {
			$status = true;
		}
		else {
			$unsecure_forms = [];

			foreach ( get_posts( [
				'post_type'     => 'wpcf7_contact_form',
				'numberposts'   => -1
			] ) as $post ) {
				$form = wpcf7_contact_form( $post );

				$manager = WPCF7_FormTagsManager::get_instance();
				$form_tags = $manager->scan( $form->get_properties()['form'] );
				if ( !array_filter( (array) $form_tags, function( $form_tag ) {
					return in_array( $form_tag->basetype, ['quiz', 'honeypot', 'cf7ic'] );
				} ) ) $unsecure_forms[] = '<a href="' . add_query_arg( [
						'page' => 'wpcf7',
						'post' => $post->ID,
						'action' => 'edit'
					], admin_url( 'admin.php' ) ) . '">' . $post->post_title . '</a>';
			}

			$status = !count($unsecure_forms);
		}

		$checks['spam'] = [
			'title'       => __( 'SPAM protection', 'security' ),
			'status'      => $status,
			'info'        => $status
				? sprintf(
					__( "We've detected you've installed the CF7 plugin and some sort of SPAM protection%s.", 'security' ),
					$reCAPTCHA ? ' (reCAPTCHA)' : ''
				)
				: sprintf(
					__( "There are CF7 forms without any protection%s", 'security' ),
					': <ul><li>' . implode( '</li><li>', $unsecure_forms ) . '</li></ul>'
				),
			'description' => sprintf(
				__( "It's important to prevent form spam because its impact ranges from annoyance to potential security risks by malicious links. Your reputation, the reputation of your client and the one of <i>your server</i> is at risk.%s", 'security' ),
				(!$status && !is_plugin_active( 'contact-form-7-honeypot/honeypot.php' )
					? ' ' . sprintf(
						__( 'We recommend using the %s plugin.', 'security' ),
						'<a href="https://wordpress.org/plugins/contact-form-7-honeypot/" target="_blank">CF7 Honeypot</a>'
					)
					: '' )
			)
		];
	}

	return $checks;
} );
