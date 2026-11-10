<?php

defined( 'ABSPATH' ) || exit;

const MITKA_ENGRAVING_LINES      = 2;
const MITKA_ENGRAVING_LINE_CHARS = 30;

/**
 * Categories big enough for a star map next to the text.
 */
const MITKA_STAR_MAP_CATEGORIES = array( 'boards', 'decor', 'office', 'drinkware', 'boxes' );

function mitka_engraving_fonts() {
	return array(
		'classic' => __( 'Classic', 'mitka' ),
		'script'  => __( 'Handwritten', 'mitka' ),
		'modern'  => __( 'Modern', 'mitka' ),
	);
}

/**
 * Cities for the star map: name, latitude, longitude.
 */
function mitka_star_cities() {
	return array(
		'kyiv'            => array( __( 'Kyiv', 'mitka' ), 50.45, 30.52 ),
		'lviv'            => array( __( 'Lviv', 'mitka' ), 49.84, 24.03 ),
		'kharkiv'         => array( __( 'Kharkiv', 'mitka' ), 49.99, 36.23 ),
		'odesa'           => array( __( 'Odesa', 'mitka' ), 46.48, 30.72 ),
		'dnipro'          => array( __( 'Dnipro', 'mitka' ), 48.46, 35.05 ),
		'zaporizhzhia'    => array( __( 'Zaporizhzhia', 'mitka' ), 47.84, 35.14 ),
		'vinnytsia'       => array( __( 'Vinnytsia', 'mitka' ), 49.23, 28.47 ),
		'poltava'         => array( __( 'Poltava', 'mitka' ), 49.59, 34.55 ),
		'chernihiv'       => array( __( 'Chernihiv', 'mitka' ), 51.49, 31.29 ),
		'cherkasy'        => array( __( 'Cherkasy', 'mitka' ), 49.44, 32.06 ),
		'zhytomyr'        => array( __( 'Zhytomyr', 'mitka' ), 50.25, 28.66 ),
		'sumy'            => array( __( 'Sumy', 'mitka' ), 50.91, 34.80 ),
		'khmelnytskyi'    => array( __( 'Khmelnytskyi', 'mitka' ), 49.42, 26.99 ),
		'rivne'           => array( __( 'Rivne', 'mitka' ), 50.62, 26.25 ),
		'lutsk'           => array( __( 'Lutsk', 'mitka' ), 50.75, 25.33 ),
		'ternopil'        => array( __( 'Ternopil', 'mitka' ), 49.55, 25.59 ),
		'ivano-frankivsk' => array( __( 'Ivano-Frankivsk', 'mitka' ), 48.92, 24.71 ),
		'uzhhorod'        => array( __( 'Uzhhorod', 'mitka' ), 48.62, 22.29 ),
		'chernivtsi'      => array( __( 'Chernivtsi', 'mitka' ), 48.29, 25.94 ),
		'kropyvnytskyi'   => array( __( 'Kropyvnytskyi', 'mitka' ), 48.51, 32.26 ),
		'mykolaiv'        => array( __( 'Mykolaiv', 'mitka' ), 46.98, 31.99 ),
		'kherson'         => array( __( 'Kherson', 'mitka' ), 46.64, 32.61 ),
		'kryvyi-rih'      => array( __( 'Kryvyi Rih', 'mitka' ), 47.91, 33.39 ),
		'bila-tserkva'    => array( __( 'Bila Tserkva', 'mitka' ), 49.80, 30.12 ),
	);
}

function mitka_supports_star_map( $product ) {
	$slugs = array_merge( MITKA_STAR_MAP_CATEGORIES, array_map( fn( $slug ) => $slug . '-en', MITKA_STAR_MAP_CATEGORIES ) );
	return has_term( $slugs, 'product_cat', $product->get_id() );
}

/**
 * How a laser mark looks on the product's material, used by the live preview.
 */
function mitka_engraving_tone( $product ) {
	$tones = array(
		'leather' => 'leather',
		'steel'   => 'metal',
		'glass'   => 'glass',
		'acrylic' => 'glass',
		'ceramic' => 'ceramic',
	);
	foreach ( wc_get_product_terms( $product->get_id(), 'pa_material', array( 'fields' => 'slugs' ) ) as $material ) {
		$material = preg_replace( '/-en$/', '', $material );
		if ( isset( $tones[ $material ] ) ) {
			return $tones[ $material ];
		}
	}
	return 'wood';
}

function mitka_engraving_fields() {
	global $product;
	if ( ! $product || ! mitka_is_engravable( $product ) ) {
		return;
	}
	get_template_part( 'template-parts/product/engraving-fields' );
}
add_action( 'woocommerce_before_add_to_cart_button', 'mitka_engraving_fields' );

/**
 * Normalises sanitized engraving input: trims each line, drops empty ones.
 */
function mitka_engraving_clean( $text ) {
	$lines = preg_split( '/\R/u', $text );
	$lines = array_values( array_filter( array_map( 'trim', $lines ), 'strlen' ) );
	return implode( "\n", $lines );
}

/**
 * Reads the engraving from an add-to-cart request.
 *
 * @return array|WP_Error|null Engraving data, an error to show, or null when nothing was asked for.
 */
