<?php

// add custom (editable) message for missing "@" in email
add_filter( 'wpcf7_messages', function( $messages ) {
	if ( !function_exists( 'defaultMessage' ) ) {
		function defaultMessage( string $errmsg = '' ): string {
			return sprintf( __( 'The email address is invalid: %s', 'cf7' ), $errmsg );
		}
	}

	$messages['invalid_email--@'] = [
		'description' => __( 'The email address entered by the sender is missing an @ sign', 'cf7' ),
		'default' => defaultMessage( __( 'the @ sign is missing.', 'cf7' ) ),
	];

	$messages['invalid_email--@@'] = [
		'description' => __( 'The email address entered by the sender contains multiple @ signs', 'cf7' ),
		'default' => defaultMessage( __( 'multiple @ signs are used.', 'cf7' ) ),
	];

	$messages['invalid_email--username'] = [
		'description' => __( 'The email address entered by the sender is missing an username', 'cf7' ),
		'default' => defaultMessage(  __( 'the username is missing.', 'cf7' ) ),
	];

	$messages['invalid_email--domain'] = [
		'description' => __( 'The email address entered by the sender is missing the domain', 'cf7' ),
		'default' => defaultMessage( __( 'the domain is missing.', 'cf7' ) ),
	];

	$messages['invalid_email--tld'] = [
		'description' => __( 'The email address entered by the sender is missing the top-level domain', 'cf7' ),
		'default' => defaultMessage( __( 'the top-level domain is missing.', 'cf7' ) ),
	];

	$messages['invalid_email--whitespace'] = [
		'description' => __( 'The email address entered by the sender contains whitespace characters', 'cf7' ),
		'default' => defaultMessage( __( 'whitespace characters are used.', 'cf7' ) ),
	];

	return $messages;
} );

// Schema-Woven Validation
// @see https://contactform7.com/schema-woven-validation/
add_action( 'wpcf7_swv_create_schema', function( $schema, $contact_form ) {
	foreach ( $contact_form->scan_form_tags( array(
		'basetype' => array( 'email' ),
	) ) as $tag ) {
		if ( 'email' !== $tag->basetype ) continue; // not an email field
		if ( empty($email = $_POST[$tag->name] ?? null) ) continue; // no input

		// expected format: USERNAME@DOMAIN.TLD

		// whitespace characters are used
		if ( preg_match( '/\s/', $email ) ) $schema->add_rule(
			wpcf7_swv_create_rule( 'email', array(
				'field' => $tag->name,
				'error' => wpcf7_get_message( 'invalid_email--whitespace' ),
			) )
		);
		// "@" is missing
		elseif ( !str_contains( $email, '@' ) ) $schema->add_rule(
			wpcf7_swv_create_rule( 'email', array(
				'field' => $tag->name,
				'error' => wpcf7_get_message( 'invalid_email--@' ),
			) )
		);
		// multiple "@" are used
		elseif ( substr_count( $email, '@'  ) > 1 ) $schema->add_rule(
			wpcf7_swv_create_rule( 'email', array(
				'field' => $tag->name,
				'error' => wpcf7_get_message( 'invalid_email--@@' ),
			) )
		);
		// username is missing
		elseif ( str_starts_with( $_POST[$tag->name], '@' ) ) $schema->add_rule(
			wpcf7_swv_create_rule( 'email', array(
				'field' => $tag->name,
				'error' => wpcf7_get_message( 'invalid_email--username' ),
			) )
		);
		elseif ( str_ends_with( $_POST[$tag->name], '@' ) || str_contains( $_POST[$tag->name], '@.' ) ) {
			$schema->add_rule(
				wpcf7_swv_create_rule( 'email', array(
					'field' => $tag->name,
					'error' => wpcf7_get_message( 'invalid_email--domain' ),
				) )
			);
		}
		elseif ( preg_match( '/@[^.]+\.?$/', $email ) ) $schema->add_rule(
			wpcf7_swv_create_rule( 'email', array(
				'field' => $tag->name,
				'error' => wpcf7_get_message( 'invalid_email--tld' ),
			) )
		);
	}
}, 9, 2 );

// WP7 uses Schema-Woven Validation (see above)
// This is the _other_ way.
//add_filter( 'wpcf7_validate', function( $result, $tags ) {
//	foreach ( $tags as $tag ) if ( $tag->basetype === 'email' && $value = $_POST[$tag->name] ) {
//		if ( !str_contains( $value, '@' ) ) {
//			$result->invalidate( $tag, wpcf7_get_message( 'invalid_email--@' ) );
//		}
//		else { /* ... */ }
//	}
//
//	return $result;
//}, 9, 2 );
