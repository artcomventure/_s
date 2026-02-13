<?php

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

add_action( 'admin_enqueue_scripts', function() {
    if ( ($_GET['page'] ?? '') !== 'bogo-texts' ) return;
    // for sanitizing slugs
    wp_enqueue_script('bogo--terms-translation--slugs', BOGO_DIRECTORY_URI .'/inc/slugs/app.js', ['wp-url'], false, true );
} );

// ---
// Term slugs.

/**
 * Add slug translations to terms translation page.
 */
add_filter( 'bogo_terms_translation', function( $items, $locale ) {
	remove_filter( 'get_term', 'bogo_term_slug' );

	foreach ( get_taxonomies( array(), 'objects' ) as $taxonomy ) {
        if ( in_array( $taxonomy->name, ['nav_menu', 'wp_theme', 'wp_pattern_category'] ) ) continue;

        $post_type = get_post_type_object( $taxonomy->object_type[0] );
		$tax_labels = get_taxonomy_labels( $taxonomy );
		$terms = get_terms( array(
			'taxonomy' => $taxonomy->name,
			'orderby' => 'slug',
			'hide_empty' => false,
		) );

		foreach ( $terms as $term ) {
			$name = sprintf( '%s:%d:slug', $taxonomy->name, $term->term_id );
			$items[] = array(
				'name' => $name,
				'original' => $term->slug,
				'translated' => bogo_translate(
					$name, $taxonomy->name, $term->slug
				),
				'context' => sprintf( __( 'Slug (%s: %s)', 'bogo' ), $post_type->labels->singular_name, $tax_labels->singular_name ),
				'cap' => $taxonomy->cap->edit_terms,
			);
		}
	}

	add_filter( 'get_term', 'bogo_term_slug', 10, 2 );

    return $items;
}, 10, 2 );

/**
 * Set translated slug in term.
 */
add_filter( 'get_term', 'bogo_term_slug', 10, 2 );
function bogo_term_slug( $term, $taxonomy ) {
	$term->slug = bogo_translate(
		sprintf( '%s:%d:slug', $term->taxonomy, $term->term_id ),
		$term->taxonomy,
		$term->slug
	);

	return $term;
}

/**
 * Replace translated query parameter with original
 */
add_action( 'pre_get_posts', function( $query ) {
	if ( !is_admin() && $query->is_main_query() ) {
		foreach ( get_taxonomies( [], 'objects' ) as $taxonomy ) {
            if ( !$queried_term = $query->get( $taxonomy->name ) ) continue;

			foreach ( get_terms( array(
				'taxonomy' => $taxonomy->name,
				'orderby' => 'slug',
				'hide_empty' => false,
			) ) as $term ) {
				if ( $queried_term !== $term->slug ) continue;

                // get original term
				remove_filter( 'get_term', 'bogo_term_slug' );
                $term = get_term( $term->term_id, $taxonomy->name );
				add_filter( 'get_term', 'bogo_term_slug', 10, 2 );

                // set original term slug
				$query->set( $taxonomy->name, $term->slug );
            }
		}
	}
} );

// ---
// Post-type and taxonomy rewrite slugs.

// for making the slugs (to be translated) kind of unique.
const SLUG_TRANSLATION_PREFIX = 'guls--';
const SLUG_TRANSLATION_SUFFIX = '--slug';

/**
 * @param string $slug
 * @return string
 */
function slug_encode( string $slug ): string {
    return SLUG_TRANSLATION_PREFIX . $slug . SLUG_TRANSLATION_SUFFIX;
}

/**
 * @param string $slug
 * @return string
 */
function slug_decode( string $slug ): string {
    return preg_replace(
        '/^' . SLUG_TRANSLATION_PREFIX . '(.+)' . SLUG_TRANSLATION_SUFFIX . '$/',
        "$1",
        $slug
    );
}

/**
 * Register slugs option.
 */
add_action( 'admin_init', function () {
	register_setting( 'bogo', 'slugs' );
} );

/**
 * Add admin page as submenu of "Languages".
 */
