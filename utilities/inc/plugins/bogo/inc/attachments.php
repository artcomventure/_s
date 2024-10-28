<?php

/**
 * Save localized attachment data (title, description, caption and alt).
 */
add_action( 'attachment_updated', 'localize_attachment', 10, 3 );
add_action( 'add_attachment', 'localize_attachment', 10, 3 );
function localize_attachment( $post_id, $post_after = null, $post_before = null ): void {
	if ( $_POST['post_id'] ?? null ) $locale = bogo_get_post_locale( $_POST['post_id'] );
	else $locale = bogo_get_user_locale() ?? get_locale();

	// get all attachment fields translations
	$translations = get_post_meta( $post_id, '_translations', true ) ?: [];

	// update data for current language
	$translations[$locale] = [
		'title' => $_POST['changes']['title'] ?? $_POST['post_title'] ?? $translations[$locale]['title'] ?? '',
		'content' => $_POST['changes']['description'] ?? $_POST['content'] ?? $translations[$locale]['content'] ?? '',
		'excerpt' => $_POST['changes']['caption'] ?? $_POST['excerpt'] ?? $translations[$locale]['excerpt'] ?? '',
		'alt' => $_POST['changes']['alt'] ?? $_POST['_wp_attachment_image_alt'] ?? $translations[$locale]['alt'] ?? '',
	];

	// save
	update_post_meta( $post_id, '_translations', $translations );

	// nothing more to do
	if ( current_action() == 'add_attachment' ) return;

	// in case of translation, restore old (default language) values
	if ( $locale !== bogo_get_default_locale() ) {
		global $wpdb;

		$wpdb->update( $wpdb->posts, [
			'post_title' => $translations[bogo_get_default_locale()]['title'],
			'post_content' => $translations[bogo_get_default_locale()]['content'],
			'post_excerpt' => $translations[bogo_get_default_locale()]['excerpt'],
		], array( 'ID' => $post_id ) );

		update_post_meta(
			$post_id,
			'_wp_attachment_image_alt',
			$translations[bogo_get_default_locale()]['alt']
		);
	}
}

/**
 * Get attachment field translation.
 */
add_filter( 'edit_post_title', 'localize_the_attachment_field', 10, 2 );
add_filter( 'edit_post_content', 'localize_the_attachment_field', 10, 2 );
add_filter( 'edit_post_excerpt', 'localize_the_attachment_field', 10, 2 );

add_filter( 'the_title', 'localize_the_attachment_field', 10, 2 );
add_filter( 'the_content', 'localize_the_attachment_field' );
add_filter( 'get_the_excerpt', 'localize_the_attachment_field', 10, 2 );

add_filter( 'wp_get_attachment_caption', 'localize_the_attachment_field', 10, 2 );

function localize_the_attachment_field( $value, $post_id = 0  ) {
	if ( current_filter() == 'wp_get_attachment_caption' )
		$field = 'excerpt';
	else $field = preg_replace( '/^(edit_post_|(get_)?the_)/', '', current_filter() );

	if ( $field ) {
		$post = get_post( $post_id = $post_id->ID ?? $post_id ?: get_the_ID() );

		if ( get_post_type( $post ) == 'attachment' ) {
			if ( $_POST['post_id'] ?? null ) $locale = bogo_get_post_locale( $_POST['post_id'] );
			else $locale = bogo_get_user_locale() ?? get_locale();

			// default language don't need localization
			if ( $locale !== bogo_get_default_locale() ) {
				// get all attachment fields translations
				$translations = get_post_meta( $post_id, '_translations', true ) ?: [];
				$value = $translations[$locale][$field] ?? '';

				// make sure attachment has title ... at least default one
				if ( $field == 'title' && !$value ) {
					$value = $post->post_title;
				}
			}
		}
	}

	return $value;
}

add_filter( 'get_post_metadata', function( $value, $object_id, $meta_key, $single ) {
	if ( $meta_key == '_wp_attachment_image_alt' ) {
		if ( $_POST['post_id'] ?? null ) $locale = bogo_get_post_locale( $_POST['post_id'] );
		else $locale = is_admin() ? bogo_get_user_locale() : get_locale();

		if ( $locale !== bogo_get_default_locale() ) {
			$translations = get_post_meta( $object_id, '_translations', true ) ?: [];
			$value = $translations[ $locale ]['alt'] ?? '';

			if ( !$single ) $value = [$value];
		}
	}

	return $value;
}, 10, 4 );

/**
 *
 */
add_filter( 'posts_results', function( array $posts ) {
	if ( $_POST['post_id'] ?? null ) $locale = bogo_get_post_locale( $_POST['post_id'] );
	else $locale = is_admin() ? bogo_get_user_locale() : get_locale();

	if ( $locale !== bogo_get_default_locale() ) {
		foreach ( $posts as $post ) {
			if ( get_post_type( $post->ID ) != 'attachment' ) continue;

			$translations = get_post_meta( $post->ID, '_translations', true ) ?: [];

			foreach ( array( 'title', 'content', 'excerpt' ) as $field ) {
				$post->{"post_{$field}"} = $translations[$locale][$field]
					// make sure title is at least default title
					?? ($field == 'title' ? $post->{"post_{$field}"} : '');
			}
		}
	}

	return $posts;
} );
