<?php
/**
 * Account dashboard — orders & wishlists cards.
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shop_url     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$orders_url   = function_exists( 'wc_get_endpoint_url' ) && function_exists( 'wc_get_page_permalink' )
	? wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) )
	: '';
$order_count  = kids_shop_get_customer_order_count();
$has_orders   = $order_count > 0;
?>
<section class="kids-shop-myaccount-panel" id="kids-shop-orders">
	<header class="kids-shop-myaccount-panel__header">
		<h2 class="kids-shop-myaccount-panel__title"><?php esc_html_e( 'My Order', 'kids-shop' ); ?></h2>
		<a class="kids-shop-myaccount-btn kids-shop-myaccount-btn--outline kids-shop-myaccount-panel__action" href="<?php echo esc_url( $shop_url ); ?>">
			<?php esc_html_e( 'Create Order', 'kids-shop' ); ?>
		</a>
	</header>
	<div class="kids-shop-myaccount-panel__body">
		<?php if ( $has_orders && $orders_url ) : ?>
			<p class="kids-shop-myaccount-panel__summary">
				<?php
				printf(
					/* translators: %d: order count */
					esc_html( _n( 'You have %d order.', 'You have %d orders.', $order_count, 'kids-shop' ) ),
					(int) $order_count
				);
				?>
			</p>
			<a class="kids-shop-myaccount-btn kids-shop-myaccount-btn--solid" href="<?php echo esc_url( $orders_url ); ?>">
				<?php esc_html_e( 'View All Orders', 'kids-shop' ); ?>
			</a>
		<?php else : ?>
			<?php
			get_template_part(
				'template-parts/myaccount/empty',
				'state',
				array(
					'empty_title' => __( 'Your Order List is Empty!', 'kids-shop' ),
					'empty_url'   => $shop_url,
					'empty_action' => __( 'Create Order', 'kids-shop' ),
				)
			);
			?>
		<?php endif; ?>
	</div>
</section>

<section class="kids-shop-myaccount-panel" id="kids-shop-wishlists">
	<header class="kids-shop-myaccount-panel__header">
		<h2 class="kids-shop-myaccount-panel__title"><?php esc_html_e( 'My Wishlists', 'kids-shop' ); ?></h2>
		<a class="kids-shop-myaccount-btn kids-shop-myaccount-btn--outline kids-shop-myaccount-panel__action" href="<?php echo esc_url( $shop_url ); ?>">
			<?php esc_html_e( 'Add Wishlist', 'kids-shop' ); ?>
		</a>
	</header>
	<div class="kids-shop-myaccount-panel__body">
		<?php
		get_template_part(
			'template-parts/myaccount/empty',
			'state',
			array(
				'empty_title'  => __( 'Your Wish List is Empty!', 'kids-shop' ),
				'empty_url'    => $shop_url,
				'empty_action' => __( 'Add Wishlist', 'kids-shop' ),
			)
		);
		?>
	</div>
</section>
