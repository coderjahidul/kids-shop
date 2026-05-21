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
				<?php
				$user_id = get_current_user_id();
				$saved_addresses = array();
				if ($user_id && function_exists('kids_shop_get_saved_addresses')) {
					$saved_addresses = kids_shop_get_saved_addresses($user_id);
				}
				?>

				<?php if (!empty($saved_addresses)): ?>
					<div class="kids-shop-address-switches" aria-label="<?php esc_attr_e('Saved Addresses', 'kids-shop'); ?>">
						<?php 
						$active_id = 'home';
						foreach ($saved_addresses as $addr_id => $addr): 
							$is_active = ($addr_id === $active_id);
						?>
							<div class="kids-shop-address-switch <?php echo $is_active ? 'kids-shop-address-switch--active' : ''; ?>" 
								data-address-id="<?php echo esc_attr($addr_id); ?>" 
								data-address="<?php echo esc_attr(wp_json_encode($addr)); ?>">
								<span class="kids-shop-address-switch-icon">&#10003;</span>
								<span><?php echo esc_html($addr['label']); ?></span>
							</div>
						<?php endforeach; ?>
						<div class="kids-shop-address-switch kids-shop-address-switch--add">
							<span class="kids-shop-address-switch-plus">+</span>
							<span><?php esc_html_e('Add New Address', 'kids-shop'); ?></span>
						</div>
					</div>
					<input type="hidden" name="kids_shop_selected_address_id" id="kids_shop_selected_address_id" value="<?php echo esc_attr($active_id); ?>">

					<!-- Beautiful Address Preview Card -->
					<div class="kids-shop-address-preview-card" style="display: none;">
						<div class="kids-shop-address-preview-body">
							<h4 class="kids-shop-address-preview-name"></h4>
							<div class="kids-shop-address-preview-row kids-shop-address-preview-phone-row">
								<span class="kids-shop-address-preview-icon">📞</span>
								<span class="kids-shop-address-preview-phone"></span>
							</div>
							<div class="kids-shop-address-preview-row kids-shop-address-preview-location-row">
								<span class="kids-shop-address-preview-icon">📍</span>
								<span class="kids-shop-address-preview-address"></span>
							</div>
						</div>
						<div class="kids-shop-address-preview-footer">
							<button type="button" class="kids-shop-address-edit-btn button secondary">
								<span>✏️</span> <?php esc_html_e('Edit Address', 'kids-shop'); ?>
							</button>
						</div>
					</div>
				<?php else: ?>
					<div class="kids-shop-address-switches" aria-hidden="true" style="display: none;">
						<div class="kids-shop-address-switch kids-shop-address-switch--active">
							<span class="kids-shop-address-switch-icon">&#10003;</span>
							<span><?php esc_html_e('Home', 'kids-shop'); ?></span>
						</div>
						<div class="kids-shop-address-switch kids-shop-address-switch--add">
							<span class="kids-shop-address-switch-plus">+</span>
							<span><?php esc_html_e('Add New Address', 'kids-shop'); ?></span>
						</div>
					</div>
				<?php endif; ?>

				<?php if ($checkout->get_checkout_fields()): ?>
					<?php do_action('woocommerce_checkout_before_customer_details'); ?>

					<div class="kids-shop-checkout-customer-details" id="customer_details">
						<div class="kids-shop-checkout-billing">
							<?php do_action('woocommerce_checkout_billing'); ?>
						</div>

						<?php if (!empty($saved_addresses)): ?>
							<div class="kids-shop-address-form-actions" style="display: none;">
								<div class="form-row form-row-wide" id="kids_shop_address_label_field_wrapper">
									<label for="kids_shop_address_label"><?php esc_html_e('Address Label (e.g. Office, Home 2) *', 'kids-shop'); ?></label>
									<input type="text" class="input-text" name="kids_shop_address_label" id="kids_shop_address_label" placeholder="<?php esc_attr_e('e.g. Office', 'kids-shop'); ?>">
								</div>
								<div class="kids-shop-address-form-buttons">
									<button type="button" class="kids-shop-save-address-btn button alt"><?php esc_html_e('Save Address', 'kids-shop'); ?></button>
									<button type="button" class="kids-shop-cancel-address-btn button secondary"><?php esc_html_e('Cancel', 'kids-shop'); ?></button>
								</div>
							</div>
						<?php endif; ?>
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

				<?php if ( WC()->cart && WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
					<div class="kids-shop-shipping-section">
						<div class="kids-shop-payment-title"><?php esc_html_e('Select Shipping Option', 'kids-shop'); ?></div>
						<?php get_template_part( 'template-parts/checkout/shipping', 'options' ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="kids-shop-checkout-right">
			<div class="kids-shop-checkout-panel kids-shop-checkout-order-panel">
				<h3 class="kids-shop-checkout-title">
					<span class="kids-shop-checkout-order-count">
					<?php
					printf(
						/* translators: %d: item count */
						esc_html__('Order Items (%d Items)', 'kids-shop'),
						$item_count
					);
					?>
					</span>
				</h3>
				<div class="kids-shop-checkout-divider"></div>

				<?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

				<div id="order_review" class="woocommerce-checkout-review-order">
					<?php woocommerce_order_review(); ?>
				</div>

				<?php if ( wc_coupons_enabled() ) : ?>
					<button type="button" class="kids-shop-checkout-coupon-toggle showcoupon" aria-controls="woocommerce-checkout-form-coupon" aria-expanded="false">
						<?php esc_html_e('Do have any coupon code?', 'kids-shop'); ?>
					</button>
					<?php wc_get_template( 'checkout/form-coupon.php' ); ?>
				<?php endif; ?>

				<div class="kids-shop-checkout-order-footer">
					<a class="kids-shop-back-to-cart" href="<?php echo esc_url(wc_get_cart_url()); ?>">
						&larr; <?php esc_html_e('Back to Cart', 'kids-shop'); ?>
					</a>
					<?php woocommerce_checkout_payment(); ?>
				</div>
			</div>
		</div>
	</div>
</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>