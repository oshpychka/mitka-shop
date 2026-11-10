<?php

defined( 'ABSPATH' ) || exit;

function mitka_register_taxonomies() {
	register_taxonomy(
		'mitka_maker',
		'product',
		array(
			'labels'            => array(
				'name'          => __( 'Makers', 'mitka' ),
				'singular_name' => __( 'Maker', 'mitka' ),
				'add_new_item'  => __( 'Add maker', 'mitka' ),
				'edit_item'     => __( 'Edit maker', 'mitka' ),
				'search_items'  => __( 'Search makers', 'mitka' ),
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'meta_box_cb'       => 'post_categories_meta_box',
			'rewrite'           => array( 'slug' => 'maker' ),
		)
	);

	register_taxonomy(
		'mitka_occasion',
		'product',
		array(
			'labels'            => array(
				'name'          => __( 'Occasions', 'mitka' ),
				'singular_name' => __( 'Occasion', 'mitka' ),
				'add_new_item'  => __( 'Add occasion', 'mitka' ),
				'edit_item'     => __( 'Edit occasion', 'mitka' ),
				'search_items'  => __( 'Search occasions', 'mitka' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'occasion' ),
		)
	);
}
add_action( 'init', 'mitka_register_taxonomies' );

function mitka_flush_rewrites() {
	mitka_register_taxonomies();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'mitka_flush_rewrites' );