add_action( 'admin_menu', function() {
	add_submenu_page(
		'bogo',
		__( 'Slugs Translation', 'bogo' ),
		__( 'Slugs Translation', 'bogo' ),
		'bogo_manage_language_packs',
		'slugs',
		function() { ?>

            <div class="wrap">
                <h1><?php _e( 'Slugs Translation', 'bogo' ) ?></h1>

                <form method="post" action="options.php">
					<?php settings_fields( 'bogo' );
					$slugs = get_option( 'slugs', [] ); ?>

                    <style>
                        .column-context,
                        .column-original {
                            min-width: 1% !important;
                            white-space: nowrap;
                        }

                        .column-translation label {
                            width: 5.2ch;
                            overflow: hidden;
                            white-space: nowrap;
                        }
                    </style>

                    <table class="wp-list-table widefat striped table-view-list">
                        <colgroup>
                            <col class="column-context" />
                            <col class="column-translation" />
                            <col class="column-original" />
                        </colgroup>
                        <thead><tr>
                            <th scope="col" id="context" class="manage-column column-context"><?php _e( 'Context', 'bogo' ) ?></th>
                            <th scope="col" id="translation" class="manage-column column-translation"><?php _e( 'Translation', 'bogo' ) ?></th>
                            <th scope="col" id="original" class="manage-column column-original column-primary"><?php _e( 'Original' ) ?></th>
                        </tr></thead>

                        <tbody id="the-list">

                        <tr>
                            <th colspan="3"><h2><?php _e( 'Post Types', 'bogo' ) ?></h2></th>
                        </tr>

						<?php foreach ( $localizable_post_types = bogo_localizable_post_types() as $post_type ) {
							$post_type = get_post_type_object( $post_type );
							if ( !$post_type || $post_type->rewrite === false ) continue;

							$original = $post_type->rewrite['slug'] ?? $post_type->name;
                            $original = slug_decode( $original ); ?>

                            <tr>
                                <td class="context column-context" data-colname="<?php _e( 'Context', 'bogo' ) ?>"><?php printf( __( '%s (Post Type)', 'bogo' ), $post_type->labels->singular_name ) ?></td>
                                <td class="translation column-translation" data-colname="<?php _e( 'Translation', 'bogo' ) ?>">
                                    <div style="display: grid; gap: .5em;">
			                            <?php foreach ( bogo_available_locales() as $locale ) {
				                            $language = bogo_get_language( $locale ); ?>
                                            <div style="display: flex; align-items: center; gap: .42em">
                                                <?php $id = "slug--post-type--$post_type->name"; ?>
                                                <label for="<?php echo $id ?>" style="font-family: monospace; title="<?php _e( $language ) ?>"><?php _e( $locale ) ?></label>
                                                <input type="text" name="slugs[post-types][<?php echo $post_type->name ?>][<?php echo $locale ?>]" id="<?php echo $id ?>" class="large-text"
                                                       value="<?php echo $slugs['post-types'][$post_type->name][$locale] ?? '' ?>" placeholder="<?php echo $original ?>">
                                            </div>
			                            <?php } ?>
                                    </div>
                                </td>
                                <td class="original column-original column-primary" data-colname="<?php _e( 'Original', 'bogo' ) ?>">
									<?php echo $original ?>
                                </td>
                            </tr>

						<?php } ?>

                        <tr>
                            <th colspan="3"><h2><?php _e( 'Taxonomies', 'bogo' ) ?></h2></th>
                        </tr>

                        <?php foreach ( get_object_taxonomies(
	                        $localizable_post_types, 'objects'
                        ) as $taxonomy ) {
	                        $original = $taxonomy->rewrite['slug'] ?? $taxonomy->name;
	                        $original = slug_decode( $original );

                            $post_types = array_map( 'get_post_type_object', $taxonomy->object_type );
	                        $post_types = array_column( $post_types, 'label' );?>

                            <tr>
                                <td class="context column-context" data-colname="<?php _e( 'Context', 'bogo' ) ?>"><?php printf( '%s (%s)', $taxonomy->labels->singular_name, implode( ', ', $post_types ) ) ?></td>
                                <td class="translation column-translation" data-colname="<?php _e( 'Translation', 'bogo' ) ?>">
                                    <div style="display: grid; gap: .5em;">
				                        <?php foreach ( bogo_available_locales() as $locale ) {
					                        $language = bogo_get_language( $locale ); ?>
                                            <div style="display: flex; align-items: center; gap: .42em">
	                                            <?php $id = "slug--taxonomy--$post_type->name"; ?>
                                                <label for="<?php echo $id ?>" style="font-family: monospace;" title="<?php _e( $language ) ?>"><?php _e( $locale ) ?></label>
                                                <input type="text" name="slugs[taxonomies][<?php echo $taxonomy->name ?>][<?php echo $locale ?>]" id="<?php echo $id ?>" class="large-text"
                                                       value="<?php echo $slugs['taxonomies'][$taxonomy->name][$locale] ?? '' ?>" placeholder="<?php echo $original ?>">
                                            </div>
				                        <?php } ?>
                                    </div>
                                </td>
                                <td class="original column-original column-primary" data-colname="<?php _e( 'Original', 'bogo' ) ?>">
			                        <?php echo $original ?>
                                </td>
                            </tr>

                        <?php } ?>

                        </tbody>

                        <tfoot><tr>
                            <th scope="col" class="manage-column column-context"><?php _e( 'Context', 'bogo' ) ?></th>
                            <th scope="col" class="manage-column column-translation"><?php _e( 'Translation', 'bogo' ) ?></th>
                            <th scope="col" class="manage-column column-original column-primary"><?php _e( 'Original', 'bogo' ) ?></th>
                        </tr></tfoot>

                    </table>

					<?php submit_button(); ?>

                </form>
            </div>

		<?php }
	);
} );

