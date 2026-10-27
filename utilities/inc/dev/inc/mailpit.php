<?php

/**
 * Send emails locally.
 * Check here: http://localhost:8025/
 */

if ( !isLocal() ) return;

// send all mails to the Mailpit service
add_action( 'phpmailer_init', function ( $phpmailer ) {
	$phpmailer->Host = 'mailpit';
	$phpmailer->Port = 1025;
	$phpmailer->From = 'wordpress@localhost.dev';
	$phpmailer->FromName = 'localhost';
	$phpmailer->IsSMTP();
} );
