<?php

define( 'SECURITY_DIRECTORY', dirname( __FILE__ ) );
define( 'SECURITY_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/security' );

// t9n
add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'security', SECURITY_DIRECTORY . '/languages' );
} );

add_action( 'admin_enqueue_scripts', function() {
	if ( get_current_screen()->base !== 'dashboard' ) {
		return;
	}

	wp_enqueue_style( 'security', SECURITY_DIRECTORY_URI . '/style.css', [ 'dashicons' ] );
} );

/**
 * Add dashboard widget for security alerts.
 */
add_action( 'wp_dashboard_setup', function() {
	if ( !apply_filters( 'security_widget', user_has_role( 'administrator' ) ) ) {
		return;
	}

	wp_add_dashboard_widget( "security_widget", __( 'Security', 'security' ), function() {

		echo wpautop( __( 'To secure<sup>1</sup> your site from hackers, malicious attackers and automated bots and prevent DDoS- and brute-force-attacks, here are some points that should be met:', 'security' ) );

		$checks = apply_filters( 'security_checks', [] );
		uasort( $checks, function( $a, $b ) {
			// sort by `title`
			if ( $a['status'] === $b['status'] ) {
				if ( -1 >= strcasecmp( $a['title'], $b['title'] ) ) {
					return -1;
				}

				return 1;
			}

			// `true` to top
			if ( is_bool( $a['status'] ) && is_bool( $b['status'] ) ) {
				return $a['status'] - $b['status'];
			}

			if ( is_string( $a['status'] ) ) {
				if ( $b['status'] === TRUE ) {
					return -1;
				}

				return 1;
			}

			if ( is_string( $b['status'] ) ) {
				if ( $a['status'] === TRUE ) {
					return 1;
				}

				return -1;
			}

			return 0;
		} );

		foreach ( $checks as $check => $data ) : ?>

          <div
            class="postbox closed <?php echo "$check-check"; ?> <?php echo is_string( $data['status'] ) ? $data['status'] : ( $data['status'] ? 'passed' : 'failed' ) ?>">
            <div class="postbox-header">
              <h3><?php echo $data['title'] ?></h3>
              <div class="handle-actions hide-if-no-js">
                <button type="button" class="handlediv" aria-expanded="false">
                  <span
                    class="screen-reader-text"><?php printf( __( 'Bedienfeld umschalten: %s' ), $data['title'] ) ?></span>
                  <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
              </div>
            </div>
            <div class="inside">
              <?php if ( $data['info'] ?? '' ) {
                echo '<div class="info">' . wpautop( $data['info'] ) . '</div>';
              }

              if ( $data['description'] ?? '' ) {
                echo '<div class="description">' . $data['description'] . '</div>';
              } ?>
            </div>
          </div>

		<?php endforeach; ?>

      <div
        class="postbox closed">
        <div class="postbox-header">
          <h3><?php echo $title = __( 'Further recommendations', 'security' ) ?></h3>
          <div class="handle-actions hide-if-no-js">
            <button type="button" class="handlediv" aria-expanded="false">
                  <span
                    class="screen-reader-text"><?php printf( __( 'Bedienfeld umschalten: %s' ), $title ) ?></span>
              <span class="toggle-indicator" aria-hidden="true"></span>
            </button>
          </div>
        </div>
        <div class="inside">
          <ul>
            <li>
              <?php printf(
                __( "Have a look at WordPress' <a href='%s'>site health</a> section", 'security' ),
                admin_url( 'site-health.php' )
              ) ?>
            </li>
            <li>
				<?php printf( __( "We encourage you to use a 2-factor-authentication and recommend the %s plugin", 'security' ), '<a href="https://de.wordpress.org/plugins/wordfence-login-security/" target="_blank">Wordfence Login Security</a>' ) ?>
            </li>
            <?php if ( !isset($checks['spam']) ) : ?>
                <li>
                    <?php _e( "Secure your contact forms with some kind of SPAM protection", 'security' ); ?>
                </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>

	<?php echo wpautop( __( '<sup>1</sup> There will never be an absolute security but these points will help to keep it as secure as possible.', 'security' ) );

  } );
} );

// enable/disable loosen security
add_action( 'customize_register', function( $wp_customize ) {
	$wp_customize->add_setting( 'loose_security', array(
		'type' => 'option',
		'capability' => 'manage_options',
	) );

	$wp_customize->add_control( 'loose_security', array(
		'label' => __( 'Loosen security', 'utilities' ),
		'description' => __( "This features should be disabled at all times! But there are cases to enable it temporarily. Use this with caution!", 'utilities' ),
		'section' => 'utilities',
		'type' => 'checkbox'
	) );
}, 8 );

// auto include /inc files
auto_include_files( dirname( __FILE__ ) . '/inc' );