<?php

defined( 'ABSPATH' ) || exit;

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

function mitka_currency_symbol( $symbol, $currency ) {
	if ( 'UAH' !== $currency ) {
		return $symbol;
	}
	return function_exists( 'pll_current_language' ) && 'en' === pll_current_language() ? 'UAH' : 'грн';
}
add_filter( 'woocommerce_currency_symbol', 'mitka_currency_symbol', 10, 2 );

function mitka_breadcrumb_args( $args ) {
	return array_merge(
		$args,
		array(
			'delimiter'   => '</li><li>',
			'wrap_before' => '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'mitka' ) . '"><ol class="breadcrumbs__list"><li>',
			'wrap_after'  => '</li></ol></nav>',
			'before'      => '',
			'after'       => '',
			'home'        => __( 'Home', 'mitka' ),
		)
	);
}
add_filter( 'woocommerce_breadcrumb_defaults', 'mitka_breadcrumb_args' );

function mitka_sale_percent( $product ) {
	if ( ! $product->is_on_sale() ) {
		return 0;
	}

	if ( $product->is_type( 'variable' ) ) {
		$regular = (float) $product->get_variation_regular_price( 'min' );
		$sale    = (float) $product->get_variation_sale_price( 'min' );
	} else {
		$regular = (float) $product->get_regular_price();
		$sale    = (float) $product->get_sale_price();
	}

	return $regular > 0 ? (int) round( ( $regular - $sale ) / $regular * 100 ) : 0;
}

function mitka_is_engravable( $product ) {
	return 'yes' === $product->get_meta( '_mitka_engraving' );
}

add_filter( 'loop_shop_per_page', fn() => 24 );

function mitka_product_maker( $product ) {
	$makers = get_the_terms( $product->get_id(), 'mitka_maker' );
	return $makers && ! is_wp_error( $makers ) ? $makers[0] : null;
}

/**
 * Picture for a category, occasion or maker tile: the term's own thumbnail,
 * or the photo of its first product that has one.
 */
function mitka_term_image_id( $term ) {
	$thumbnail_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	if ( $thumbnail_id ) {
		return $thumbnail_id;
	}

	$ids = get_posts(
		array(
			'post_type'      => 'product',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'orderby'        => 'menu_order ID',
			'order'          => 'ASC',
			'meta_key'       => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- small catalog.
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- small catalog.
				array(
					'taxonomy' => $term->taxonomy,
					'terms'    => $term->term_id,
				),
			),
		)
	);
	return $ids ? (int) get_post_thumbnail_id( $ids[0] ) : 0;
}

function mitka_top_categories() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'menu_order' => 'ASC',
			'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		)
	);
	return is_wp_error( $terms ) ? array() : $terms;
}

function mitka_woocommerce_image_sizes() {
	add_theme_support(
		'woocommerce',
		array(
			'single_image_width'    => 900,
			'thumbnail_image_width' => 480,
		)
	);
}
add_action( 'after_setup_theme', 'mitka_woocommerce_image_sizes', 20 );

add_filter(
	'woocommerce_output_related_products_args',
	fn( $args ) => array_merge(
		$args,
		array(
			'posts_per_page' => 4,
			'columns'        => 4,
			'orderby'        => 'ID',
			'order'          => 'asc',
		)
	)
);
// The same related products on every visit (and in every static export).
add_filter( 'woocommerce_product_related_posts_shuffle', '__return_false' );
add_filter( 'woocommerce_product_description_heading', '__return_empty_string' );

// Ukrainian quotes in "Search results: “…”" (WooCommerce writes them as entities).
function mitka_ukrainian_quotes( $text ) {
	if ( 'uk' !== mitka_lang() ) {
		return str_replace( array( '&ldquo;', '&rdquo;' ), array( '“', '”' ), $text );
	}
	return str_replace( array( '&ldquo;', '&rdquo;', '“', '”' ), array( '«', '»', '«', '»' ), $text );
}
add_filter( 'woocommerce_page_title', 'mitka_ukrainian_quotes' );
add_filter(
	'woocommerce_get_breadcrumb',
	fn( $crumbs ) => array_map( fn( $crumb ) => array( mitka_ukrainian_quotes( $crumb[0] ), $crumb[1] ), $crumbs )
);

// WooCommerce makes quantity field ids with uniqid(); a stable id keeps static exports identical.
function mitka_quantity_input_id( $args, $product ) {
	$name             = (string) ( $args['input_name'] ?? 'quantity' );
	$args['input_id'] = 'quantity_' . ( $product ? $product->get_id() : 0 ) . '_' . substr( md5( $name ), 0, 6 );
	return $args;
}
add_filter( 'woocommerce_quantity_input_args', 'mitka_quantity_input_id', 10, 2 );
