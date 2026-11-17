<?php
/**
 * Email Header
 *
 * Overridden by Pepiniera Ciumbrud API plugin to reflect the storefront theme:
 * - Top Red Announcement & Phone Bar (#7D0C0C)
 * - Brand Header on warm eggshell (#FAF6EE) with Logo, Title ("Pepiniera Ciumbrud"),
 *   Slogan ("Din 2000 · Satul Rozelor"), and motto
 * - Red Theme Heading Banner (#B21111 -> #7D0C0C)
 *
 * @package WooCommerce\Templates\Emails
 * @version 10.7.0
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$email_improvements_enabled = FeaturesUtil::feature_is_enabled( 'email_improvements' );
$mailer_instance            = class_exists( 'PCS_Mailer' ) ? PCS_Mailer::instance() : null;

$store_name = $store_name ?? ( $mailer_instance ? $mailer_instance->get_default_from_name() : get_bloginfo( 'name', 'display' ) );
if ( empty( $store_name ) || 'WordPress' === $store_name ) {
	$store_name = 'Pepiniera Ciumbrud';
}

$slogan         = $mailer_instance ? $mailer_instance->get_store_slogan() : 'Din 2000 · Satul Rozelor';
$tagline        = $mailer_instance ? $mailer_instance->get_store_tagline() : 'Puieți care prind rădăcini, și rodesc ani la rând';
$store_phone    = $mailer_instance ? $mailer_instance->get_store_phone() : '+40 765 152 832';
$phone_href     = preg_replace( '/\s+/', '', $store_phone );
$storefront_url = $mailer_instance ? $mailer_instance->get_storefront_url() : home_url();

$header_image_url = apply_filters( 'woocommerce_email_header_image_url', $storefront_url );

$img = $mailer_instance ? $mailer_instance->get_email_logo_url() : get_option( 'woocommerce_email_header_image' );
if ( apply_filters( 'woocommerce_is_email_preview', false ) ) {
	$img_transient = get_transient( 'woocommerce_email_header_image' );
	$img           = false !== $img_transient && ! empty( $img_transient ) ? $img_transient : $img;
}

// Determine contextual eyebrow text based on email ID
$email_id     = ( is_object( $email ?? null ) && isset( $email->id ) ) ? (string) $email->id : '';
$eyebrow_map  = [
	'new_order'                 => 'ADMINISTRARE · COMANDĂ NOUĂ',
	'cancelled_order'           => 'ADMINISTRARE · COMANDĂ ANULATĂ',
	'failed_order'              => 'ADMINISTRARE · COMANDĂ EȘUATĂ',
	'customer_processing_order' => 'CONFIRMARE COMANDĂ · PEPINIERA CIUMBRUD',
	'customer_completed_order'  => 'EXPEDIERE COLET · PREDATĂ CURIER',
	'customer_cancelled_order'  => 'ACTUALIZARE STATUS · COMANDĂ ANULATĂ',
	'customer_on_hold_order'    => 'ACTUALIZARE STATUS · ÎN AȘTEPTARE',
	'customer_refunded_order'   => 'ACTUALIZARE STATUS · COMANDĂ RAMBURSATĂ',
	'customer_note'             => 'NOTĂ COMANDĂ · PEPINIERA CIUMBRUD',
	'customer_invoice'          => 'DETALII COMANDĂ · PEPINIERA CIUMBRUD',
	'customer_reset_password'   => 'CONT CLIENT · SECURITATE',
	'customer_new_account'      => 'CONT CLIENT · BUN VENIT',
];
$eyebrow_text = $eyebrow_map[ $email_id ] ?? 'NOTIFICARE COMANDĂ · PEPINIERA CIUMBRUD';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
		<meta content="width=device-width, initial-scale=1.0" name="viewport">
		<title><?php echo esc_html( $store_name ); ?></title>
	</head>
	<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="background-color: #FAF6EE; margin: 0; padding: 0; -webkit-text-size-adjust: 100%;">
		<table width="100%" id="outer_wrapper" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #FAF6EE;">
			<tr>
				<td><!-- Deliberately empty to support consistent sizing and layout across multiple email clients. --></td>
				<td width="780" align="center" valign="top">
					<div id="wrapper" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>" style="margin: 0 auto; padding: 28px 12px; width: 100%; max-width: 780px; box-sizing: border-box;">
						<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="inner_wrapper" role="presentation" style="background-color: #FFFFFF; border-radius: 16px; border: 1px solid #E2DACB; overflow: hidden; box-shadow: 0 12px 32px -12px rgba(178, 17, 17, 0.14);">
							<!-- Top Red Announcement Strip -->
							<tr>
								<td style="background-color: #7D0C0C; color: #FAF6EE; padding: 8px 24px; font-size: 11px; border-bottom: 1px solid rgba(255, 255, 255, 0.12);">
									<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
										<tr>
											<td align="left" valign="middle" style="color: #FAF6EE; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.2px; padding: 0;">
												<span style="color: #F2BDBD;">&#9679;</span> Livrare în toată țara &middot; Ambalare specială pentru rădăcini
											</td>
											<td align="right" valign="middle" style="font-size: 11px; font-weight: 700; white-space: nowrap; padding: 0 0 0 10px;">
												<a href="tel:<?php echo esc_attr( $phone_href ); ?>" style="color: #FFFFFF; text-decoration: none; font-weight: 700;">&#9742; <?php echo esc_html( $store_phone ); ?></a>
											</td>
										</tr>
									</table>
								</td>
							</tr>

							<!-- Brand Identity Header (Logo + Title + Slogan) -->
							<tr>
								<td id="template_header_image" style="background-color: #FAF6EE; padding: 20px 28px; border-bottom: 1px solid #E2DACB; text-align: left;">
									<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse: collapse;">
										<tr>
											<?php if ( $img ) : ?>
												<td width="68" valign="middle" style="padding: 0 16px 0 0; width: 68px; vertical-align: middle;">
													<?php if ( $header_image_url ) : ?>
														<a href="<?php echo esc_url( $header_image_url ); ?>" style="display: inline-block; text-decoration: none;" target="_blank">
															<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $store_name ); ?>" width="64" height="64" style="width: 64px; height: 64px; max-width: 64px; object-fit: contain; display: block; border: 0; margin: 0;" />
														</a>
													<?php else : ?>
														<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $store_name ); ?>" width="64" height="64" style="width: 64px; height: 64px; max-width: 64px; object-fit: contain; display: block; border: 0; margin: 0;" />
													<?php endif; ?>
												</td>
											<?php endif; ?>
											<td valign="middle" style="padding: 0 0 0 16px; border-left: 2px solid rgba(178, 17, 17, 0.22); vertical-align: middle;">
												<div class="email-logo-text" style="margin: 0; font-family: 'Cormorant Garamond', Georgia, 'Times New Roman', serif; font-size: 25px; font-weight: 700; line-height: 1.1; color: #7D0C0C; letter-spacing: 0.3px;">
													<?php if ( $header_image_url ) : ?>
														<a href="<?php echo esc_url( $header_image_url ); ?>" style="color: #7D0C0C; font-weight: 700; text-decoration: none;" target="_blank"><?php echo esc_html( $store_name ); ?></a>
													<?php else : ?>
														<?php echo esc_html( $store_name ); ?>
													<?php endif; ?>
												</div>
												<div style="font-family: 'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 2.4px; color: #B21111; font-weight: 700; margin-top: 5px;">
													<?php echo esc_html( $slogan ); ?>
												</div>
												<div style="font-family: Georgia, 'Times New Roman', serif; font-style: italic; font-size: 12px; color: #5F5A54; margin-top: 3px;">
													<?php echo esc_html( $tagline ); ?>
												</div>
											</td>
										</tr>
									</table>
								</td>
							</tr>

							<tr>
								<td align="center" valign="top">
									<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_container" role="presentation" style="background-color: #FFFFFF; border: 0;">
										<tr>
											<td align="center" valign="top">
												<!-- Header Banner -->
												<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header" role="presentation" style="background-color: #B21111; background-image: linear-gradient(135deg, #B21111 0%, #7D0C0C 100%); color: #FFFFFF; border-bottom: 0; border-radius: 0;">
													<tr>
														<td id="header_wrapper" style="padding: 22px 28px; display: block; text-align: left;">
															<div style="font-family: 'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 2.2px; color: #F2BDBD; font-weight: 600; margin-bottom: 6px;">
																<?php echo esc_html( $eyebrow_text ); ?>
															</div>
															<h1 style="margin: 0; font-family: 'Cormorant Garamond', Georgia, 'Times New Roman', serif; font-size: 25px; font-weight: 700; color: #FFFFFF; line-height: 1.25; letter-spacing: 0.2px; text-align: left;"><?php echo esc_html( $email_heading ); ?></h1>
														</td>
													</tr>
												</table>
												<!-- End Header Banner -->
											</td>
										</tr>
										<tr>
											<td align="center" valign="top">
												<!-- Body -->
												<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_body" role="presentation">
													<tr>
														<td valign="top" id="body_content" style="background-color: #FFFFFF;">
															<!-- Content -->
															<table border="0" cellpadding="20" cellspacing="0" width="100%" role="presentation">
																<tr>
																	<td valign="top" id="body_content_inner_cell" style="padding: 28px 28px 24px;">
																		<div id="body_content_inner" style="color: #2E2C2A; font-size: 15px; line-height: 1.65; text-align: left;">
