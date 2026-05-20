<?php
/**
 * Header hover mini-cart dropdown content.
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cart     = function_exists( 'WC' ) && WC()->cart ? WC()->cart : null;
$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$checkout = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' );
$is_empty = ! $cart || $cart->is_empty();
?>
<?php if ( $is_empty ) : ?>
	<div _ngcontent-ng-c3456407154="" class="no-data kids-shop-header-cart-empty">
		<app-empty-data _ngcontent-ng-c3456407154="" _nghost-ng-c1872529743="">
			<div _ngcontent-ng-c1872529743="" class="no-card" style="border-width: medium; border-style: none; border-color: currentcolor; border-image: initial;">
				<div _ngcontent-ng-c1872529743="" class="info">
					<h3 _ngcontent-ng-c1872529743="" style="font-size: 20px;"><?php esc_html_e( 'Your Cart List is Empty!', 'kids-shop' ); ?></h3>
					<p _ngcontent-ng-c1872529743=""><?php esc_html_e( 'Sorry! your cart has no item to show. Please add some product to see here.', 'kids-shop' ); ?></p>
				</div>
				<div _ngcontent-ng-c1872529743="" class="action">
					<a _ngcontent-ng-c1872529743="" class="btn" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Continue Shopping', 'kids-shop' ); ?></a>
				</div>
			</div>
		</app-empty-data>
	</div>
<?php else : ?>
	<ul _ngcontent-ng-c3456407154="" class="kids-shop-header-cart-list">
		<?php
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			get_template_part(
				'template-parts/cart/header-dropdown-item',
				null,
				array(
					'cart_item_key' => $cart_item_key,
					'cart_item'     => $cart_item,
				)
			);
		}
		?>
	</ul>
	<div _ngcontent-ng-c3456407154="" class="shopping-cart-footer">
		<div _ngcontent-ng-c3456407154="" class="shopping-cart-total">
			<h4 _ngcontent-ng-c3456407154=""><?php esc_html_e( 'Subtotal:', 'kids-shop' ); ?></h4>
			<span _ngcontent-ng-c3456407154=""><?php echo wp_kses_post( $cart->get_cart_subtotal() ); ?></span>
		</div>
		<div _ngcontent-ng-c3456407154="" class="shopping-cart-button">
			<a _ngcontent-ng-c3456407154="" class="outline" href="<?php echo esc_url( $cart_url ); ?>"><?php esc_html_e( 'View Cart', 'kids-shop' ); ?></a>
			<a _ngcontent-ng-c3456407154="" href="<?php echo esc_url( $checkout ); ?>"><?php esc_html_e( 'Checkout', 'kids-shop' ); ?></a>
		</div>
	</div>
<?php endif; ?>
