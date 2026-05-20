<?php
/**
 * Checkout form.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_checkout_form', $checkout);

if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
	echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
	return;
}

$item_count = WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout kids-shop-checkout-form"
	action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data"
	aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">
	<div class="kids-shop-checkout-shell">
		<div class="kids-shop-checkout-left">
			<div class="kids-shop-checkout-panel">
				<h3 class="kids-shop-checkout-title"><?php esc_html_e('Delivery Address', 'kids-shop'); ?></h3>
				<p class="kids-shop-checkout-subtitle"><?php esc_html_e('Manage Saved Address', 'kids-shop'); ?></p>
				<div class="kids-shop-checkout-divider"></div>
				<div class="kids-shop-address-switches" aria-hidden="true">
					<div class="kids-shop-address-switch kids-shop-address-switch--active">
						<span class="kids-shop-address-switch-icon">&#10003;</span>
						<span><?php esc_html_e('Home', 'kids-shop'); ?></span>
					</div>
					<div class="kids-shop-address-switch kids-shop-address-switch--add">
						<span class="kids-shop-address-switch-plus">+</span>
						<span><?php esc_html_e('Add New Address', 'kids-shop'); ?></span>
					</div>
				</div>

				<?php if ($checkout->get_checkout_fields()): ?>
					<?php do_action('woocommerce_checkout_before_customer_details'); ?>

					<div class="kids-shop-checkout-customer-details" id="customer_details">
						<div class="kids-shop-checkout-billing">
							<?php do_action('woocommerce_checkout_billing'); ?>
						</div>
					</div>

					<?php do_action('woocommerce_checkout_after_customer_details'); ?>
				<?php endif; ?>

				<div class="kids-shop-checkout-divider"></div>
				<div class="kids-shop-payment-title"><?php esc_html_e('Select a Payment Option', 'kids-shop'); ?>
				</div>
				<div class="kids-shop-payment-preview">
					<span class="kids-shop-payment-check">&#10003;</span>
					<span class="kids-shop-payment-label"><?php esc_html_e('Cash on Delivery', 'kids-shop'); ?></span>
				</div>
			</div>
		</div>

		<div class="kids-shop-checkout-right">
			<div class="kids-shop-checkout-panel kids-shop-checkout-order-panel">
				<h3 class="kids-shop-checkout-title">
					<?php
					printf(
						/* translators: %d: item count */
						esc_html__('Order Items (%d Items)', 'kids-shop'),
						$item_count
					);
					?>
				</h3>
				<div class="kids-shop-checkout-divider"></div>

				<?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

				<div id="order_review" class="woocommerce-checkout-review-order">
					<?php do_action('woocommerce_checkout_order_review'); ?>
				</div>

				<a class="kids-shop-back-to-cart" href="<?php echo esc_url(wc_get_cart_url()); ?>">
					&larr; <?php esc_html_e('Back to Cart', 'kids-shop'); ?>
				</a>
			</div>
		</div>
	</div>
</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>