<?php

/**
 * Gmaps' settings page styles/scripts.
 */
add_action( 'admin_enqueue_scripts', function () {
	global $pagenow;

	// mapbox setting page only
	if ( $pagenow != 'options-general.php' || ( $_GET['page'] ?? '' ) != 'gmaps' ) {
		return;
	}

	wp_enqueue_style( 'gmaps-settings', GMAPS_DIRECTORY_URI . '/inc/settings/style.css', [] );
    wp_enqueue_script( 'gmaps-settings', GMAPS_DIRECTORY_URI . '/inc/settings/app.js', [], false, true );
	gmaps_localize_script( 'gmaps-settings' );
} );

/**
 * Get map settings (filled up with default values).
 *
 * @return array
 */
function gmaps_get_settings(): array {
	if ( ! is_array( $settings = get_option( 'gmaps', [] ) ) ) {
		$settings = [];
	}

	// merge defaults
	$settings += [
		'apiKey' => '',
		'mapId' => '',
        'styles' => '',
        'zoom' => 13,
        'center' => []
	];

    // default position (SMITH)
    $settings['center'] += ['lat' => 52.490616318609355, 'lng' => 13.422908715342453];

    // type number
    $settings['zoom'] = floatval($settings['zoom']);
    $settings['center']['lat'] = floatval($settings['center']['lat']);
	$settings['center']['lng'] = floatval($settings['center']['lng']);

    // parse styles
    if ( $settings['styles'] && is_string( $settings['styles'] ) ) try {
	    $settings['styles'] = json_decode( $settings['styles'], true );
    }
    catch( Exception $e ) { $settings['styles'] = ''; }
    $settings['styles'] = $settings['styles'] ?: [];

	return $settings;
}

/**
 * Retrieve specific mapbox setting.
 * To reach values in multidimensional array use "/" between _levels_.
 *
 * @param string $setting
 * @param mixed $default
 *
 * @return mixed
 */
function gmaps_get_setting( string $setting, mixed $default = null ): mixed {
	$settings = gmaps_get_settings();

	foreach ( explode( '/', $setting ) as $setting ) {
		if ( ! isset( $settings[ $setting ] ) ) {
			return $default;
		}
		$settings = $settings[ $setting ];
	}

	return $settings;
}

/**
 * Register "mapbox" as option.
 */
add_action( 'admin_init', function () {
	register_setting( 'gmaps', 'gmaps' );
} );

