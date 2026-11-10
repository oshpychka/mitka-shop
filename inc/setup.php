<?php

defined( 'ABSPATH' ) || exit;

function mitka_setup() {
	load_theme_textdomain( 'mitka', MITKA_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'responsive-embeds' );

	register_nav_menus(
		array(
			'primary'        => __( 'Primary navigation', 'mitka' ),
			'footer-buyers'  => __( 'Footer: for buyers', 'mitka' ),
			'footer-company' => __( 'Footer: company', 'mitka' ),
		)
	);
}
add_action( 'after_setup_theme', 'mitka_setup' );

function mitka_image_sizes() {
	add_image_size( 'mitka-card', 480, 480, true );
	add_image_size( 'mitka-thumb', 160, 160, true );
}
add_action( 'after_setup_theme', 'mitka_image_sizes' );

function mitka_remove_default_image_sizes( $sizes ) {
	unset( $sizes['medium_large'], $sizes['1536x1536'], $sizes['2048x2048'] );
	return $sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'mitka_remove_default_image_sizes' );
add_filter( 'big_image_size_threshold', fn() => 2000 );
