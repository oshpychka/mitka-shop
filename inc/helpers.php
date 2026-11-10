<?php

defined( 'ABSPATH' ) || exit;

function mitka_icon( $name, $modifier = '' ) {
	return sprintf(
		'<svg class="icon %1$s" aria-hidden="true" focusable="false"><use href="%2$s#%3$s"></use></svg>',
		esc_attr( $modifier ),
		esc_url( MITKA_URI . '/assets/images/icons.svg' ),
		esc_attr( $name )
	);
}

function mitka_the_icon( $name, $modifier = '' ) {
	echo wp_kses( mitka_icon( $name, $modifier ), mitka_svg_kses() );
}

function mitka_svg_kses() {
	return array(
		'svg' => array(
			'class'       => true,
			'aria-hidden' => true,
			'focusable'   => true,
		),
		'use' => array( 'href' => true ),
	);
}

function mitka_phone_href( $phone ) {
	return 'tel:' . preg_replace( '/[^\d+]/', '', $phone );
}

/**
 * antispambot() picks random encodings on every render, so static exports
 * differed on every page. Seed it from the address to keep output stable.
 */
function mitka_antispambot( $email ) {
	mt_srand( crc32( $email ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rand_seeding_mt_srand -- deterministic output on purpose.
	$encoded = antispambot( $email );
	mt_srand(); // phpcs:ignore WordPress.WP.AlternativeFunctions.rand_seeding_mt_srand -- restore random seeding.
	return $encoded;
}
