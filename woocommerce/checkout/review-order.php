<?php
/**
 * Review order table.
 *
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

$regular_total  = kids_shop_cart_regular_subtotal();
$discount_total = kids_shop_cart_discount_total();
$shipping_total = WC()->cart ? (float) WC()->cart->get_shipping_total() + (float) WC()->cart->get_shipping_tax() : 0.0;
$grand_total    = WC()->cart ? (float) WC()->cart->get_total( 'edit' ) : 0.0;
?>
<div class="kids-shop-checkout-order-review shop_table woocommerce-checkout-review-order-table">
	<div class="kids-shop-checkout-items">
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				continue;
			}

			get_template_part(
				'template-parts/checkout/order',
				'item',
				array(
					'cart_item_key' => $cart_item_key,
					'cart_item'     => $cart_item,
					'_product'      => $_product,
				)
			);
		}

		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</div>

	<div class="kids-shop-checkout-summary">
		<div class="kids-shop-checkout-summary__row">
			<span><?php esc_html_e( 'Sub Total:', 'kids-shop' ); ?></span>
			<span><?php echo wp_kses_post( kids_shop_cart_format_price( $regular_total ) ); ?></span>
		</div>

		<div class="kids-shop-checkout-summary__row">
			<span><?php esc_html_e( 'Discount:', 'kids-shop' ); ?></span>
			<span class="kids-shop-checkout-summary__discount">
				<?php
				if ( $discount_total > 0 ) {
					echo wp_kses_post( '-' . wc_price( $discount_total, array( 'decimals' => 0 ) ) );
				} else {
					echo wp_kses_post( kids_shop_cart_format_price( 0 ) );
				}
				?>
			</span>
		</div>

		<div class="kids-shop-checkout-summary__row">
			<span><?php esc_html_e( 'Delivery Charge:', 'kids-shop' ); ?></span>
			<span><?php echo wp_kses_post( kids_shop_cart_format_price( $shipping_total ) ); ?></span>
		</div>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<div class="kids-shop-checkout-summary__row kids-shop-checkout-summary__row--total order-total">
			<span><?php esc_html_e( 'GrandTotal:', 'kids-shop' ); ?></span>
			<span><?php echo wp_kses_post( kids_shop_cart_format_price( $grand_total ) ); ?></span>
		</div>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
	</div>
</div>
