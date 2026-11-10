<?php

defined( 'ABSPATH' ) || exit;

function mitka_catalog_tree() {
	$cache_key = 'mitka_catalog_tree_' . ( function_exists( 'pll_current_language' ) ? pll_current_language() : 'uk' );
	$tree      = get_transient( $cache_key );

	if ( false !== $tree ) {
		return $tree;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'menu_order' => 'ASC',
			'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		)
	);

	$tree = array();
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			if ( 0 === $term->parent ) {
				$tree[ $term->term_id ] = array(
					'name'     => $term->name,
					'url'      => get_term_link( $term ),
					'children' => array(),
				);
			}
		}
		foreach ( $terms as $term ) {
			if ( isset( $tree[ $term->parent ] ) ) {
				$tree[ $term->parent ]['children'][] = array(
					'name' => $term->name,
					'url'  => get_term_link( $term ),
				);
			}
		}
	}

	set_transient( $cache_key, $tree, DAY_IN_SECONDS );
	return $tree;
}

function mitka_flush_catalog_tree() {
	foreach ( array( 'uk', 'en' ) as $lang ) {
		delete_transient( 'mitka_catalog_tree_' . $lang );
	}
}
add_action( 'edited_product_cat', 'mitka_flush_catalog_tree' );
add_action( 'created_product_cat', 'mitka_flush_catalog_tree' );
add_action( 'delete_product_cat', 'mitka_flush_catalog_tree' );

// The mobile drawer lists categories already; skip category links in its copy of the primary menu.
function mitka_mobile_menu_dedupe( $items, $args ) {
	if ( 'mobile-menu__links' !== ( $args->menu_class ?? '' ) ) {
		return $items;
	}
	return array_filter( $items, fn( $item ) => 'product_cat' !== $item->object );
}
add_filter( 'wp_nav_menu_objects', 'mitka_mobile_menu_dedupe', 10, 2 );
