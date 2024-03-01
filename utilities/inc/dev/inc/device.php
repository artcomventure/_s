<?php

/**
 * Device dev panel info.
 */
add_action( 'dev-info-panel', function() {
    // https://www.php.net/manual/en/function.get-browser.php#101125

    $platform = '';
    $device = __( 'PC/Laptop', 'dev' );
    $browser = [
        'name' => '',
        'version' => '???'
    ];

    $ua = $_SERVER['HTTP_USER_AGENT'];

    // get platform
    if ( preg_match('/linux/i', $ua ) && preg_match('/x11/i', $ua ) ) $platform = 'Linux';
    if ( preg_match('/x11/i', $ua ) ) $platform = 'UNIX';
    elseif ( preg_match('/like mac/i', $ua ) ) $platform = 'iOS';
    elseif ( preg_match('/macintosh|mac os x/i', $ua ) ) $platform = 'Mac OS';
    elseif ( preg_match('/windows|win32/i', $ua ) ) $platform = 'Windows';

    // get tablet device
    if ( preg_match('/ipad/i', $ua ) ) $device = __( 'iPad', 'dev' );
    else if ( preg_match('/kindle/i', $ua ) ) $device = __( 'Kindle', 'dev' );
    else if ( preg_match( '/(tablet|(android(?!.*mobile))|(windows(?!.*phone)(.*touch))|playbook|silk|(puffin(?!.*(IP|AP|WP))))/i', $ua ) )
        $device = __( 'Tablet', 'dev' );
    // ... or phone
    else if ( preg_match('/pixel ([^\)+])/i', $ua, $matches ) ) $device = __('Pixel', 'dev') . $matches[1];
    else if ( preg_match('/ipod/i', $ua ) ) $device = __( 'iPod', 'dev' );
    else if ( preg_match('/iphone/i', $ua ) ) $device = __( 'iPhone', 'dev' );
    else if ( preg_match('/blackberry/i', $ua ) ) $device = __( 'Blackberry', 'dev' );
    else if ( preg_match('/(mobi|phone|opera mini|fennec|minimo|symbian|psp|nintendo ds|archos|skyfire|puffin|blazer|bolt|gobrowser|iris|maemo|semc|teashark|uzard)/i', $ua ) )
        $device = __( 'Phone', 'dev' );

    // next get the name of the useragent yes separately and for good reason
    if ( preg_match('/MSIE/i', $ua ) && !preg_match('/Opera/i', $ua ) ) {
        $browser['name'] = 'Internet Explorer';
        $ub = "MSIE";
    }
    elseif ( preg_match('/Firefox/i', $ua ) ) {
        $browser['name'] = $ub = 'Firefox';
    }
    elseif ( preg_match('/Chrome/i', $ua ) ) {
        $browser['name'] = $ub = 'Chrome';
    }
    elseif (preg_match('/Safari/i', $ua ) ) {
        $browser['name'] = $ub = 'Safari';
    }
    elseif ( preg_match('/Opera/i', $ua ) ) {
        $browser['name'] = $ub = 'Opera';
    }
    elseif ( preg_match('/Netscape/i', $ua ) ) {
        $browser['name'] = $ub = 'Netscape';
    }

    // get the version number
    $pattern = '#(?<browser>' . join('|', ['Version', $ub, 'other']) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

    if ( preg_match_all( $pattern, $ua, $matches ) ) {
        if ( count($matches['browser']) > 1 ) {
            // we will have two since we are not using 'other' argument yet
            // see if version is before or after the name
            if ( strripos( $ua,'Version' ) < strripos( $ua, $ub ) )
                $browser['version'] = $matches['version'][0];
            else $browser['version']= $matches['version'][1];
        }
        else $browser['version']= $matches['version'][0];
    } ?>

    <div id="device-info" class="hidden">
        <?php if ( $browser['name'] ) : ?>
            <span title="<?php _e( 'Browser', 'dev' ) ?>" data-copy="">
                <?php printf( __( '%s v%s', 'dev' ), $browser['name'], $browser['version'] ) ?>
            </span>
        <?php endif; ?>
        <?php if ( $platform ) : ?>
            <span title="<?php _e( 'OS', 'dev' ) ?>" data-copy="">
                <?php echo $platform ?>
            </span>
        <?php endif; ?>
        <span title="<?php _e( 'Device assumption', 'dev' ) ?>" data-copy="">
            <?php echo $device ?>
        </span>
    </div>

<?php } );
