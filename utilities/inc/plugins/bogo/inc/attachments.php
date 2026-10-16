<?php

/**
 * Save localized attachment data (title, description, caption and alt).
 */
add_action( 'attachment_updated', 'localize_attachment', 10, 3 );
add_action( 'add_attachment', 'localize_attachment', 10, 3 );
function localize_attachment( $post_id, $post_after = null, $post_before = null ): void {
	$locale = localize_attachment_locale();

	// get all attachment fields translations
	$translations = localize_attachment_translations( $post_id );

	$post = wp_unslash( $_POST );
	$changes = is_array( $post['changes'] ?? null ) ? $post['changes'] : [];

	// update data for current language
	$translations[$locale] = localize_attachment_sanitize( [
		'title' => $changes['title'] ?? $post['post_title'] ?? $translations[$locale]['title'] ?? '',
		'content' => $changes['description'] ?? $post['content'] ?? $translations[$locale]['content'] ?? '',
		'excerpt' => $changes['caption'] ?? $post['excerpt'] ?? $translations[$locale]['excerpt'] ?? '',
		'alt' => $changes['alt'] ?? $post['_wp_attachment_image_alt'] ?? $translations[$locale]['alt'] ?? '',
	] );

	// save
	update_post_meta( $post_id, '_translations', wp_slash( $translations ) );

	// nothing more to do
	if ( current_action() == 'add_attachment' ) return;

	// in case of translation, restore old (default language) values
	if ( $locale !== bogo_get_default_locale() && $default = $translations[bogo_get_default_locale()] ?? null ) {
		global $wpdb;

		$wpdb->update( $wpdb->posts, [
			'post_title' => $default['title'],
			'post_content' => $default['content'],
			'post_excerpt' => $default['excerpt'],
		], array( 'ID' => $post_id ) );

		clean_post_cache( $post_id );

		update_post_meta( $post_id, '_wp_attachment_image_alt', wp_slash( $default['alt'] ) );
	}
}

/**
 * Locale of the post being edited (media modal) or of the current user/request.
 */
function localize_attachment_locale(): string {
	if ( $post_id = absint( $_POST['post_id'] ?? 0 ) ) return bogo_get_post_locale( $post_id );

	return bogo_get_user_locale() ?? get_locale();
}

/**
 * Sanitize one locale's attachment fields.
 */
function localize_attachment_sanitize( $fields ): array {
	$fields = is_array( $fields ) ? $fields : [];
	$string = fn( $value ) => is_scalar( $value ) ? (string) $value : '';

	return [
		'title' => wp_kses_post( $string( $fields['title'] ?? '' ) ),
		'content' => wp_kses_post( $string( $fields['content'] ?? '' ) ),
		'excerpt' => wp_kses_post( $string( $fields['excerpt'] ?? '' ) ),
		'alt' => wp_strip_all_tags( $string( $fields['alt'] ?? '' ), true ),
	];
}

/**
 * Get all attachment fields translations.
 * Sanitized on read too: values stored before sanitizing existed.
 */
function localize_attachment_translations( int $post_id ): array {
	$translations = get_post_meta( $post_id, '_translations', true );

	return is_array( $translations ) ? array_map( 'localize_attachment_sanitize', $translations ) : [];
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
			$locale = localize_attachment_locale();

			// default language don't need localization
			if ( $locale !== bogo_get_default_locale() ) {
				// get all attachment fields translations
				$translations = localize_attachment_translations( $post_id );
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
		if ( $post_id = absint( $_POST['post_id'] ?? 0 ) ) $locale = bogo_get_post_locale( $post_id );
		else $locale = is_admin() ? bogo_get_user_locale() : get_locale();

		if ( $locale !== bogo_get_default_locale() ) {
			$translations = localize_attachment_translations( $object_id );
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
	if ( $post_id = absint( $_POST['post_id'] ?? 0 ) ) $locale = bogo_get_post_locale( $post_id );
	else $locale = is_admin() ? bogo_get_user_locale() : get_locale();

	if ( $locale !== bogo_get_default_locale() ) {
		foreach ( $posts as $post ) {
			if ( get_post_type( $post->ID ) != 'attachment' ) continue;

			$translations = localize_attachment_translations( $post->ID );

			foreach ( array( 'title', 'content', 'excerpt' ) as $field ) {
				$post->{"post_{$field}"} = $translations[$locale][$field]
					// make sure title is at least default title
					?? ($field == 'title' ? $post->{"post_{$field}"} : '');
			}
		}
	}

	return $posts;
} );
