<?php

/**
 * Prints auth cookies (JSON) of an administrator for `smoke-test.mjs --cookies`.
 *
 * Usage (in the project's `docker/`; script piped in from the boilerplate):
 * `docker-compose exec -T wordpress wp eval-file - --path=/var/www/html --allow-root < ~/Develop/wordpress/wp-content/themes/_s/bin/admin-cookies.php > cookies.json`
 *
 * Valid for 1 hour; creates a session token (visible under "Sessions" in the user's profile).
 *
 * @since 1.21.0
 */

if ( !defined( 'WP_CLI' ) ) exit;

$admins = get_users( ['role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'fields' => 'ID'] );
if ( !$admins ) WP_CLI::error( 'No administrator found.' );

$user_id    = (int) $admins[0];
$expiration = time() + HOUR_IN_SECONDS;
$token      = WP_Session_Tokens::get_instance( $user_id )->create( $expiration );
$secure     = is_ssl() || str_starts_with( get_option( 'siteurl' ), 'https:' );

echo wp_json_encode( [
	['name' => $secure ? SECURE_AUTH_COOKIE : AUTH_COOKIE, 'value' => wp_generate_auth_cookie( $user_id, $expiration, $secure ? 'secure_auth' : 'auth', $token )],
	['name' => LOGGED_IN_COOKIE, 'value' => wp_generate_auth_cookie( $user_id, $expiration, 'logged_in', $token )],
] );
