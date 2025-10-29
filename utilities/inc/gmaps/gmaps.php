<?php

define( 'GMAPS_DIRECTORY', dirname( __FILE__ ) );
define( 'GMAPS_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/gmaps' );

// t9n
add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'gmaps', GMAPS_DIRECTORY . '/languages' );
} );

/**
 * Enqueue scripts and styles.
 */
function gmaps_register_scripts(): void {
	wp_enqueue_script( 'gmaps', GMAPS_DIRECTORY_URI . '/app.js', ['behaviours'], false, true );
	gmaps_localize_script( 'gmaps' );

	wp_enqueue_style( 'gmaps', GMAPS_DIRECTORY_URI . '/style.css' );
}

add_action( 'wp_enqueue_scripts', 'gmaps_register_scripts' );
// make scripts available in admin area
add_action( 'admin_enqueue_scripts', 'gmaps_register_scripts' );

function gmaps_localize_script( $handle ) {
	wp_localize_script(
		$handle,
		'gmaps',
		['settings' => gmaps_get_settings() + [
			'home_url' => home_url()
		] ]
	);
}

add_action( 'admin_head', 'gmaps_enqueue_api' );
add_action( 'wp_head', 'gmaps_enqueue_api' );
function gmaps_enqueue_api() {
    if ( !$gmaps_api_key = gmaps_get_setting( 'apiKey' ) ) return; ?>

    <script>
      (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
        key: "<?php echo $gmaps_api_key ?>",
        v: "weekly",
      });
    </script>

<?php }

// auto include utilities files
auto_include_files( GMAPS_DIRECTORY . '/inc' );