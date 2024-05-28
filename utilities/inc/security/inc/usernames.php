<?php

/**
 * Check if any user has the username `admin`.
 */
add_filter( 'security_checks', function( $checks ) {
	$info = '';

	// check for "admin"
	$admin = get_user_by( 'login', 'admin' );
	if ( ($status = $admin ? (user_has_role( 'administrator', $admin ) ? false : 'warning') : true) !== true ) {
		$info = sprintf(
			__( "There is a user with login name <code><a href='%s'>%s</a></code>%s", 'security' ),
			add_query_arg( 'user_id', $admin->ID, admin_url( 'user-edit.php' ) ),
			$admin->get( 'user_login' ),
			' ' . __( "and also with the role <code>administrator</code>", 'security' )
		) . '<ul><li>' . implode( '</li><li>', [
				__( 'Create new administrator user and delete the <i>old</i> one', 'security' ),
				__( '... or change <code>user_login</code> directly in the database', 'security' )
			] ) . '</li></ul>';
	}

	// check users for identical login name and display name
	if ( $users = array_filter( get_users(), function( $user ) {
		return $user->get( 'user_login' ) === $user->get( 'display_name' );
	} ) ) {
		if ( $status === true ) $status = 'warning';

		$info .= sprintf(
			__( 'There are accounts with identical login name and display name:%s', 'security' ),
			'<ul><li>' . implode( '</li><li>', array_map( function( $user ) {
				return '<a href="' . add_query_arg( 'user_id', $user->ID, admin_url( 'user-edit.php' ) ) . '">' . $user->get( 'display_name' ) . '</a>';
			}, $users ) ) . '</li></ul>'
		);
	}

	$checks['usernames'] = [
		'title' => __( 'Usernames', 'security' ),
		'status' => $status,
		'info' => $info,
		'description' => __( 'Since generic WordPress usernames like <code>admin</code> are easier to guess, they pose a significant risk for your website. Also avoid accounts with identical login name and display name.', 'security' )
	];

	return $checks;
} );
