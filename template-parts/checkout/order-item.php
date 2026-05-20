<?php
/**
 * Single checkout order line item card.
 *
 * @package Kids_Shop
 * @var string     $cart_item_key Cart item key.
 * @var array      $cart_item     Cart item data.
 * @var WC_Product $_product      Product object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $args ) && is_array( $args ) ) {
	$cart_item_key = isset( $args['cart_item_key'] ) ? $args['cart_item_key'] : '';
	$cart_item     = isset( $args['cart_item'] ) ? $args['cart_item'] : array();
	$_product      = isset( $args['_product'] ) ? $args['_product'] : null;
}

if ( ! $_product || ! $_product->exists() || (int) $cart_item['quantity'] <= 0 ) {
	return;
}

$product_id    = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
$permalink     = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
$product_name  = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
$image_id      = $_product->get_image_id();
$image_url     = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' ) : wc_placeholder_img_src( 'woocommerce_thumbnail' );
if ( ! $image_url ) {
	$image_url = wc_placeholder_img_src( 'woocommerce_thumbnail' );
}
$quantity      = (int) $cart_item['quantity'];
$price_html    = kids_shop_cart_format_price( (float) $_product->get_price() );
$on_sale       = $_product->is_on_sale();
$regular_price = (float) $_product->get_regular_price();
$min_qty       = max( 1, $_product->get_min_purchase_quantity() );
$max_qty       = $_product->get_max_purchase_quantity();
?>
<div class="kids-shop-checkout-item cart_item" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
	<button type="button" class="kids-shop-checkout-item__remove kids-shop-checkout-remove-item" aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s from cart', 'kids-shop' ), wp_strip_all_tags( $product_name ) ) ); ?>">
		<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M18 6L12 12.586 7.414 6A1 1 0 0 0 6 6v0a1 1 0 0 0 0 1.414L10.586 12 6 16.586A1 1 0 0 0 6 18v0a1 1 0 0 0 1.414 0L12 13.414 16.586 18A1 1 0 0 0 18 18v0a1 1 0 0 0 0-1.414L13.414 12 18 7.414A1 1 0 0 0 18 6Z"/></svg>
	</button>

	<div class="kids-shop-checkout-item__media">
		<?php if ( $permalink ) : ?>
			<a class="kids-shop-checkout-item__image-link" href="<?php echo esc_url( $permalink ); ?>">
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $product_name ) ); ?>" class="kids-shop-checkout-item__image" width="80" height="80" loading="lazy" decoding="async"/>
			</a>
		<?php else : ?>
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $product_name ) ); ?>" class="kids-shop-checkout-item__image" width="80" height="80" loading="lazy" decoding="async"/>
		<?php endif; ?>
	</div>

	<div class="kids-shop-checkout-item__body">
		<div class="kids-shop-checkout-item__name">
			<?php if ( $permalink ) : ?>
				<a href="<?php echo esc_url( $permalink ); ?>"><?php echo wp_kses_post( $product_name ); ?></a>
			<?php else : ?>
				<?php echo wp_kses_post( $product_name ); ?>
			<?php endif; ?>
		</div>

		<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<div class="kids-shop-checkout-item__meta">
			<div class="kids-shop-checkout-item__qty quantity-container">
				<button type="button" class="quantity-button kids-shop-qty-minus" aria-label="<?php esc_attr_e( 'Decrease quantity', 'kids-shop' ); ?>">-</button>
				<label class="screen-reader-text" for="checkout-qty-<?php echo esc_attr( $cart_item_key ); ?>"><?php esc_html_e( 'Quantity', 'kids-shop' ); ?></label>
				<input
					id="checkout-qty-<?php echo esc_attr( $cart_item_key ); ?>"
					class="quantity-input kids-shop-checkout-qty-input"
					type="number"
					value="<?php echo esc_attr( $quantity ); ?>"
					min="<?php echo esc_attr( $min_qty ); ?>"
					<?php echo $max_qty > 0 ? 'max="' . esc_attr( $max_qty ) . '"' : ''; ?>
					step="1"
					inputmode="numeric"
					data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
				/>
				<button type="button" class="quantity-button kids-shop-qty-plus" aria-label="<?php esc_attr_e( 'Increase quantity', 'kids-shop' ); ?>">+</button>
			</div>

			<div class="kids-shop-checkout-item__price">
				<span class="kids-shop-checkout-item__price-current"><?php echo wp_kses_post( $price_html ); ?></span>
				<?php if ( $on_sale && $regular_price > 0 ) : ?>
					<span class="kids-shop-checkout-item__price-regular"><del><?php echo wp_kses_post( kids_shop_cart_format_price( $regular_price ) ); ?></del></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
