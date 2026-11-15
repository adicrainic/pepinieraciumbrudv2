<?php
/**
 * Email Order Items
 *
 * Overridden by Pepiniera Ciumbrud API plugin:
 * - Removes product images completely
 * - Keeps only the product name under the "Produs" column
 * - Styles rows with Pepiniera Ciumbrud warm palette
 *
 * @package WooCommerce\Templates\Emails
 * @version 11.2.0
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

defined( 'ABSPATH' ) || exit;

$email_improvements_enabled = FeaturesUtil::feature_is_enabled( 'email_improvements' );
$block_email_editor_enabled = FeaturesUtil::feature_is_enabled( 'block_email_editor' );

foreach ( $items as $item_id => $item ) :
	$product       = $item->get_product();
	$purchase_note = '';

	if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
		continue;
	}

	if ( is_object( $product ) ) {
		$purchase_note = $product->get_purchase_note();
	}

	$order_item_class = apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order );
	$order_item_name  = apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false );
	?>
	<tr class="<?php echo esc_attr( $order_item_class ); ?>">
		<td class="td font-family text-align-left" style="padding: 12px 16px; vertical-align: middle; word-wrap: break-word; border-bottom: 1px solid #EDE6D9; color: #2E2C2A;">
			<?php
			echo wp_kses_post( "<div class='wc-product-name' style='font-size: 14px; font-weight: 600; color: #2E2C2A; margin: 0;'>{$order_item_name}</div>" );
			?>
		</td>
		<td class="td font-family text-align-right" style="padding: 12px 16px; vertical-align: middle; text-align: right; border-bottom: 1px solid #EDE6D9; color: #5F5A54; font-size: 14px; font-weight: 600; white-space: nowrap;">
			<?php
			$qty          = $item->get_quantity();
			$refunded_qty = $order->get_qty_refunded_for_item( $item_id );

			if ( $refunded_qty ) {
				$qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
			} else {
				$qty_display = esc_html( $qty );
			}

			$quantity = apply_filters( 'woocommerce_email_order_item_quantity', $qty_display, $item );
			if ( '' !== $quantity ) {
				echo wp_kses_post( '&times;&nbsp;' . $quantity );
			}
			?>
		</td>
		<td class="td font-family text-align-right" style="padding: 12px 16px; vertical-align: middle; text-align: right; border-bottom: 1px solid #EDE6D9; color: #7D0C0C; font-size: 14px; font-weight: 700; white-space: nowrap;">
			<?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
		</td>
	</tr>
	<?php

	if ( $show_purchase_note && $purchase_note ) {
		?>
		<tr>
			<td colspan="3" class="font-family text-align-left" style="padding: 8px 16px; vertical-align: middle; border-bottom: 1px solid #EDE6D9; font-size: 13px; color: #5F5A54;">
				<?php
				echo wp_kses_post( wpautop( do_shortcode( $purchase_note ) ) );
				?>
			</td>
		</tr>
		<?php
	}
	?>

<?php endforeach; ?>
