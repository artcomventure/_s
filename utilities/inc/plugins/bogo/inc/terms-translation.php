<?php

/**
 * $_POST data from terms translation table gets _slashed_ by WP @see `wp-includes/load.php:wp_magic_quotes()`
 * ... and once again by `wp-includes/pomo/po.php:poify()`,
 * which results in visible slashes on frontend.
 *
 * There is no filter to interfere, so I strip the slashes before saving strings into the bogo po-file.
 */
add_action( 'admin_menu', function() {
	if ( wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'bogo-edit-text-translation' ) ) {
		// same security logic as in here @see `wp-content/plugins/bogo/admin/admin.php:bogo_load_texts_page`
		if ( check_admin_referer( 'bogo-edit-text-translation' ) ) {
			if ( ($_POST['action'] ?? '') == 'save' ) {
				if ( bogo_is_available_locale( $locale = $_POST['locale'] ?? null ) ) {
					foreach ( (array) bogo_terms_translation( $locale ) as $item ) {
						if ( isset( $_POST[$item['name']] )
						     and current_user_can( $item['cap'] ?? 'bogo_edit_terms_translation' ) ) {
							// _unslash_ term translation
							$_POST[$item['name']] = stripslashes( $_POST[$item['name']] );
						}
					}
				}
			}
		}
	}
} );