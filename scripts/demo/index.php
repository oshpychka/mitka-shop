<?php
/**
 * Search index for the static demo, one list per language.
 * Run by scripts/export-static.mjs through `wp eval-file`; prints one JSON line.
 */

defined( 'WP_CLI' ) || exit;

$mitka_index = array();

foreach ( array( 'uk', 'en' ) as $mitka_lang ) {
	// Price formatting (currency symbol) follows the current language.
	PLL()->curlang = PLL()->model->get_language( $mitka_lang );

	$mitka_ids = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'lang'           => $mitka_lang,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	$mitka_text = fn( $html ) => trim( html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES, 'UTF-8' ) );

	foreach ( $mitka_ids as $mitka_id ) {
		$mitka_product = wc_get_product( $mitka_id );
		$mitka_item    = array(
			'name'     => $mitka_product->get_name(),
			'url'      => get_permalink( $mitka_id ),
			'image'    => (string) wp_get_attachment_image_url( $mitka_product->get_image_id(), 'mitka-thumb' ),
			'category' => implode( ', ', wp_list_pluck( get_the_terms( $mitka_id, 'product_cat' ) ? get_the_terms( $mitka_id, 'product_cat' ) : array(), 'name' ) ),
		);

		if ( $mitka_product->is_type( 'variable' ) && $mitka_product->get_variation_price( 'min' ) !== $mitka_product->get_variation_price( 'max' ) ) {
			$mitka_item['price'] = $mitka_text( wc_price( $mitka_product->get_variation_price( 'min' ) ) ) . ' – ' . $mitka_text( wc_price( $mitka_product->get_variation_price( 'max' ) ) );
		} else {
			$mitka_item['price'] = $mitka_text( wc_price( $mitka_product->get_price() ) );
			if ( $mitka_product->is_on_sale() ) {
				$mitka_item['old'] = $mitka_text( wc_price( $mitka_product->get_regular_price() ) );
			}
		}
		$mitka_index[ $mitka_lang ][] = $mitka_item;
	}
}

echo 'MITKA_INDEX:' . wp_json_encode( $mitka_index, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n";
