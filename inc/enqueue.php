<?php

defined( 'ABSPATH' ) || exit;

function mitka_asset_version( $file ) {
	$path = MITKA_DIR . '/assets/build/' . $file;
	return file_exists( $path ) ? (string) filemtime( $path ) : null;
}

function mitka_enqueue_assets() {
	wp_enqueue_style( 'mitka-main', MITKA_URI . '/assets/build/main.css', array(), mitka_asset_version( 'main.css' ) );
	wp_enqueue_script(
		'mitka-main',
		MITKA_URI . '/assets/build/main.js',
		array(),
		mitka_asset_version( 'main.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'mitka_enqueue_assets' );

function mitka_preload_fonts() {
	$fonts = array( 'manrope-cyrillic.woff2', 'unbounded-cyrillic.woff2' );
	if ( function_exists( 'pll_current_language' ) && 'en' === pll_current_language() ) {
		$fonts = array( 'manrope-latin.woff2', 'unbounded-latin.woff2' );
	}
	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( MITKA_URI . '/assets/fonts/' . $font )
		);
	}
}
add_action( 'wp_head', 'mitka_preload_fonts', 1 );

add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

function mitka_head_meta() {
	if ( has_site_icon() ) {
		return;
	}
	printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( MITKA_URI . '/assets/images/favicon.svg' ) );
	echo '<meta name="theme-color" content="#ffffff">' . "\n";
}
add_action( 'wp_head', 'mitka_head_meta', 2 );
