<?php

defined( 'ABSPATH' ) || exit;

function mitka_cleanup_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
}
add_action( 'init', 'mitka_cleanup_head' );

add_filter( 'the_generator', '__return_empty_string' );
add_filter( 'emoji_svg_url', '__return_false' );
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'feed_links_show_comments_feed', '__return_false' );

function mitka_strip_wp_version( $src ) {
	if ( $src && str_contains( $src, 'ver=' . get_bloginfo( 'version' ) ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'style_loader_src', 'mitka_strip_wp_version' );
add_filter( 'script_loader_src', 'mitka_strip_wp_version' );

function mitka_dequeue_front_assets() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
	if ( ! is_cart() && ! is_checkout() && ! is_account_page() ) {
		wp_dequeue_style( 'wc-blocks-style' );
	}

	if ( ! is_user_logged_in() ) {
		wp_dequeue_style( 'dashicons' );
	}
}
add_action( 'wp_enqueue_scripts', 'mitka_dequeue_front_assets', 100 );

function mitka_remove_jquery_migrate( $scripts ) {
	if ( is_admin() || empty( $scripts->registered['jquery'] ) ) {
		return;
	}
	$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
}
add_action( 'wp_default_scripts', 'mitka_remove_jquery_migrate' );

function mitka_heartbeat() {
	if ( ! is_admin() ) {
		wp_deregister_script( 'heartbeat' );
	}
}
add_action( 'init', 'mitka_heartbeat', 1 );
add_filter(
	'heartbeat_settings',
	function ( $settings ) {
		$settings['interval'] = 60;
		return $settings;
	}
);

function mitka_disable_feeds() {
	wp_safe_redirect( home_url( '/' ), 301 );
	exit;
}
add_action( 'do_feed', 'mitka_disable_feeds', 1 );
add_action( 'do_feed_rdf', 'mitka_disable_feeds', 1 );
add_action( 'do_feed_rss', 'mitka_disable_feeds', 1 );
add_action( 'do_feed_rss2', 'mitka_disable_feeds', 1 );
add_action( 'do_feed_atom', 'mitka_disable_feeds', 1 );

function mitka_disable_comments_support() {
	foreach ( get_post_types() as $post_type ) {
		if ( 'product' !== $post_type && post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
}
add_action( 'init', 'mitka_disable_comments_support', 100 );

function mitka_comments_open( $open, $post_id ) {
	return 'product' === get_post_type( $post_id ) ? $open : false;
}
add_filter( 'comments_open', 'mitka_comments_open', 20, 2 );
add_filter( 'pings_open', '__return_false', 20 );

function mitka_remove_comments_admin() {
	remove_menu_page( 'edit-comments.php' );
}
add_action( 'admin_menu', 'mitka_remove_comments_admin' );

function mitka_remove_comments_admin_bar( $bar ) {
	$bar->remove_node( 'comments' );
}
add_action( 'admin_bar_menu', 'mitka_remove_comments_admin_bar', 100 );

function mitka_redirect_archives() {
	if ( is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}

	if ( is_attachment() ) {
		$parent = wp_get_post_parent_id( get_queried_object_id() );
		wp_safe_redirect( $parent ? get_permalink( $parent ) : home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'mitka_redirect_archives' );
