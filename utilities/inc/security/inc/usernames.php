<?php

/**
 * Check if any user has the username `admin`.
 */
add_filter( 'security_checks', function( $checks ) {
	$admin = get_user_by( 'login', 'admin' );
	$status = $admin ? (user_has_role( 'administrator', $admin ) ? false : 'warning') : true;

	$checks['admin-user-name'] = [
		'title' => __( 'Username', 'security' ),
		'status' => $status,
		'info' => $status !== true
			? sprintf(
				__( "There is an user with login name <code><a href='%s'>%s</a></code>%s", 'security' ),
				add_query_arg( 'user_id', $admin->ID, admin_url( 'user-edit.php' ) ),
				$admin->get( 'user_login' ),
				' ' . __( "and also with the role <code>administrator</code>", 'security' )
			)
			: __( 'There is no account with the username <code>admin</code>.', 'security' ),
		'description' => sprintf(
			__( 'Since generic WordPress usernames like <code>admin</code> are easier to guess, they pose a significant risk for your website.%s', 'security' ),
			$status !== true ? '<ul><li>' . implode( '</li><li>', [
				__( 'Create new administrator user and delete the <i>old</i> one', 'security' ),
				__( '... or change <code>user_login</code> directly in the database', 'security' )
				] ) . '</li></ul>' : ''
		)
	];

	return $checks;
} );
