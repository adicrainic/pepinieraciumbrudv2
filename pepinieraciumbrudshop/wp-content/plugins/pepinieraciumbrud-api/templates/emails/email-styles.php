<?php
/**
 * Email Styles
 *
 * Overridden by Pepiniera Ciumbrud API plugin to apply the storefront theme:
 * - Warm eggshell outer background (#FAF6EE)
 * - Rose Red primary accent (#B21111) & Deep Burgundy (#7D0C0C)
 * - Warm border (#E2DACB) & charcoal typography (#2E2C2A)
 *
 * @package WooCommerce\Templates\Emails
 * @version 11.0.0
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email_improvements_enabled = FeaturesUtil::feature_is_enabled( 'email_improvements' );
$block_email_editor_enabled = FeaturesUtil::feature_is_enabled( 'block_email_editor' );

// Pepiniera Ciumbrud Theme Palette
$bg          = '#FAF6EE';
$body        = '#FFFFFF';
$base        = '#B21111';
$base_dark   = '#7D0C0C';
$text        = '#2E2C2A';
$footer_text = '#5F5A54';
$border      = '#E2DACB';
$border_soft = '#EDE6D9';
$rose_50     = '#FCF2F2';
$rose_200    = '#F2BDBD';

$safe_font_family   = "'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif";
$serif_font_family  = "'Cormorant Garamond', Georgia, 'Times New Roman', serif";
?>
body {
	background-color: <?php echo esc_attr( $bg ); ?>;
	padding: 0;
	margin: 0;
	text-align: center;
	font-family: <?php echo $safe_font_family; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
	color: <?php echo esc_attr( $text ); ?>;
}

#outer_wrapper {
	background-color: <?php echo esc_attr( $bg ); ?>;
}

#wrapper {
	margin: 0 auto;
	padding: 28px 12px;
	-webkit-text-size-adjust: none !important;
	width: 100%;
	max-width: 620px;
	box-sizing: border-box;
}

#inner_wrapper {
	background-color: <?php echo esc_attr( $body ); ?>;
	border: 1px solid <?php echo esc_attr( $border ); ?>;
	border-radius: 16px !important;
	overflow: hidden;
	box-shadow: 0 12px 32px -12px rgba(178, 17, 17, 0.14) !important;
}

#template_container {
	box-shadow: none !important;
	background-color: <?php echo esc_attr( $body ); ?>;
	border: 0;
	border-radius: 0 !important;
}

#template_header {
	background-color: <?php echo esc_attr( $base ); ?>;
	background-image: linear-gradient(135deg, <?php echo esc_attr( $base ); ?> 0%, <?php echo esc_attr( $base_dark ); ?> 100%);
	border-radius: 0 !important;
	color: #FFFFFF;
	border-bottom: 0;
	font-weight: bold;
	line-height: 125%;
	vertical-align: middle;
	font-family: <?php echo $serif_font_family; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
}

#template_header h1,
#template_header h1 a {
	color: #FFFFFF !important;
	background-color: transparent;
	font-family: <?php echo $serif_font_family; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
}

#template_header_image {
	background-color: <?php echo esc_attr( $bg ); ?>;
	padding: 20px 28px;
	border-bottom: 1px solid <?php echo esc_attr( $border ); ?>;
}

#template_header_image img {
	width: 64px;
	height: 64px;
	object-fit: contain;
	margin: 0;
}

.email-logo-text {
	color: <?php echo esc_attr( $base_dark ); ?>;
	font-family: <?php echo $serif_font_family; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
	font-size: 25px;
	font-weight: 700;
}

.email-introduction {
	padding-bottom: 20px;
}

.email-order-item-meta {
	color: <?php echo esc_attr( $footer_text ); ?>;
	font-size: 13px;
	line-height: 140%;
}

#body_content {
	background-color: <?php echo esc_attr( $body ); ?>;
}

#body_content table td {
	padding: 28px 28px 24px;
}

#body_content table td td {
	padding: 10px 12px;
}

#body_content table td th {
	padding: 10px 12px;
}

#body_content table .email-order-details td,
#body_content table .email-order-details th {
	padding: 10px 14px;
}

#body_content td ul.wc-item-meta {
	font-size: small;
	margin: 0.5em 0 0;
	padding: 0;
	list-style: none;
}

#body_content td ul.wc-item-meta li {
	margin: 0.35em 0 0;
	padding: 0;
}

#body_content td ul.wc-item-meta li p {
	margin: 0;
}

#body_content p {
	margin: 0 0 14px;
}

#body_content_inner {
	color: <?php echo esc_attr( $text ); ?>;
	font-family: <?php echo $safe_font_family; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
	font-size: 15px;
	line-height: 165%;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

.td {
	color: <?php echo esc_attr( $text ); ?>;
	border: 0;
	vertical-align: middle;
}

.address {
	color: <?php echo esc_attr( $text ); ?>;
	font-style: normal;
	line-height: 165%;
	padding: 0;
	border: 0;
	word-break: break-word;
}

#addresses td + td {
	padding-<?php echo is_rtl() ? 'right' : 'left'; ?>: 10px !important;
}

.text,
.address-title,
.order-item-data {
	color: <?php echo esc_attr( $text ); ?>;
	font-family: <?php echo $safe_font_family; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
}

.link,
a {
	color: <?php echo esc_attr( $base ); ?>;
	font-weight: 600;
	text-decoration: underline;
}

#header_wrapper {
	padding: 22px 28px;
	display: block;
}

#template_footer td {
	padding: 0;
	border-radius: 0;
}

#template_footer #credit {
	background-color: <?php echo esc_attr( $base_dark ); ?>;
	color: <?php echo esc_attr( $bg ); ?>;
	border: 0;
	font-family: <?php echo $safe_font_family; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
	font-size: 12px;
	line-height: 150%;
	text-align: center;
	padding: 24px 28px;
}

#template_footer #credit a {
	color: #FFFFFF;
	text-decoration: underline;
}

h1 {
	color: #FFFFFF;
	font-family: <?php echo $serif_font_family; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
	font-size: 25px;
	font-weight: 700;
	line-height: 125%;
	margin: 0;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

h2 {
	color: <?php echo esc_attr( $base_dark ); ?>;
	display: block;
	font-family: <?php echo $serif_font_family; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
	font-size: 21px;
	font-weight: 700;
	line-height: 140%;
	margin: 0 0 14px;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

h3 {
	color: <?php echo esc_attr( $base_dark ); ?>;
	display: block;
	font-family: <?php echo $serif_font_family; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
	font-size: 16px;
	font-weight: 700;
	line-height: 140%;
	margin: 14px 0 6px;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

blockquote {
	background-color: <?php echo esc_attr( $bg ); ?>;
	border: 1px solid <?php echo esc_attr( $border ); ?>;
	border-left: 4px solid <?php echo esc_attr( $base ); ?>;
	border-radius: 10px;
	padding: 14px 18px;
	margin: 16px 0 20px;
	color: <?php echo esc_attr( $text ); ?>;
	font-style: normal;
}

blockquote p:last-child {
	margin-bottom: 0;
}

#body_content table td td.email-additional-content {
	background-color: <?php echo esc_attr( $rose_50 ); ?>;
	border: 1px solid <?php echo esc_attr( $rose_200 ); ?>;
	border-radius: 10px;
	color: <?php echo esc_attr( $base_dark ); ?>;
	font-family: <?php echo $safe_font_family; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
	font-size: 13px;
	padding: 14px 18px;
}

.email-additional-content p {
	text-align: center;
	margin: 0;
	color: <?php echo esc_attr( $base_dark ); ?>;
}

.font-family {
	font-family: <?php echo $safe_font_family; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
}

.text-align-left {
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

.text-align-right {
	text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>;
}

@media screen and (max-width: 600px) {
	#template_header_image {
		padding: 16px 16px !important;
	}

	#header_wrapper {
		padding: 18px 18px !important;
	}

	#header_wrapper h1 {
		font-size: 21px !important;
	}

	#body_content_inner_cell {
		padding: 18px 16px !important;
	}

	#body_content_inner {
		font-size: 14px !important;
	}
}
<?php
