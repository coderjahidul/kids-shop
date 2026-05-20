<?php
/**
 * My Addresses — card layout (KiddoMart-style).
 *
 * @package Kids_Shop
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __( 'Billing address', 'woocommerce' ),
			'shipping' => __( 'Delivery address', 'woocommerce' ),
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __( 'Delivery address', 'woocommerce' ),
		),
		$customer_id
	);
}

$oldcol = 1;
$col    = 1;

$add_type = 'billing';
if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$add_type = 'shipping';
}
$add_url = wc_get_endpoint_url( 'edit-address', $add_type );
?>
<div class="kids-shop-ma-address">
	<h2 class="kids-shop-ma-address__page-title"><?php esc_html_e( 'Delivery Address', 'kids-shop' ); ?></h2>

	<div class="kids-shop-ma-address__grid">
		<?php foreach ( $get_addresses as $name => $address_title ) : ?>
			<?php
			$address = wc_get_account_formatted_address( $name );
			$col     = $col * -1;
			$oldcol  = $oldcol * -1;
			$edit    = wc_get_endpoint_url( 'edit-address', $name );
			?>
			<div class="kids-shop-ma-address__card woocommerce-Address woocommerce-Address--<?php echo esc_attr( $name ); ?>">
				<header class="kids-shop-ma-address__card-head">
					<h3 class="kids-shop-ma-address__card-title"><?php echo esc_html( $address_title ); ?></h3>
					<a class="kids-shop-ma-address__edit" href="<?php echo esc_url( $edit ); ?>">
						<?php echo $address ? esc_html__( 'Edit', 'kids-shop' ) : esc_html__( 'Add', 'kids-shop' ); ?>
					</a>
				</header>
				<div class="kids-shop-ma-address__card-body">
					<?php if ( $address ) : ?>
						<div class="kids-shop-ma-address__formatted"><?php echo wp_kses_post( $address ); ?></div>
					<?php else : ?>
						<p class="kids-shop-ma-address__empty"><?php esc_html_e( 'You have not set up this address yet.', 'kids-shop' ); ?></p>
					<?php endif; ?>
					<?php do_action( 'woocommerce_my_account_after_my_address', $name ); ?>
				</div>
			</div>
		<?php endforeach; ?>

		<a class="kids-shop-ma-address__card kids-shop-ma-address__card--add" href="<?php echo esc_url( $add_url ); ?>">
			<span class="kids-shop-ma-address__add-icon" aria-hidden="true">+</span>
			<span class="kids-shop-ma-address__add-title"><?php esc_html_e( 'Add New Address', 'kids-shop' ); ?></span>
			<span class="kids-shop-ma-address__add-hint"><?php esc_html_e( 'Tap here to add or update your delivery details.', 'kids-shop' ); ?></span>
		</a>
	</div>
</div>
