<?php

defined( 'ABSPATH' ) || exit;

const MITKA_OPTIONS = 'mitka_options';

function mitka_option_fields() {
	return array(
		'phone'        => array(
			'label' => __( 'Phone', 'mitka' ),
			'type'  => 'text',
		),
		'email'        => array(
			'label' => __( 'Email', 'mitka' ),
			'type'  => 'email',
		),
		'address'      => array(
			'label' => __( 'Workshop address', 'mitka' ),
			'type'  => 'text',
		),
		'hours'        => array(
			'label' => __( 'Working hours', 'mitka' ),
			'type'  => 'text',
		),
		'instagram'    => array(
			'label' => __( 'Instagram URL', 'mitka' ),
			'type'  => 'url',
		),
		'facebook'     => array(
			'label' => __( 'Facebook URL', 'mitka' ),
			'type'  => 'url',
		),
		'telegram'     => array(
			'label' => __( 'Telegram URL', 'mitka' ),
			'type'  => 'url',
		),
		'announcement' => array(
			'label' => __( 'Announcement bar text', 'mitka' ),
			'type'  => 'text',
		),
	);
}

function mitka_option( $key ) {
	$options = get_option( MITKA_OPTIONS, array() );
	$value   = $options[ $key ] ?? '';

	if ( $value && function_exists( 'pll__' ) && in_array( $key, mitka_translatable_options(), true ) ) {
		$value = pll__( $value );
	}

	return $value;
}

function mitka_translatable_options() {
	return array( 'address', 'hours', 'announcement' );
}

function mitka_options_page() {
	add_submenu_page(
		'woocommerce',
		__( 'Mitka settings', 'mitka' ),
		__( 'Mitka', 'mitka' ),
		'manage_woocommerce',
		'mitka-settings',
		'mitka_render_options_page'
	);
}
add_action( 'admin_menu', 'mitka_options_page', 60 );

function mitka_register_options() {
	register_setting(
		'mitka_settings',
		MITKA_OPTIONS,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'mitka_sanitize_options',
			'default'           => array(),
		)
	);

	add_settings_section( 'mitka_contacts', __( 'Contacts and header', 'mitka' ), '__return_false', 'mitka-settings' );

	foreach ( mitka_option_fields() as $key => $field ) {
		add_settings_field(
			'mitka_' . $key,
			$field['label'],
			'mitka_render_option_field',
			'mitka-settings',
			'mitka_contacts',
			array(
				'key'       => $key,
				'type'      => $field['type'],
				'label_for' => 'mitka_' . $key,
			)
		);
	}
}
add_action( 'admin_init', 'mitka_register_options' );

function mitka_sanitize_options( $input ) {
	$clean = array();
	foreach ( mitka_option_fields() as $key => $field ) {
		$value         = $input[ $key ] ?? '';
		$clean[ $key ] = match ( $field['type'] ) {
			'email' => sanitize_email( $value ),
			'url'   => esc_url_raw( $value ),
			default => sanitize_text_field( $value ),
		};
	}
	return $clean;
}

function mitka_render_option_field( $args ) {
	$options = get_option( MITKA_OPTIONS, array() );
	printf(
		'<input type="%1$s" id="mitka_%2$s" name="%3$s[%2$s]" value="%4$s" class="regular-text">',
		esc_attr( $args['type'] ),
		esc_attr( $args['key'] ),
		esc_attr( MITKA_OPTIONS ),
		esc_attr( $options[ $args['key'] ] ?? '' )
	);
}

function mitka_render_options_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Mitka settings', 'mitka' ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'mitka_settings' );
			do_settings_sections( 'mitka-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

add_filter( 'option_page_capability_mitka_settings', fn() => 'manage_woocommerce' );

function mitka_register_option_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	$options = get_option( MITKA_OPTIONS, array() );
	foreach ( mitka_translatable_options() as $key ) {
		if ( ! empty( $options[ $key ] ) ) {
			pll_register_string( 'mitka_' . $key, $options[ $key ], 'Mitka' );
		}
	}
}
add_action( 'init', 'mitka_register_option_strings' );
