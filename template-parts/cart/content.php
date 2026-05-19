<?php
/**
 * Cart page content (items + checkout summary).
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! WC()->cart || WC()->cart->is_empty() ) {
	return;
}

$cart_count     = WC()->cart->get_cart_contents_count();
$checkout_url   = wc_get_checkout_url();
$subtotal_html  = kids_shop_cart_format_price( WC()->cart->get_subtotal() );
$regular_total  = kids_shop_cart_regular_subtotal();
$discount_total = kids_shop_cart_discount_total();
$grand_total    = kids_shop_cart_format_price( (float) WC()->cart->get_total( 'edit' ) );
?>
<form class="woocommerce-cart-form kids-shop-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php do_action( 'woocommerce_before_cart' ); ?>

	<app-cart _nghost-ng-c713332739="" class="ng-star-inserted">
		<div _ngcontent-ng-c713332739="" class="container kids-shop-cart-layout">
			<div _ngcontent-ng-c713332739="" class="cart-items ng-star-inserted">
				<div _ngcontent-ng-c713332739="" class="cart-items-heading">
					<h3 _ngcontent-ng-c713332739="" class="title"><?php esc_html_e( 'My Cart', 'kids-shop' ); ?></h3>
					<div _ngcontent-ng-c713332739="" class="cart-items-heading-right">
						<h3 _ngcontent-ng-c713332739="" class="total-items">
							<?php
							printf(
								/* translators: %d: number of items in cart */
								esc_html( _n( '%d Item', '%d Items', $cart_count, 'kids-shop' ) ),
								(int) $cart_count
							);
							?>
						</h3>
					</div>
				</div>
				<div _ngcontent-ng-c713332739="" class="cart-border-element"></div>
				<div _ngcontent-ng-c713332739="" class="cart-header mobile-d-none">
					<div _ngcontent-ng-c713332739="" class="header-area">
						<div _ngcontent-ng-c713332739="" class="select-all mobile-d-none">
							<input _ngcontent-ng-c713332739="" id="kids-shop-select-all" class="kids-shop-select-all" type="checkbox" checked="checked"/>
						</div>
						<div _ngcontent-ng-c713332739="" class="header-item product-details"><?php esc_html_e( 'Product Details', 'kids-shop' ); ?></div>
					</div>
					<div _ngcontent-ng-c713332739="" class="header-right">
						<div _ngcontent-ng-c713332739="" class="header-item quantity"><?php esc_html_e( 'Quantity', 'kids-shop' ); ?></div>
						<div _ngcontent-ng-c713332739="" class="header-item price"><?php esc_html_e( 'Price', 'kids-shop' ); ?></div>
						<div _ngcontent-ng-c713332739="" class="header-item total"><?php esc_html_e( 'Total', 'kids-shop' ); ?></div>
					</div>
				</div>

				<?php
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
						continue;
					}
					if ( ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
						continue;
					}
					get_template_part(
						'template-parts/cart/cart',
						'item',
						array(
							'cart_item_key' => $cart_item_key,
							'cart_item'     => $cart_item,
							'_product'      => $_product,
						)
					);
				}
				?>

				<button type="submit" class="button kids-shop-update-cart-hidden" name="update_cart" value="1" hidden>Update</button>
			</div>

			<div _ngcontent-ng-c713332739="" class="cart-checkout sticky-view ng-star-inserted">
				<div _ngcontent-ng-c713332739="" class="checkout-container sticky-view">
					<!-- Desktop order summary (matches reference) -->
					<div _ngcontent-ng-c713332739="" class="kids-shop-desktop-summary mobile-d-none">
						<div _ngcontent-ng-c713332739="" class="cart-items-heading">
							<h3 _ngcontent-ng-c713332739=""><?php esc_html_e( 'Order Summary', 'kids-shop' ); ?></h3>
						</div>
						<div _ngcontent-ng-c713332739="" class="checkout-border-element"></div>
						<div _ngcontent-ng-c713332739="" class="cost-summary">
							<div _ngcontent-ng-c713332739="" class="summary-item">
								<span _ngcontent-ng-c713332739=""><?php esc_html_e( 'Sub Total', 'kids-shop' ); ?></span>
								<span _ngcontent-ng-c713332739=""><?php echo wp_kses_post( kids_shop_cart_format_price( $regular_total ) ); ?></span>
							</div>
							<?php if ( $discount_total > 0 ) : ?>
								<div _ngcontent-ng-c713332739="" class="summary-item">
									<span _ngcontent-ng-c713332739=""><?php esc_html_e( 'Discount', 'kids-shop' ); ?></span>
									<span _ngcontent-ng-c713332739=""><?php echo wp_kses_post( kids_shop_cart_format_price( $discount_total ) ); ?></span>
								</div>
							<?php endif; ?>
							<div _ngcontent-ng-c713332739="" class="summary-item total">
								<span _ngcontent-ng-c713332739=""><?php esc_html_e( 'GrandTotal', 'kids-shop' ); ?></span>
								<span _ngcontent-ng-c713332739=""><?php echo wp_kses_post( $grand_total ); ?></span>
							</div>
						</div>
						<a _ngcontent-ng-c713332739="" class="btn kids-shop-checkout-btn" href="<?php echo esc_url( $checkout_url ); ?>"><?php esc_html_e( 'Checkout', 'kids-shop' ); ?></a>
					</div>

					<!-- Mobile sticky checkout bar -->
					<div _ngcontent-ng-c713332739="" class="kids-shop-mobile-checkout web-d-none">
						<div _ngcontent-ng-c713332739="" class="select-all">
							<input _ngcontent-ng-c713332739="" id="select-all-mobile" class="kids-shop-select-all" type="checkbox" checked="checked"/>
							<label _ngcontent-ng-c713332739="" for="select-all-mobile"><?php esc_html_e( 'All', 'kids-shop' ); ?></label>
						</div>
						<div _ngcontent-ng-c713332739="" class="order-summary-container">
							<div _ngcontent-ng-c713332739="" class="order-summary kids-shop-mobile-order-summary">
								<div _ngcontent-ng-c713332739="" class="subtotal">
									<div _ngcontent-ng-c713332739="" class="sub-total-cart">
										<span _ngcontent-ng-c713332739=""><?php esc_html_e( 'Subtotal', 'kids-shop' ); ?></span>
										<span _ngcontent-ng-c713332739="" class="kids-shop-cart-subtotal"><?php echo wp_kses_post( $subtotal_html ); ?></span>
									</div>
									<span _ngcontent-ng-c713332739="" class="arrow kids-shop-summary-toggle" role="button" tabindex="0" aria-expanded="false">
										<svg _ngcontent-ng-c713332739="" fill="#000000" height="15px" viewbox="0 0 330 330" width="15px" xmlns="http://www.w3.org/2000/svg"><path _ngcontent-ng-c713332739="" d="M325.606,229.393l-150.004-150C172.79,76.58,168.974,75,164.996,75c-3.979,0-7.794,1.581-10.607,4.394l-149.996,150c-5.858,5.858-5.858,15.355,0,21.213c5.857,5.857,15.355,5.858,21.213,0l139.39-139.393l139.397,139.393C307.322,253.536,311.161,255,315,255c3.839,0,7.678-1.464,10.607-4.394C331.464,244.748,331.464,235.251,325.606,229.393z"></path></svg>
									</span>
								</div>
								<div _ngcontent-ng-c713332739="" class="shipping-fee">
									<?php
									printf(
										/* translators: %d: cart item count */
										esc_html__( 'Total Item: (%d)', 'kids-shop' ),
										(int) $cart_count
									);
									?>
								</div>
							</div>
							<div _ngcontent-ng-c713332739="" class="kids-shop-order-details">
								<div _ngcontent-ng-c713332739="" class="modal-content">
									<div _ngcontent-ng-c713332739="" class="cart-items-heading">
										<h3 _ngcontent-ng-c713332739=""><?php esc_html_e( 'Order Summary', 'kids-shop' ); ?></h3>
									</div>
									<div _ngcontent-ng-c713332739="" class="checkout-border-element"></div>
									<div _ngcontent-ng-c713332739="" class="cost-summary">
										<div _ngcontent-ng-c713332739="" class="summary-item">
											<span _ngcontent-ng-c713332739=""><?php esc_html_e( 'Sub Total', 'kids-shop' ); ?></span>
											<span _ngcontent-ng-c713332739=""><?php echo wp_kses_post( kids_shop_cart_format_price( $regular_total ) ); ?></span>
										</div>
										<?php if ( $discount_total > 0 ) : ?>
											<div _ngcontent-ng-c713332739="" class="summary-item">
												<span _ngcontent-ng-c713332739=""><?php esc_html_e( 'Discount', 'kids-shop' ); ?></span>
												<span _ngcontent-ng-c713332739=""><?php echo wp_kses_post( kids_shop_cart_format_price( $discount_total ) ); ?></span>
											</div>
										<?php endif; ?>
										<div _ngcontent-ng-c713332739="" class="summary-item total">
											<span _ngcontent-ng-c713332739=""><?php esc_html_e( 'GrandTotal', 'kids-shop' ); ?></span>
											<span _ngcontent-ng-c713332739=""><?php echo wp_kses_post( $grand_total ); ?></span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<a _ngcontent-ng-c713332739="" class="btn mb-btn" href="<?php echo esc_url( $checkout_url ); ?>"><?php esc_html_e( 'Checkout', 'kids-shop' ); ?></a>
					</div>
				</div>
			</div>
		</div>
	</app-cart>

	<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
	<?php do_action( 'woocommerce_after_cart' ); ?>
</form>

<?php
$suggestions = kids_shop_get_cart_suggestion_products( 5 );
if ( ! empty( $suggestions ) ) :
	?>
	<div _ngcontent-ng-c713332739="" class="container ng-star-inserted kids-shop-cart-suggestions">
		<div _ngcontent-ng-c713332739="" class="suggestion-section">
			<h3 _ngcontent-ng-c713332739="" class="title"><?php esc_html_e( 'You May Like', 'kids-shop' ); ?></h3>
			<div _ngcontent-ng-c713332739="" class="suggestion-border-element"></div>
			<div _ngcontent-ng-c713332739="" class="products-cards">
				<?php
				foreach ( $suggestions as $product ) {
					$GLOBALS['product'] = $product;
					get_template_part( 'template-parts/shop/product', 'card' );
				}
				wp_reset_postdata();
				?>
			</div>
		</div>
	</div>
	<?php
endif;
