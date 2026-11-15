<?php
/**
 * Customer completed order email (Sent when order transitions to 'shipped' / Predată Curier)
 *
 * Overridden by Pepiniera Ciumbrud API plugin for branded Romanian courier dispatch notification.
 *
 * @package WooCommerce\Templates\Emails
 * @version 10.4.0
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email_improvements_enabled = FeaturesUtil::feature_is_enabled( 'email_improvements' );
$first_name                 = trim( (string) $order->get_billing_first_name() );

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<div class="email-introduction" style="padding-bottom: 20px;">
	<p style="font-size: 16px; color: #2E2C2A; margin: 0 0 14px;">
		<?php if ( '' !== $first_name ) : ?>
			Bună <strong style="color: #7D0C0C;"><?php echo esc_html( $first_name ); ?></strong>,
		<?php else : ?>
			Bună ziua,
		<?php endif; ?>
	</p>
	<p style="font-size: 15px; color: #2E2C2A; margin: 0 0 12px; line-height: 1.65;">
		Vești bune! Comanda ta nr. <strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong> de la <strong>Pepiniera Ciumbrud</strong> a fost pregătită cu grijă (cu rădăcinile hidratate și protejate) și a fost <strong>predată curierului</strong>, fiind acum în drum spre tine.
	</p>
	<p style="font-size: 14px; color: #5F5A54; margin: 0;">
		Mai jos găsești rezumatul produselor expediate și adresa de livrare:
	</p>
</div>

<?php
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

if ( $additional_content ) {
	echo '<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin-top: 8px;"><tr><td class="email-additional-content" style="background-color: #FCF2F2; border: 1px solid #F2BDBD; border-radius: 10px; padding: 14px 18px; color: #7D0C0C; font-size: 13px; text-align: center;">';
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
	echo '</td></tr></table>';
}

do_action( 'woocommerce_email_footer', $email );
