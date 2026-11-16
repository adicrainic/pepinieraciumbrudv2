<?php
/**
 * Order details table shown in emails.
 *
 * Overridden by Pepiniera Ciumbrud API plugin:
 * - Displays a themed Order Status & Summary banner
 * - Capitalizes "Produs" column header with a capital "P"
 * - Disables product images in the order table
 * - Applies the Pepiniera Ciumbrud Red & Warm Cream color scheme
 *
 * @package WooCommerce\Templates\Emails
 * @version 11.2.0
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

defined( 'ABSPATH' ) || exit;

$email_improvements_enabled = FeaturesUtil::feature_is_enabled( 'email_improvements' );
$block_email_editor_enabled = FeaturesUtil::feature_is_enabled( 'block_email_editor' );

if ( $email_improvements_enabled ) {
	add_filter( 'woocommerce_order_shipping_to_display_shipped_via', '__return_false' );
}

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

// Determine Romanian status label & badge styling based on email type and order status
$email_id     = ( is_object( $email ?? null ) && isset( $email->id ) ) ? (string) $email->id : '';
$order_status = $order->get_status();

$status_label = 'Comandă Înregistrată';
$badge_bg     = '#FCF2F2';
$badge_border = '#F2BDBD';
$badge_text   = '#B21111';

if ( 'customer_completed_order' === $email_id || 'shipped' === $order_status ) {
	$status_label = 'Predată Curier';
	$badge_bg     = '#F1F7F2';
	$badge_border = '#8DBB97';
	$badge_text   = '#2F6B3A';
} elseif ( in_array( $email_id, [ 'customer_cancelled_order', 'cancelled_order' ], true ) || 'cancelled' === $order_status ) {
	$status_label = 'Comandă Anulată';
	$badge_bg     = '#FCF2F2';
	$badge_border = '#E68585';
	$badge_text   = '#7D0C0C';
} elseif ( 'customer_refunded_order' === $email_id || 'refunded' === $order_status ) {
	$status_label = 'Comandă Rambursată';
} elseif ( 'prepared' === $order_status ) {
	$status_label = 'Procesată (Pregătită)';
} elseif ( 'processing' === $order_status && 'customer_processing_order' !== $email_id ) {
	$status_label = 'În Procesare';
} elseif ( 'completed' === $order_status ) {
	$status_label = 'Finalizată (Livrată)';
	$badge_bg     = '#F1F7F2';
	$badge_border = '#8DBB97';
	$badge_text   = '#2F6B3A';
}

$order_number = $order->get_order_number();
$order_date   = wc_format_datetime( $order->get_date_created() );

$admin_order_url = '';
if ( $sent_to_admin ) {
	$admin_base      = class_exists( 'PCS_Mailer' ) ? PCS_Mailer::instance()->get_admin_url() : '';
	$admin_order_url = $admin_base ? ( $admin_base . '/comenzi/' . $order->get_id() ) : $order->get_edit_order_url();
}
?>

<!-- Order Status & Summary Header Card -->
<div style="background-color: #FAF6EE; border: 1px solid #E2DACB; border-left: 4px solid #B21111; border-radius: 10px; padding: 14px 18px; margin-bottom: 18px;">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
		<tr>
			<td align="left" valign="middle" style="padding: 0;">
				<div style="font-family: 'Cormorant Garamond', Georgia, 'Times New Roman', serif; font-size: 20px; font-weight: 700; color: #7D0C0C; line-height: 1.2;">
					<?php if ( $sent_to_admin && $admin_order_url ) : ?>
						<a href="<?php echo esc_url( $admin_order_url ); ?>" target="_blank" rel="noopener noreferrer" style="color: #7D0C0C; text-decoration: underline;">Comanda #<?php echo esc_html( $order_number ); ?></a>
					<?php else : ?>
						Rezumat Comandă #<?php echo esc_html( $order_number ); ?>
					<?php endif; ?>
				</div>
				<div style="font-size: 12px; color: #5F5A54; margin-top: 3px;">
					Plasată pe <time datetime="<?php echo esc_attr( $order->get_date_created()->format( 'c' ) ); ?>"><?php echo esc_html( $order_date ); ?></time>
				</div>
			</td>
			<td align="right" valign="middle" style="padding: 0 0 0 12px; white-space: nowrap;">
				<span style="display: inline-block; background-color: <?php echo esc_attr( $badge_bg ); ?>; border: 1px solid <?php echo esc_attr( $badge_border ); ?>; color: <?php echo esc_attr( $badge_text ); ?>; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 5px 12px; border-radius: 20px;">
					<?php echo esc_html( $status_label ); ?>
				</span>
			</td>
		</tr>
		<?php if ( $sent_to_admin && $admin_order_url ) : ?>
		<tr>
			<td colspan="2" align="left" style="padding-top: 10px;">
				<a href="<?php echo esc_url( $admin_order_url ); ?>" target="_blank" rel="noopener noreferrer" style="display: inline-block; background-color: #7D0C0C; color: #FFFFFF; text-decoration: none; font-size: 12px; font-weight: 600; padding: 6px 14px; border-radius: 6px;">
					Deschide în Panoul de Administrare &rarr;
				</a>
			</td>
		</tr>
		<?php endif; ?>
	</table>
</div>

<div style="margin-bottom: 24px; border: 1px solid #E2DACB; border-radius: 12px; overflow: hidden; background-color: #FFFFFF;">
	<table class="td font-family email-order-details" cellspacing="0" cellpadding="10" style="width: 100%; border-collapse: collapse;" border="0">
		<?php if ( ! $block_email_editor_enabled ) : ?>
		<thead>
			<tr style="background-color: #FAF6EE; border-bottom: 2px solid #E2DACB;">
				<th class="td text-align-left" scope="col" style="padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #7D0C0C; border-bottom: 2px solid #E2DACB;">Produs</th>
				<th class="td text-align-right" scope="col" style="padding: 12px 16px; text-align: right; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #7D0C0C; border-bottom: 2px solid #E2DACB;"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
				<th class="td text-align-right" scope="col" style="padding: 12px 16px; text-align: right; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #7D0C0C; border-bottom: 2px solid #E2DACB;"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
			</tr>
		</thead>
		<?php endif; ?>
		<tbody>
			<?php
			echo wc_get_email_order_items( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$order,
				array(
					'show_sku'      => false,
					'show_image'    => false,
					'image_size'    => array( 0, 0 ),
					'plain_text'    => $plain_text,
					'sent_to_admin' => $sent_to_admin,
				)
			);
			?>
		</tbody>
		<tfoot>
			<?php
			$item_totals       = $order->get_order_item_totals();
			$item_totals_count = count( $item_totals );

			if ( $item_totals ) {
				$i = 0;
				foreach ( $item_totals as $total_key => $total ) {
					++$i;
					$is_last  = ( $i === $item_totals_count );
					$is_total = ( 'order_total' === $total_key || 'total' === ( $total['type'] ?? '' ) || $is_last );

					if ( $email_improvements_enabled && 'shipping' === ( $total['type'] ?? '' ) && isset( $total['meta'] ) && $total['value'] === $total['meta'] ) {
						if ( (bool) apply_filters( 'woocommerce_email_order_shipping_show_free_label', $order->has_shipping_method( 'free_shipping' ), $order ) ) {
							$total['value'] = __( 'Free!', 'woocommerce' );
						}
					}

					$row_bg     = $is_total ? '#FCF2F2' : '#FAF6EE';
					$top_border = ( 1 === $i ) ? 'border-top: 2px solid #E2DACB;' : 'border-top: 1px solid #EDE6D9;';
					$label_col  = $is_total ? '#7D0C0C' : '#5F5A54';
					$val_col    = $is_total ? '#B21111' : '#2E2C2A';
					$font_size  = $is_total ? '17px' : '14px';
					$font_wt    = $is_total ? '700' : '600';
					?>
					<tr class="order-totals order-totals-<?php echo esc_attr( $total['type'] ?? 'unknown' ); ?>" style="background-color: <?php echo esc_attr( $row_bg ); ?>;">
						<th class="td text-align-left" scope="row" colspan="2" style="<?php echo esc_attr( $top_border ); ?> padding: 10px 16px; text-align: left; font-size: <?php echo esc_attr( $font_size ); ?>; font-weight: <?php echo esc_attr( $font_wt ); ?>; color: <?php echo esc_attr( $label_col ); ?>;">
							<?php
							echo wp_kses_post( $total['label'] ) . ' ';
							if ( $email_improvements_enabled ) {
								echo isset( $total['meta'] ) ? wp_kses_post( $total['meta'] ) : '';
							}
							?>
						</th>
						<td class="td text-align-right" style="<?php echo esc_attr( $top_border ); ?> padding: 10px 16px; text-align: right; font-size: <?php echo esc_attr( $font_size ); ?>; font-weight: 700; color: <?php echo esc_attr( $val_col ); ?>;">
							<?php echo wp_kses_post( $total['value'] ); ?>
						</td>
					</tr>
					<?php
				}
			}
			?>
		</tfoot>
	</table>
</div>

<?php if ( $order->get_customer_note() ) : ?>
	<div style="background-color: #FAF6EE; border: 1px solid #E2DACB; border-left: 4px solid #B21111; border-radius: 10px; padding: 14px 18px; margin-bottom: 24px;">
		<div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #7D0C0C; margin-bottom: 4px;">
			<?php esc_html_e( 'Customer note', 'woocommerce' ); ?>:
		</div>
		<div style="font-size: 14px; color: #2E2C2A; font-style: italic; line-height: 1.55;">
			<?php echo wp_kses( nl2br( wc_wptexturize_order_note( $order->get_customer_note() ) ), array( 'br' => array() ) ); ?>
		</div>
	</div>
<?php endif; ?>

<?php
if ( $email_improvements_enabled ) {
	remove_filter( 'woocommerce_order_shipping_to_display_shipped_via', '__return_false' );
}

do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );
?>