/**
 * Sanitize slugs before saving.
 */
add_filter( 'pre_update_option_slugs', function( $value ) {
	foreach ( $value as $group => $types ) {
        foreach ( $types as $name => $translations ) {
	        foreach ( $translations as $locale => $slug ) {
		        $slug = trim( $slug, '/' );
		        $slug = explode( '/', $slug );
		        $slug = array_map( 'sanitize_title', $slug );
		        $value[$group][$name][$locale] = implode ( '/', $slug );
	        }
        }
	}

	return $value;
} );

/**
 * Flush rewrite rules when slugs are changed.
 */
add_action( 'update_option_slugs', function( $option ) {
	flush_rewrite_rules();
} );

/**
 * Change the post-type slug to a unique string to be clearly replaceable.
 */
add_filter( 'register_post_type_args', function( $args, $post_type ) {
	if ( bogo_is_localizable_post_type( $post_type ) ) {
        if ( ($args['rewrite'] ?? true) !== false ) {
	        $slug = $args['rewrite']['slug'] ?? $post_type;
	        $args['rewrite'] = ['slug' => slug_encode( $slug )];
        }
    }

	return $args;
}, 10, 2 );

if ( !function_exists( 'bogo_is_localizable_taxonomy' ) ) {
	/**
     * Checks whether the given taxonomy is localizable or not.
     *
	 * @param $taxonomy
	 * @return bool
	 */
    function bogo_is_localizable_taxonomy( $taxonomy ): bool {
	    $localizable_post_types = bogo_localizable_post_types();
	    $localizable_taxonomies = get_object_taxonomies( $localizable_post_types, 'objects' );

        return in_array( $taxonomy->name ?? $taxonomy, $localizable_taxonomies );
    }
}


/**
 * Change the taxonomy slug to a unique string to be clearly replaceable.
 */
add_filter( 'register_taxonomy_args', function( $args, $taxonomy ) {
	if ( !empty( $args['rewrite'] ?? true ) ) {
		$slug = $args['rewrite']['slug'] ?? $taxonomy;
		$args['rewrite'] = ['slug' => slug_encode( $slug )];
	}

    return $args;
}, 10, 2 );

/**
 * Replace unique slug string with all translations.
 */
