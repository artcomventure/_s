<?php

/**
 * Checks if user has any of the given $roles.
 * _We can ignore an `AND` relation between 2 or more roles, because user only can have one role._
 *
 * @param string|array $roles
 * @param null|int|WP_User $user
 *
 * @return bool
 */
function user_has_role( $roles, $user = null ) {
	if ( !is_array($roles) ) $roles = array( $roles );

	if ( ! $user ) $user = wp_get_current_user();
	elseif ( is_numeric( $user ) ) $user = get_userdata( $user );

	if ( !empty($user->roles) && array_intersect( $roles, (array) $user->roles ) ) return true;

	return false;
}
