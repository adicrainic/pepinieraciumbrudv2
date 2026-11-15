<?php
/**
 * Email Addresses
 *
 * Overridden by Pepiniera Ciumbrud API plugin:
 * - Provides clear headings and descriptions for both Billing and Shipping addresses
 * - Adds Romanian captions before each field: Nume, Adresă, Cod poștal, Telefon, Email
 * - Styled in the Pepiniera Ciumbrud Red & Warm Cream theme
 *
 * @package WooCommerce\Templates\Emails
 * @version 10.6.0
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email_improvements_enabled = FeaturesUtil::feature_is_enabled( 'email_improvements' );

// Helper to resolve full state/county name from WooCommerce country states
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

// Build Billing Address fields
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

$billing_address_parts = array_filter( [
	$billing_street,
	$billing_city ? "Loc. {$billing_city}" : '',
	$billing_state ? "Jud. {$billing_state}" : '',
] );
$billing_full_address = implode( ', ', $billing_address_parts );

// Build Shipping Address fields (falling back to billing if shipping field is empty)
$shipping_name    = trim( $order->get_formatted_shipping_full_name() );
$shipping_company = trim( (string) $order->get_shipping_company() );
if ( '' !== $shipping_company ) {
	$shipping_name = $shipping_name ? "{$shipping_name} ({$shipping_company})" : $shipping_company;
}
if ( '' === $shipping_name ) {
	$shipping_name = $billing_name;
}

$shipping_street = trim( implode( ', ', array_filter( array_map( 'trim', [ (string) $order->get_shipping_address_1(), (string) $order->get_shipping_address_2() ] ) ) ) );
if ( '' === $shipping_street ) {
	$shipping_street = $billing_street;
}
$shipping_city     = trim( (string) $order->get_shipping_city() ) ?: $billing_city;
$shipping_country  = trim( (string) $order->get_shipping_country() ) ?: $billing_country;
$shipping_state    = $resolve_state_name( $shipping_country, (string) ( $order->get_shipping_state() ?: $order->get_billing_state() ) );
$shipping_postcode = trim( (string) $order->get_shipping_postcode() ) ?: $billing_postcode;
$shipping_phone    = trim( (string) $order->get_shipping_phone() ) ?: $billing_phone;
$shipping_email    = $billing_email;

$shipping_address_parts = array_filter( [
	$shipping_street,
	$shipping_city ? "Loc. {$shipping_city}" : '',
	$shipping_state ? "Jud. {$shipping_state}" : '',
] );
$shipping_full_address = implode( ', ', $shipping_address_parts );
?>
<table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 20px; padding: 0;" border="0" role="presentation">
	<tr>
		<td class="font-family text-align-left" style="border: 0; padding: 0 8px 0 0; vertical-align: top;" valign="top" width="50%">
			<div style="background-color: #FAF6EE; border: 1px solid #E2DACB; border-top: 3px solid #B21111; border-radius: 10px; padding: 14px 16px;">
				<b class="address-title" style="display: block; font-family: 'Cormorant Garamond', Georgia, 'Times New Roman', serif; font-size: 16px; font-weight: 700; color: #7D0C0C; margin-bottom: 4px;">Adresă de facturare (Date Client &amp; Factură)</b>
				<p style="margin: 0 0 12px 0; font-size: 11px; color: #5F5A54; line-height: 1.4;">Datele cumpărătorului utilizate pentru emiterea facturii și confirmarea comenzii:</p>

				<address class="address" style="font-style: normal; font-size: 13px; line-height: 1.65; color: #2E2C2A; padding: 0; border: 0; word-break: break-word;">
					<div style="margin-bottom: 4px;"><strong style="color: #7D0C0C;">Nume:</strong> <?php echo esc_html( $billing_name ?: '-' ); ?></div>
					<div style="margin-bottom: 4px;"><strong style="color: #7D0C0C;">Adresă:</strong> <?php echo esc_html( $billing_full_address ?: '-' ); ?></div>
					<div style="margin-bottom: 4px;"><strong style="color: #7D0C0C;">Cod poștal:</strong> <?php echo esc_html( $billing_postcode ?: '-' ); ?></div>
					<div style="margin-bottom: 4px;"><strong style="color: #7D0C0C;">Telefon:</strong> <?php echo $billing_phone ? wp_kses_post( wc_make_phone_clickable( $billing_phone ) ) : '-'; ?></div>
					<div style="margin-bottom: 0;"><strong style="color: #7D0C0C;">Email:</strong> <?php echo $billing_email ? '<a href="mailto:' . esc_attr( $billing_email ) . '" style="color: #B21111; text-decoration: underline;">' . esc_html( $billing_email ) . '</a>' : '-'; ?></div>
					<?php do_action( 'woocommerce_email_customer_address_section', 'billing', $order, $sent_to_admin, false ); ?>
				</address>
			</div>
		</td>
		<td class="font-family text-align-left" style="border: 0; padding: 0 0 0 8px; vertical-align: top;" valign="top" width="50%">
			<div style="background-color: #FAF6EE; border: 1px solid #E2DACB; border-top: 3px solid #B21111; border-radius: 10px; padding: 14px 16px;">
				<b class="address-title" style="display: block; font-family: 'Cormorant Garamond', Georgia, 'Times New Roman', serif; font-size: 16px; font-weight: 700; color: #7D0C0C; margin-bottom: 4px;">Adresă de livrare (Destinație Colet / Curier)</b>
				<p style="margin: 0 0 12px 0; font-size: 11px; color: #5F5A54; line-height: 1.4;">Adresa la care curierul va livra coletul cu produsele comandate:</p>

				<address class="address" style="font-style: normal; font-size: 13px; line-height: 1.65; color: #2E2C2A; padding: 0; border: 0; word-break: break-word;">
					<div style="margin-bottom: 4px;"><strong style="color: #7D0C0C;">Nume:</strong> <?php echo esc_html( $shipping_name ?: '-' ); ?></div>
					<div style="margin-bottom: 4px;"><strong style="color: #7D0C0C;">Adresă:</strong> <?php echo esc_html( $shipping_full_address ?: '-' ); ?></div>
					<div style="margin-bottom: 4px;"><strong style="color: #7D0C0C;">Cod poștal:</strong> <?php echo esc_html( $shipping_postcode ?: '-' ); ?></div>
					<div style="margin-bottom: 4px;"><strong style="color: #7D0C0C;">Telefon:</strong> <?php echo $shipping_phone ? wp_kses_post( wc_make_phone_clickable( $shipping_phone ) ) : '-'; ?></div>
					<div style="margin-bottom: 0;"><strong style="color: #7D0C0C;">Email:</strong> <?php echo $shipping_email ? '<a href="mailto:' . esc_attr( $shipping_email ) . '" style="color: #B21111; text-decoration: underline;">' . esc_html( $shipping_email ) . '</a>' : '-'; ?></div>
					<?php do_action( 'woocommerce_email_customer_address_section', 'shipping', $order, $sent_to_admin, false ); ?>
				</address>
			</div>
		</td>
	</tr>
</table>