// add settings menu
add_action( 'admin_menu', function() {
	add_options_page(
		__( 'Google Maps', 'gmaps' ),  // Page title
		__( 'Google Maps', 'gmaps' ),  // Menu title
		'manage_options',   // Capability
		'gmaps',  // Menu slug
		function() {

			$tabs        = [ '' => __( 'General' ), 'map' => __( 'Map', 'gmaps' ) ];
			$current_tab = $_GET['tab'] ?? ''; ?>

			<form method="post" action="options.php">
				<?php settings_fields( 'gmaps' ); ?>

				<div class="wrap">
					<h1><?php _e( 'Settings' ) ?> › <?php _e( 'Google Maps', 'gmaps' ) ?></h1>

                    <div class="notice notice-warning update-nag inline" style="margin-top: 1em">
						<?php printf( __( 'Add map to content by adding the shortcode %s', 'gmaps' ), '<code>[gmap]</code>' ) ?>
                    </div>

					<nav class="nav-tab-wrapper hide-if-no-js">
						<?php foreach ( $tabs as $tab => $label ) : ?>
							<a href="<?php echo add_query_arg( array_filter( [
								'page'      => 'gmaps',
								'tab'       => $tab
							] ), admin_url( '/options-general.php' ) ) ?>"
							   id="<?php echo 'nav-tab-' . ( $tab ?: 'general' ) ?>"
							   role="tab"
							   aria-controls="<?php echo 'tab-' . ( $tab ?: 'general' ) ?>"
							   aria-selected="<?php echo $current_tab == $tab ? 'true' : 'false' ?>"
							   class="nav-tab<?php echo $current_tab == $tab ? ' nav-tab-active' : '' ?>"><?php echo $label ?></a>
						<?php endforeach; ?>
					</nav>

					<?php foreach ( $tabs as $tab => $label ) : ?>
						<div id="<?php echo 'tab-' . ( $tab ?: 'general' ) ?>" role="tabpanel"
						     aria-labelledby="<?php echo 'nav-tab-' . ( $tab ?: 'general' ) ?>"
						     class="tab-content"<?php echo $current_tab != $tab ? ' style="display:none;"' : '' ?>>

							<h2 class="hide-if-js"><?php echo $label ?></h2>

							<?php if ( $tab == 'map' ) { ?>

                                <table class="form-table" role="presentation">
                                    <tbody>
                                    <tr>
                                        <th scope="row">
                                            <label style="">
                                                <?php _e( 'Zoom', 'gmaps' ) ?>
                                                <input type="number" name="gmaps[zoom]" value="<?php echo esc_attr(gmaps_get_setting( 'zoom' )) ?>" min="0" max="22" />
                                            </label>
                                            <label>
	                                            <?php _e( 'Latitude', 'gmaps' ) ?>
                                                <input type="text" name="gmaps[center][lat]" value="<?php echo esc_attr(gmaps_get_setting( 'center/lat' )) ?>" min="-90" max="90" />
                                            </label>
                                            <label>
	                                            <?php _e( 'Longitude', 'gmaps' ) ?>
                                                <input type="text" name="gmaps[center][lng]" value="<?php echo esc_attr(gmaps_get_setting( 'center/lng' )) ?>" min="-180" max="180" />
                                            </label>
                                            <label>
	                                            <?php _e( 'Map ID', 'gmaps' ) ?>
                                                <input type="text" id="gmaps-map-id" name="gmaps[mapId]" value="<?php echo esc_attr(gmaps_get_setting( 'mapId' )) ?>" />
                                                <p class="description">
		                                            <?php echo sprintf(
			                                            '<a href="%s" target="_blank">' . __( 'Design your map', 'gmaps' ) . '</a>.',
			                                            'https://console.cloud.google.com/google/maps-apis/studio/maps'
		                                            ) ?>
                                                </p>
                                            </label>
                                            <label>
	                                            <?php _e( 'Styles', 'gmaps' ) ?>
                                                <textarea name="gmaps[styles]"><?php
                                                    $styles = gmaps_get_setting( 'styles' );
                                                    echo $styles ? json_encode( $styles ) : '';
                                                ?></textarea>
                                                <p class="description"><?php
                                                    echo sprintf(
                                                        '<a href="%s" target="_blank">' . __( 'Create map style', 'gmaps' ) . '</a>.',
	                                                    'https://mapstyle.withgoogle.com/'
                                                    )
                                                ?></p>
                                            </label>
                                        </th>
                                        <td style="vertical-align: top;">
                                            <?php echo gmap_shortcode() ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>

							<?php } // general tab
							else { ?>

								<table class="form-table" role="presentation">
									<tbody>
									<tr>
										<th scope="row">
											<label for="gmaps-api-key"><?php _e( 'API key', 'gmaps' ) ?></label>
										</th>
										<td>
											<input type="text" id="gmaps-api-key" name="gmaps[apiKey]" value="<?php echo esc_attr(gmaps_get_setting( 'apiKey' )) ?>" size="60" />
											<p class="description">
												<?php printf(
													__( 'Create/get an <a href="%s">API key</a>.', 'gmaps' ),
													'https://console.cloud.google.com/apis/credentials'
												) ?>
											</p>
										</td>
									</tr>
									</tbody>
								</table>

							<?php } ?>

						</div>
					<?php endforeach;

					submit_button() ?>
				</div>
			</form>
		<?php }
	);
} );