add_filter( 'rewrite_rules_array', function( $rules ) {
    // get translated slugs
    $slugs = get_option( 'slugs', [] );

    // loop through localizable post-types
	foreach ( $localizable_post_types = bogo_localizable_post_types() as $post_type ) {
		$post_type = get_post_type_object( $post_type );
		if ( !$post_type || $post_type->rewrite === false ) continue;

        // add default slug
//        $slugs['post-types'][$post_type->name] = [slug_decode( $post_type->rewrite['slug'] )] + ($slugs['post-types'][$post_type->name] ?? []);
        $slugs['post-types'][$post_type->name][] = slug_decode( $post_type->rewrite['slug'] );

        $updated_rules = []; // holds the updated rules

        // ---
        // Post-types.

		foreach ( $rules as $regex => $query ) {
            // look for encoded slug
			if ( str_contains( $regex, $post_type->rewrite['slug'] ) ) {
                // add all possible slugs
                foreach ( array_filter( array_unique( $slugs['post-types'][$post_type->name] ) ) as $locale => $translation ) {
			        $updated_regex = str_replace( $post_type->rewrite['slug'], $translation, $regex );
			        // normally not necessary, but just to be safe
			        $updated_query = str_replace( $post_type->rewrite['slug'], $translation, $query );

			        // add updated rule
			        $updated_rules[$updated_regex] = $updated_query; // at the end
	                $updated_rules = [$updated_regex => $updated_query] + $updated_rules; // in the very beginning
		        }
	        }
			// ... or add rule unchanged
			else $updated_rules[$regex] = $query;
        }

        // for next loop
        $rules = $updated_rules ?: $rules;
	}

	$localizable_taxonomies = get_object_taxonomies(
		$localizable_post_types,
		'objects'
	);

    // ---
    // Taxonomies.

	foreach ( $localizable_taxonomies as $taxonomy ) {
		if ( empty( $taxonomy->rewrite ) ) continue;

		// add default slug
		$slugs['taxonomies'][$taxonomy->name] = [slug_decode( $taxonomy->rewrite['slug'] )] + ($slugs['taxonomies'][$taxonomy->name] ?? []);

		$updated_rules = []; // holds the updated rules

		// loop through each and every rule
		foreach ( $rules as $regex => $query ) {
			// look for encoded slug
			if ( str_contains( $regex, $taxonomy->rewrite['slug'] ) ) {
				// add all possible slugs
				foreach ( array_filter( array_unique( $slugs['taxonomies'][$taxonomy->name] ) ) as $locale => $translation ) {
					$updated_regex = str_replace( $taxonomy->rewrite['slug'], $translation, $regex );
					// normally not necessary, but just to be safe
					$updated_query = str_replace( $taxonomy->rewrite['slug'], $translation, $query );

					// add updated rule
//					$updated_rules[$updated_regex] = $updated_query; // at the end
					$updated_rules = [$updated_regex => $updated_query] + $updated_rules; // in the very beginning
				}
			}
			// ... aor add rule unchanged
			else $updated_rules[$regex] = $query;
		}

		// for next loop
		$rules = $updated_rules ?: $rules;
	}

	return $rules;
} );

/**
 * Replace encoded slug with actual localized slug.
 */
add_filter( 'post_type_link', function( $post_link, $post ) {
    if ( bogo_is_localizable_post_type( $post->post_type ) ) {
	    $post_type = get_post_type_object( $post->post_type );
	    if ( $post_type && $post_type->rewrite !== false ) {
		    // get post type specific slugs
		    $slugs = get_option( 'slugs', [] )['post-types'][$post->post_type] ?? [];

		    if ( $slug = ($slugs[bogo_get_post_locale( $post->ID )] ?? '') ?: slug_decode( $post_type->rewrite['slug'] ) ) {
			    $post_link = str_replace( $post_type->rewrite['slug'], $slug, $post_link );
		    }
	    }
	}

	return $post_link;
}, 10, 2 );

/**
 * Replace encoded slug with actual localized slug.
 */
add_filter( 'term_link', function( $termlink, $term, $taxonomy ) {
    $taxonomy = get_taxonomy( $taxonomy );
    if ( $taxonomy && $taxonomy->rewrite !== false ) {
	    // get post type specific slugs
        $slugs = get_option( 'slugs', [] );
	    $slugs = get_option( 'slugs', [] )['taxonomies'][$taxonomy->name] ?? [];

	    if ( $slug = ($slugs[get_locale()] ?? '') ?: slug_decode( $taxonomy->rewrite['slug'] ) ) {
		    $termlink = str_replace( $taxonomy->rewrite['slug'], $slug, $termlink );
	    }
	}

	return $termlink;
}, 10, 3 );

/**
 * Force localized URL to prevent duplicated content (SEO).
 *
 * Update:
 * Shouldn't be needed due to canonical URL.
 */
//add_action( 'template_redirect', function () {
//    if ( !$post = get_post() ) return;
//    if ( !bogo_is_localizable_post_type( $post->post_type ) ) return;
//
//	$post_type = get_post_type_object( $post->post_type );
//	if ( $post_type && $post_type->rewrite !== false ) {
//		global $wp;
//
//		$current_url = rtrim( home_url( $wp->request ), '/' );
//		$permalink = rtrim( get_permalink( $post->ID ) , '/' );
//
//		if ( $current_url != $permalink ) {
//			wp_redirect( $permalink );
//			exit();
//		}
//	}
//} );
