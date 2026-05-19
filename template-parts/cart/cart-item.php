<?php
/**
 * Single cart line item.
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
$thumbnail     = apply_filters(
	'woocommerce_cart_item_thumbnail',
	sprintf(
		'<img src="%s" alt="%s" class="product-image attachment-woocommerce_thumbnail" width="80" height="80" loading="lazy" decoding="async" />',
		esc_url( $image_url ),
		esc_attr( $product_name )
	),
	$cart_item,
	$cart_item_key
);
$quantity      = (int) $cart_item['quantity'];
$line_total_amount = (float) $_product->get_price() * $quantity;
$line_subtotal     = apply_filters(
	'woocommerce_cart_item_subtotal',
	kids_shop_cart_format_line_total( $line_total_amount ),
	$cart_item,
	$cart_item_key
);
$price_html    = apply_filters(
	'woocommerce_cart_item_price',
	kids_shop_cart_format_price( (float) $_product->get_price() ),
	$cart_item,
	$cart_item_key
);
$remove_url    = wc_get_cart_remove_url( $cart_item_key );
$category      = kids_shop_get_product_category_label( $product_id );
$on_sale       = $_product->is_on_sale();
$regular_price = $_product->get_regular_price();
?>
<div _ngcontent-ng-c713332739="" class="cart-item kids-shop-cart-item" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
	<div _ngcontent-ng-c713332739="" class="product-info">
		<input _ngcontent-ng-c713332739="" class="mobile-checkbox kids-shop-cart-item-check" type="checkbox" checked="checked" aria-label="<?php esc_attr_e( 'Include item', 'kids-shop' ); ?>"/>
		<?php
		if ( $permalink ) {
			echo '<a _ngcontent-ng-c713332739="" class="product-image-link" href="' . esc_url( $permalink ) . '">';
		}
		echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $permalink ) {
			echo '</a>';
		}
		?>
		<div _ngcontent-ng-c713332739="" class="details">
			<?php if ( $permalink ) : ?>
				<a _ngcontent-ng-c713332739="" class="product-title" href="<?php echo esc_url( $permalink ); ?>"><?php echo wp_kses_post( $product_name ); ?></a>
			<?php else : ?>
				<span _ngcontent-ng-c713332739="" class="product-title"><?php echo wp_kses_post( $product_name ); ?></span>
			<?php endif; ?>
			<?php if ( $category ) : ?>
				<span _ngcontent-ng-c713332739="" class="sku"><?php esc_html_e( 'Category:', 'kids-shop' ); ?> <?php echo esc_html( $category ); ?></span>
			<?php endif; ?>
			<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
	<div _ngcontent-ng-c713332739="" class="qty-area">
		<div _ngcontent-ng-c713332739="" class="quantity-container">
			<button _ngcontent-ng-c713332739="" type="button" class="quantity-button kids-shop-qty-minus" aria-label="<?php esc_attr_e( 'Decrease quantity', 'kids-shop' ); ?>">
				<svg _ngcontent-ng-c713332739="" fill="#5f6368" height="24px" viewbox="0 0 24 24" width="24px" xmlns="http://www.w3.org/2000/svg"><path _ngcontent-ng-c713332739="" d="M0 0h24v24H0z" fill="none"></path><path _ngcontent-ng-c713332739="" d="M19 13H5v-2h14v2z"></path></svg>
			</button>
			<label class="screen-reader-text" for="cart-qty-<?php echo esc_attr( $cart_item_key ); ?>"><?php esc_html_e( 'Quantity', 'kids-shop' ); ?></label>
			<input
				_ngcontent-ng-c713332739=""
				id="cart-qty-<?php echo esc_attr( $cart_item_key ); ?>"
				class="quantity-input"
				type="number"
				name="cart[<?php echo esc_attr( $cart_item_key ); ?>][qty]"
				value="<?php echo esc_attr( $quantity ); ?>"
				min="<?php echo esc_attr( max( 1, $_product->get_min_purchase_quantity() ) ); ?>"
				<?php echo $_product->get_max_purchase_quantity() > 0 ? 'max="' . esc_attr( $_product->get_max_purchase_quantity() ) . '"' : ''; ?>
				step="1"
				inputmode="numeric"
			/>
			<button _ngcontent-ng-c713332739="" type="button" class="quantity-button kids-shop-qty-plus" aria-label="<?php esc_attr_e( 'Increase quantity', 'kids-shop' ); ?>">
				<svg _ngcontent-ng-c713332739="" fill="#5f6368" height="24px" viewbox="0 0 24 24" width="24px" xmlns="http://www.w3.org/2000/svg"><path _ngcontent-ng-c713332739="" d="M0 0h24v24H0z" fill="none"></path><path _ngcontent-ng-c713332739="" d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"></path></svg>
			</button>
		</div>
		<div _ngcontent-ng-c713332739="" class="price mobile-d-none">
			<p _ngcontent-ng-c713332739=""><?php echo wp_kses_post( $price_html ); ?></p>
			<?php if ( $on_sale && $regular_price ) : ?>
				<p _ngcontent-ng-c713332739="" class="price-discount"><del _ngcontent-ng-c713332739=""><?php echo wp_kses_post( kids_shop_cart_format_price( $regular_price ) ); ?></del></p>
			<?php endif; ?>
		</div>
		<p _ngcontent-ng-c713332739="" class="total mobile-d-none"><?php echo wp_kses_post( $line_subtotal ); ?></p>
		<div _ngcontent-ng-c713332739="" class="delete-icon kids-shop-delete-desktop mobile-d-none">
			<a href="<?php echo esc_url( $remove_url ); ?>" class="kids-shop-remove-cart-item" aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s from cart', 'kids-shop' ), wp_strip_all_tags( $product_name ) ) ); ?>">
				<svg _ngcontent-ng-c713332739="" height="512" viewbox="0 0 24 24" width="512" xmlns="http://www.w3.org/2000/svg"><path _ngcontent-ng-c713332739="" d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6A1,1,0,0,0,6,7.414L10.586,12,6,16.586A1,1,0,0,0,6,18H6a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"></path></svg>
			</a>
		</div>
		<div _ngcontent-ng-c713332739="" class="mobile-view web-d-none">
			<div _ngcontent-ng-c713332739="" class="price">
				<p _ngcontent-ng-c713332739=""><?php echo wp_kses_post( $price_html ); ?></p>
				<?php if ( $on_sale && $regular_price ) : ?>
					<p _ngcontent-ng-c713332739="" class="price-discount"><del _ngcontent-ng-c713332739=""><?php echo wp_kses_post( kids_shop_cart_format_price( $regular_price ) ); ?></del></p>
				<?php endif; ?>
			</div>
			<div _ngcontent-ng-c713332739="" class="delete-icon">
				<a href="<?php echo esc_url( $remove_url ); ?>" class="kids-shop-remove-cart-item" aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s from cart', 'kids-shop' ), wp_strip_all_tags( $product_name ) ) ); ?>">
					<svg _ngcontent-ng-c713332739="" height="512" viewbox="0 0 24 24" width="512" xmlns="http://www.w3.org/2000/svg"><path _ngcontent-ng-c713332739="" d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6A1,1,0,0,0,6,7.414L10.586,12,6,16.586A1,1,0,0,0,6,18H6a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"></path></svg>
				</a>
			</div>
		</div>
	</div>
</div>
