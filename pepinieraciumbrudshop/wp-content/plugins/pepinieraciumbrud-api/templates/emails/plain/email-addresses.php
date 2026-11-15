<?php
/**
 * Email Addresses (plain)
 *
 * Overridden by Pepiniera Ciumbrud API plugin:
 * - Provides clear headings and descriptions for both Billing and Shipping addresses
 * - Adds Romanian captions before each field: Nume, Adresă, Cod poștal, Telefon, Email
 *
 * @package WooCommerce\Templates\Emails\Plain
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

$resolve_state_name = static function ( string $country, string $state ): string {
	$state = trim( $state );
	if ( '' === $state ) {
		return '';
	}
	if ( function_exists( 'WC' ) && WC()->countries ) {
		$states = WC()->countries->get_states( $country ?: 'RO' );
		if ( is_array( $states ) && isset( $states[ $state ] ) ) {
			return (string) $states[ $state ];
		}
	}
	return $state;
};

// Billing
$billing_name    = trim( $order->get_formatted_billing_full_name() );
$billing_company = trim( (string) $order->get_billing_company() );
if ( '' !== $billing_company ) {
	$billing_name = $billing_name ? "{$billing_name} ({$billing_company})" : $billing_company;
}
$billing_street   = trim( implode( ', ', array_filter( array_map( 'trim', [ (string) $order->get_billing_address_1(), (string) $order->get_billing_address_2() ] ) ) ) );
$billing_city     = trim( (string) $order->get_billing_city() );
$billing_country  = trim( (string) $order->get_billing_country() ) ?: 'RO';
$billing_state    = $resolve_state_name( $billing_country, (string) $order->get_billing_state() );
$billing_postcode = trim( (string) $order->get_billing_postcode() );
$billing_phone    = trim( (string) $order->get_billing_phone() );
$billing_email    = trim( (string) $order->get_billing_email() );

$billing_full_address = implode( ', ', array_filter( [
	$billing_street,
	$billing_city ? "Loc. {$billing_city}" : '',
	$billing_state ? "Jud. {$billing_state}" : '',
] ) );

// Shipping
$shipping_name    = trim( $order->get_formatted_shipping_full_name() );
$shipping_company = trim( (string) $order->get_shipping_company() );
if ( '' !== $shipping_company ) {
	$shipping_name = $shipping_name ? "{$shipping_name} ({$shipping_company})" : $shipping_company;
}
if ( '' === $shipping_name ) {
	$shipping_name = $billing_name;
}
$shipping_street = trim( implode( ', ', array_filter( array_map( 'trim', [ (string) $order->get_shipping_address_1(), (string) $order->get_shipping_address_2() ] ) ) ) ) ?: $billing_street;
$shipping_city    = trim( (string) $order->get_shipping_city() ) ?: $billing_city;
$shipping_country = trim( (string) $order->get_shipping_country() ) ?: $billing_country;
$shipping_state   = $resolve_state_name( $shipping_country, (string) ( $order->get_shipping_state() ?: $order->get_billing_state() ) );
$shipping_postcode = trim( (string) $order->get_shipping_postcode() ) ?: $billing_postcode;
$shipping_phone    = trim( (string) $order->get_shipping_phone() ) ?: $billing_phone;
$shipping_email    = $billing_email;

$shipping_full_address = implode( ', ', array_filter( [
	$shipping_street,
	$shipping_city ? "Loc. {$shipping_city}" : '',
	$shipping_state ? "Jud. {$shipping_state}" : '',
] ) );

echo "\nADRESĂ DE FACTURARE (DATE CLIENT & FACTURĂ)\n";
echo "Datele cumpărătorului utilizate pentru emiterea facturii și confirmarea comenzii:\n";
echo "Nume: " . ( $billing_name ?: '-' ) . "\n";
echo "Adresă: " . ( $billing_full_address ?: '-' ) . "\n";
echo "Cod poștal: " . ( $billing_postcode ?: '-' ) . "\n";
echo "Telefon: " . ( $billing_phone ?: '-' ) . "\n";
echo "Email: " . ( $billing_email ?: '-' ) . "\n";

do_action( 'woocommerce_email_customer_address_section', 'billing', $order, $sent_to_admin, true );

echo "\nADRESĂ DE LIVRARE (DESTINAȚIE COLET / CURIER)\n";
echo "Adresa la care curierul va livra coletul cu produsele comandate:\n";
echo "Nume: " . ( $shipping_name ?: '-' ) . "\n";
echo "Adresă: " . ( $shipping_full_address ?: '-' ) . "\n";
echo "Cod poștal: " . ( $shipping_postcode ?: '-' ) . "\n";
echo "Telefon: " . ( $shipping_phone ?: '-' ) . "\n";
echo "Email: " . ( $shipping_email ?: '-' ) . "\n";

do_action( 'woocommerce_email_customer_address_section', 'shipping', $order, $sent_to_admin, true );