function mitka_engraving_from_request( $product ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce verifies add-to-cart requests.
	$text = isset( $_POST['mitka_engraving'] ) ? mitka_engraving_clean( sanitize_textarea_field( wp_unslash( $_POST['mitka_engraving'] ) ) ) : '';
	$font = isset( $_POST['mitka_engraving_font'] ) ? sanitize_key( $_POST['mitka_engraving_font'] ) : '';
	$mode = isset( $_POST['mitka_engraving_mode'] ) ? sanitize_key( $_POST['mitka_engraving_mode'] ) : 'text';
	$date = isset( $_POST['mitka_stars_date'] ) ? sanitize_text_field( wp_unslash( $_POST['mitka_stars_date'] ) ) : '';
	$city = isset( $_POST['mitka_stars_city'] ) ? sanitize_key( $_POST['mitka_stars_city'] ) : '';
	// phpcs:enable

	$stars = 'stars' === $mode && $product && mitka_supports_star_map( $product );
	if ( '' === $text && ! $stars ) {
		return null;
	}
	if ( ! $product || ! mitka_is_engravable( $product ) ) {
		return new WP_Error( 'mitka_engraving', __( 'This item cannot be engraved.', 'mitka' ) );
	}

	$lines = '' === $text ? array() : explode( "\n", $text );
	if ( count( $lines ) > MITKA_ENGRAVING_LINES ) {
		/* translators: %d: maximum number of lines. */
		return new WP_Error( 'mitka_engraving', sprintf( __( 'Engraving fits at most %d lines.', 'mitka' ), MITKA_ENGRAVING_LINES ) );
	}
	foreach ( $lines as $line ) {
		if ( mb_strlen( $line ) > MITKA_ENGRAVING_LINE_CHARS ) {
			/* translators: %d: maximum characters per line. */
			return new WP_Error( 'mitka_engraving', sprintf( __( 'Each engraving line fits at most %d characters.', 'mitka' ), MITKA_ENGRAVING_LINE_CHARS ) );
		}
	}

	$engraving = array(
		'mode' => $stars ? 'stars' : 'text',
		'text' => $text,
		'font' => array_key_exists( $font, mitka_engraving_fonts() ) ? $font : 'classic',
	);

	if ( $stars ) {
		$day    = DateTimeImmutable::createFromFormat( '!Y-m-d', $date, wp_timezone() );
		$latest = new DateTimeImmutable( '+1 year', wp_timezone() );
		if ( ! $day || $day->format( 'Y-m-d' ) !== $date || $day->format( 'Y' ) < 1900 || $day > $latest ) {
			return new WP_Error( 'mitka_engraving', __( 'Choose the date for the star map.', 'mitka' ) );
		}
		if ( ! array_key_exists( $city, mitka_star_cities() ) ) {
			return new WP_Error( 'mitka_engraving', __( 'Choose the city for the star map.', 'mitka' ) );
		}
		$engraving['date'] = $date;
		$engraving['city'] = $city;
	}

	return $engraving;
}

function mitka_engraving_validate( $passed, $product_id ) {
	$engraving = mitka_engraving_from_request( wc_get_product( $product_id ) );
	if ( is_wp_error( $engraving ) ) {
		wc_add_notice( $engraving->get_error_message(), 'error' );
		return false;
	}
	return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'mitka_engraving_validate', 10, 2 );

function mitka_engraving_cart_item_data( $data, $product_id ) {
	$engraving = mitka_engraving_from_request( wc_get_product( $product_id ) );
	if ( is_array( $engraving ) ) {
		$data['mitka_engraving'] = $engraving;
	}
	return $data;
}
add_filter( 'woocommerce_add_cart_item_data', 'mitka_engraving_cart_item_data', 10, 2 );

function mitka_engraving_item_rows( $engraving ) {
	$rows = array();
	if ( 'stars' === ( $engraving['mode'] ?? 'text' ) ) {
		$rows[] = array(
			'key'   => __( 'Star map', 'mitka' ),
			'value' => sprintf(
				'%s, %s',
				wp_date( 'd.m.Y', strtotime( $engraving['date'] . ' 12:00' ) ),
				mitka_star_cities()[ $engraving['city'] ][0]
			),
		);
	}
	if ( '' !== $engraving['text'] ) {
		$rows[] = array(
			'key'   => __( 'Engraving', 'mitka' ),
			'value' => $engraving['text'],
		);
		$rows[] = array(
			'key'   => __( 'Font', 'mitka' ),
			'value' => mitka_engraving_fonts()[ $engraving['font'] ],
		);
	}
	return $rows;
}

function mitka_engraving_item_data( $item_data, $cart_item ) {
	if ( empty( $cart_item['mitka_engraving'] ) ) {
		return $item_data;
	}
	return array_merge( $item_data, mitka_engraving_item_rows( $cart_item['mitka_engraving'] ) );
}
add_filter( 'woocommerce_get_item_data', 'mitka_engraving_item_data', 10, 2 );

function mitka_engraving_order_item( $item, $cart_item_key, $values ) {
	if ( empty( $values['mitka_engraving'] ) ) {
		return;
	}
	foreach ( mitka_engraving_item_rows( $values['mitka_engraving'] ) as $row ) {
		$item->add_meta_data( $row['key'], $row['value'] );
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'mitka_engraving_order_item', 10, 3 );
