<?php
/**
 * Header mini-cart dropdown line item.
 *
 * @package Kids_Shop
 * @var string     $cart_item_key Cart item key.
 * @var array      $cart_item     Cart item data.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $args ) && is_array( $args ) ) {
	$cart_item_key = isset( $args['cart_item_key'] ) ? $args['cart_item_key'] : '';
	$cart_item     = isset( $args['cart_item'] ) ? $args['cart_item'] : array();
}

$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

if ( ! $_product || ! $_product->exists() || (int) $cart_item['quantity'] <= 0 ) {
	return;
}

$product_id   = (int) $cart_item['product_id'];
$permalink    = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
$product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
$image_id     = $_product->get_image_id();
$image_url    = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' ) : wc_placeholder_img_src( 'woocommerce_thumbnail' );

if ( ! $image_url ) {
	$image_url = wc_placeholder_img_src( 'woocommerce_thumbnail' );
}

$quantity  = (int) $cart_item['quantity'];
$line_html = apply_filters(
	'woocommerce_cart_item_subtotal',
	WC()->cart->get_product_subtotal( $_product, $quantity ),
	$cart_item,
	$cart_item_key
);
$remove_url = wc_get_cart_remove_url( $cart_item_key );
$item_data  = wc_get_formatted_cart_item_data( $cart_item, true );
?>
<li _ngcontent-ng-c3456407154="" class="kids-shop-header-cart-item" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
	<div _ngcontent-ng-c3456407154="" class="shopping-cart-img">
		<?php if ( $permalink ) : ?>
			<a _ngcontent-ng-c3456407154="" href="<?php echo esc_url( $permalink ); ?>">
				<img _ngcontent-ng-c3456407154="" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $product_name ) ); ?>" width="55" height="55" loading="lazy" decoding="async"/>
			</a>
		<?php else : ?>
			<img _ngcontent-ng-c3456407154="" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $product_name ) ); ?>" width="55" height="55" loading="lazy" decoding="async"/>
		<?php endif; ?>
	</div>
	<div _ngcontent-ng-c3456407154="" class="shopping-cart-title">
		<?php if ( $permalink ) : ?>
			<a _ngcontent-ng-c3456407154="" href="<?php echo esc_url( $permalink ); ?>"><?php echo wp_kses_post( $product_name ); ?></a>
		<?php else : ?>
			<span _ngcontent-ng-c3456407154=""><?php echo wp_kses_post( $product_name ); ?></span>
		<?php endif; ?>
		<p _ngcontent-ng-c3456407154=""><?php echo wp_kses_post( $line_html ); ?> <span _ngcontent-ng-c3456407154="">× <?php echo esc_html( (string) $quantity ); ?></span></p>
		<?php if ( $item_data ) : ?>
			<div _ngcontent-ng-c3456407154="" class="variation-info"><?php echo wp_kses_post( $item_data ); ?></div>
		<?php endif; ?>
	</div>
	<div _ngcontent-ng-c3456407154="" class="shopping-cart-delete">
		<a _ngcontent-ng-c3456407154="" href="<?php echo esc_url( $remove_url ); ?>" class="remove kids-shop-header-cart-remove" aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s from cart', 'kids-shop' ), wp_strip_all_tags( $product_name ) ) ); ?>" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
			<svg _ngcontent-ng-c3456407154="" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path _ngcontent-ng-c3456407154="" d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
		</a>
	</div>
</li>
