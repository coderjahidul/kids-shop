<?php
/**
 * Checkout shipping method dropdown.
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'WC' ) || ! WC()->cart || ! WC()->cart->needs_shipping() || ! WC()->cart->show_shipping() ) {
	return;
}

if ( function_exists( 'kids_shop_prepare_checkout_shipping' ) ) {
	kids_shop_prepare_checkout_shipping();
}

$packages = WC()->shipping()->get_packages();
$chosen_methods = WC()->session ? (array) WC()->session->get( 'chosen_shipping_methods', array() ) : array();
$has_methods    = false;
?>
<div class="kids-shop-shipping-options">
	<?php if ( ! empty( $packages ) ) : ?>
		<?php foreach ( $packages as $index => $package ) : ?>
			<?php
			$available_methods = isset( $package['rates'] ) ? $package['rates'] : array();
			$chosen_method     = isset( $chosen_methods[ $index ] ) ? $chosen_methods[ $index ] : '';
			$select_id         = 'kids-shop-shipping-method-' . (int) $index;
			?>
			<?php if ( ! empty( $available_methods ) ) : ?>
				<?php $has_methods = true; ?>
				<label class="screen-reader-text" for="<?php echo esc_attr( $select_id ); ?>">
					<?php esc_html_e( 'Select Shipping Option', 'kids-shop' ); ?>
				</label>
				<select
					id="<?php echo esc_attr( $select_id ); ?>"
					name="shipping_method[<?php echo esc_attr( $index ); ?>]"
					data-index="<?php echo esc_attr( $index ); ?>"
					class="shipping_method update_totals_on_change kids-shop-shipping-select"
				>
					<?php foreach ( $available_methods as $method ) : ?>
						<?php
						$method_label = wp_strip_all_tags( wc_cart_totals_shipping_method_label( $method ) );
						?>
						<option value="<?php echo esc_attr( $method->id ); ?>" <?php selected( $method->id, $chosen_method ); ?>>
							<?php echo esc_html( $method_label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ( ! $has_methods ) : ?>
		<p class="kids-shop-shipping-placeholder">
			<?php esc_html_e( 'Enter your address to view shipping options.', 'kids-shop' ); ?>
		</p>
	<?php endif; ?>
</div>
