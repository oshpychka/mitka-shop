<?php
/**
 * Two languages through Polylang (free). Ukrainian is the default; every
 * English post and term is a linked translation (seeded with an "-en" slug).
 * Without Polylang the helpers fall back to plain WordPress behaviour.
 */

defined( 'ABSPATH' ) || exit;

function mitka_lang() {
	return function_exists( 'pll_current_language' ) ? ( pll_current_language() ? pll_current_language() : 'uk' ) : 'uk';
}

/**
 * A term in the current language, looked up by its Ukrainian slug.
 */
function mitka_term( $slug, $taxonomy ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'slug'       => $slug,
			'hide_empty' => false,
			'lang'       => '',
		)
	);
	if ( is_wp_error( $terms ) || ! $terms ) {
		return null;
	}
	if ( ! function_exists( 'pll_get_term' ) ) {
		return $terms[0];
	}
	$translated = pll_get_term( $terms[0]->term_id );
	return $translated ? get_term( $translated, $taxonomy ) : $terms[0];
}

/**
 * Catalog link in the current language. The shop page itself is not
 * translated; the product archive is, through Polylang's language prefix.
 */
function mitka_shop_url() {
	return get_post_type_archive_link( 'product' );
}

/**
 * Cart, checkout, account, terms and privacy pages in the visitor's language,
 * so WooCommerce links, redirects and is_cart()/is_checkout() all follow it.
 */
function mitka_translated_page_id( $id ) {
	if ( is_admin() || ! $id || ! function_exists( 'pll_get_post' ) || ! did_action( 'pll_language_defined' ) ) {
		return $id;
	}
	$translated = pll_get_post( (int) $id );
	return $translated ? $translated : $id;
}
foreach ( array( 'cart', 'checkout', 'myaccount', 'terms' ) as $mitka_page ) {
	add_filter( "option_woocommerce_{$mitka_page}_page_id", 'mitka_translated_page_id' );
}
add_filter( 'option_wp_page_for_privacy_policy', 'mitka_translated_page_id' );

// The shop page title ("Каталог") is Ukrainian; name the catalog per language.
function mitka_shop_title( $title ) {
	return is_shop() && ! is_search() ? __( 'Catalog', 'mitka' ) : $title;
}
add_filter( 'woocommerce_page_title', 'mitka_shop_title', 5 );

function mitka_breadcrumb_shop( $crumbs ) {
	$shop_title = get_the_title( (int) get_option( 'woocommerce_shop_page_id' ) );
	foreach ( $crumbs as &$crumb ) {
		if ( $crumb[0] === $shop_title ) {
			$crumb = array( __( 'Catalog', 'mitka' ), mitka_shop_url() );
		}
	}
	return $crumbs;
}
add_filter( 'woocommerce_get_breadcrumb', 'mitka_breadcrumb_shop' );

/**
 * Before first paint: pages show the language picker until a choice is saved.
 * Inline so there is no flash of the page before the overlay.
 */
function mitka_language_choice_flag() {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return;
	}
	echo "<script>try{if(!localStorage.getItem('mitka-lang'))document.documentElement.classList.add('needs-lang')}catch(e){}</script>\n";
}
add_action( 'wp_head', 'mitka_language_choice_flag', 0 );

/**
 * WooCommerce's background requests (checkout refresh, place order) go to
 * "/?wc-ajax=…", which Polylang reads as the default language. Keep the
 * visitor's language prefix so those parts render in English too.
 */
function mitka_ajax_endpoint_language( $url, $request ) {
	if ( ! function_exists( 'pll_home_url' ) || 'uk' === mitka_lang() ) {
		return $url;
	}
	return add_query_arg( 'wc-ajax', $request, pll_home_url() );
}
add_filter( 'woocommerce_ajax_get_endpoint', 'mitka_ajax_endpoint_language', 10, 2 );
