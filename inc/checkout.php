<?php

defined( 'ABSPATH' ) || exit;

/**
 * Checkout for Ukraine with Nova Poshta: name, phone, email, city and branch.
 * Company, region, postcode and the second address line are not needed.
 *
 * Address fields are set on the defaults because WooCommerce re-applies
 * labels and order from them when the country is (re)selected.
 */
function mitka_address_fields( $fields ) {
	unset( $fields['company'], $fields['address_2'], $fields['postcode'], $fields['state'] );

	$fields['first_name']['priority'] = 10;
	$fields['last_name']['priority']  = 20;
	$fields['country']['priority']    = 50;
	$fields['country']['class'][]     = 'visually-hidden';

	$fields['city']['priority']    = 60;
	$fields['city']['label']       = __( 'City', 'mitka' );
	$fields['city']['placeholder'] = __( 'For example, Lviv', 'mitka' );
	$fields['city']['class']       = array( 'form-row-first' );

	$fields['address_1']['priority']    = 70;
	$fields['address_1']['label']       = __( 'Nova Poshta branch', 'mitka' );
	$fields['address_1']['placeholder'] = __( 'Branch or parcel locker number', 'mitka' );
	$fields['address_1']['class']       = array( 'form-row-last' );

	return $fields;
}
add_filter( 'woocommerce_default_address_fields', 'mitka_address_fields' );

function mitka_billing_fields( $fields ) {
	$fields['billing_phone']['required'] = true;
	$fields['billing_phone']['priority'] = 30;
	$fields['billing_phone']['class']    = array( 'form-row-first' );
	$fields['billing_email']['priority'] = 40;
	$fields['billing_email']['class']    = array( 'form-row-last' );
	return $fields;
}
add_filter( 'woocommerce_billing_fields', 'mitka_billing_fields' );

function mitka_checkout_locale( $locale ) {
	$locale['UA'] = array(
		'state'     => array(
			'required' => false,
			'hidden'   => true,
		),
		'postcode'  => array(
			'required' => false,
			'hidden'   => true,
		),
		'city'      => array(
			'label'    => __( 'City', 'mitka' ),
			'priority' => 60,
		),
		'address_1' => array(
			'label'       => __( 'Nova Poshta branch', 'mitka' ),
			'placeholder' => __( 'Branch or parcel locker number', 'mitka' ),
			'priority'    => 70,
		),
	);
	return $locale;
}
add_filter( 'woocommerce_get_country_locale', 'mitka_checkout_locale' );

function mitka_phone_digits( $phone ) {
	$digits = preg_replace( '/\D/', '', (string) $phone );
	if ( str_starts_with( $digits, '0' ) ) {
		$digits = '38' . $digits;
	} elseif ( str_starts_with( $digits, '80' ) ) {
		$digits = '3' . $digits;
	}
	return $digits;
}

/**
 * Stores the phone in one format whatever was typed: +380 67 123 45 67.
 */
function mitka_normalize_phone( $phone ) {
	$digits = mitka_phone_digits( $phone );
	if ( ! preg_match( '/^380(\d{2})(\d{3})(\d{2})(\d{2})$/', $digits, $m ) ) {
		return $phone;
	}
	return sprintf( '+380 %s %s %s %s', $m[1], $m[2], $m[3], $m[4] );
}
add_filter( 'woocommerce_process_checkout_field_billing_phone', 'mitka_normalize_phone' );

function mitka_validate_phone( $data, $errors ) {
	if ( ! empty( $data['billing_phone'] ) && ! preg_match( '/^380\d{9}$/', mitka_phone_digits( $data['billing_phone'] ) ) ) {
		$errors->add( 'billing_phone_validation', __( 'Enter the phone as +380 (XX) XXX XX XX.', 'mitka' ), array( 'id' => 'billing_phone' ) );
	}
}
add_action( 'woocommerce_after_checkout_validation', 'mitka_validate_phone', 10, 2 );

// The branch field holds a number, not a street address.
function mitka_branch_field_attributes( $fields ) {
	$fields['address_1']['autocomplete']      = 'off';
	$fields['address_1']['custom_attributes'] = array( 'inputmode' => 'numeric' );
	$fields['city']['autocomplete']           = 'address-level2';
	return $fields;
}
add_filter( 'woocommerce_default_address_fields', 'mitka_branch_field_attributes', 20 );

// "Billing First name is a required field" → "First name is a required field".
function mitka_drop_billing_prefix( $translation, $text, $context, $domain ) {
	if ( 'woocommerce' === $domain && 'checkout-validation' === $context && 'Billing %s' === $text ) {
		return '%s';
	}
	return $translation;
}
add_filter( 'gettext_with_context', 'mitka_drop_billing_prefix', 10, 4 );
