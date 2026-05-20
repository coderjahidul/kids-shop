<?php
/**
 * Order customer details — delivery address card.
 *
 * @package Kids_Shop
 * @version 8.7.0
 */

defined( 'ABSPATH' ) || exit;

$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
$name          = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
$phone         = $order->get_billing_phone();
$email         = $order->get_billing_email();
$address       = $order->get_formatted_billing_address();
?>
<section class="kids-shop-view-order-address woocommerce-customer-details">
	<h2 class="kids-shop-view-order__section-title">
		<?php echo esc_html( $show_shipping ? __( 'Billing Address', 'woocommerce' ) : __( 'Delivery Address', 'kids-shop' ) ); ?>
	</h2>

	<div class="kids-shop-view-order-address__card">
		<?php if ( $name ) : ?>
			<p class="kids-shop-view-order-address__name"><?php echo esc_html( $name ); ?></p>
		<?php endif; ?>

		<?php if ( $phone ) : ?>
			<p class="kids-shop-view-order-address__line">
				<span class="kids-shop-view-order-address__label"><?php esc_html_e( 'Phone', 'kids-shop' ); ?>:</span>
				<?php echo esc_html( $phone ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $email ) : ?>
			<p class="kids-shop-view-order-address__line">
				<span class="kids-shop-view-order-address__label"><?php esc_html_e( 'Email', 'kids-shop' ); ?>:</span>
				<?php echo esc_html( $email ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $address ) : ?>
			<div class="kids-shop-view-order-address__formatted">
				<?php echo wp_kses_post( $address ); ?>
			</div>
		<?php else : ?>
			<p class="kids-shop-view-order-address__empty"><?php esc_html_e( 'N/A', 'woocommerce' ); ?></p>
		<?php endif; ?>

		<?php do_action( 'woocommerce_order_details_after_customer_address', 'billing', $order ); ?>
	</div>

	<?php if ( $show_shipping ) : ?>
		<h2 class="kids-shop-view-order__section-title kids-shop-view-order__section-title--shipping">
			<?php esc_html_e( 'Shipping Address', 'woocommerce' ); ?>
		</h2>
		<div class="kids-shop-view-order-address__card">
			<?php
			$shipping_address = $order->get_formatted_shipping_address();
			if ( $shipping_address ) {
				echo wp_kses_post( $shipping_address );
			} else {
				echo '<p class="kids-shop-view-order-address__empty">' . esc_html__( 'N/A', 'woocommerce' ) . '</p>';
			}
			if ( $order->get_shipping_phone() ) {
				echo '<p class="kids-shop-view-order-address__line"><span class="kids-shop-view-order-address__label">' . esc_html__( 'Phone', 'kids-shop' ) . ':</span> ' . esc_html( $order->get_shipping_phone() ) . '</p>';
			}
			do_action( 'woocommerce_order_details_after_customer_address', 'shipping', $order );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_order_details_after_customer_details', $order ); ?>
</section>
