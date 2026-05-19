<?php
/**
 * Product card (Kiddo Mart design).
 *
 * @package Kids_Shop
 * @var WC_Product $product
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
	return;
}

$product_id   = $product->get_id();
$permalink    = get_permalink( $product_id );
$title        = $product->get_name();
$image_id     = $product->get_image_id();
$image_url    = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' ) : wc_placeholder_img_src();
$rating_count = $product->get_rating_count();
$avg_rating   = $product->get_average_rating();
$rating_label = $avg_rating > 0 ? round( $avg_rating, 1 ) : '0';
$sold_count   = kids_shop_get_sold_count( $product );
$on_sale      = $product->is_on_sale();
$regular      = $product->get_regular_price();
$sale_price   = $product->get_sale_price();
$display      = $product->get_price();
$buy_now_url  = $product->is_type( 'simple' ) ? $product->add_to_cart_url() . '&quantity=1' : $permalink;
?>
<app-product-card-1 _nghost-ng-c4050667118="" class="ng-star-inserted" data-product-id="<?php echo esc_attr( $product_id ); ?>">
	<div _ngcontent-ng-c4050667118="" class="product-card-wrapper">
		<div _ngcontent-ng-c4050667118="" class="product-card">
			<button _ngcontent-ng-c4050667118="" type="button" class="wishlist-icon" aria-label="<?php esc_attr_e( 'Add to wishlist', 'kids-shop' ); ?>">
				<svg _ngcontent-ng-c4050667118="" fill="none" height="20" stroke="#e91e63" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg"><path _ngcontent-ng-c4050667118="" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
			</button>
			<?php if ( $on_sale ) : ?>
				<div _ngcontent-ng-c4050667118="" class="sale-badge ng-star-inserted"><?php esc_html_e( 'SALE', 'kids-shop' ); ?></div>
			<?php endif; ?>
			<a _ngcontent-ng-c4050667118="" class="product-image-link" href="<?php echo esc_url( $permalink ); ?>">
				<div _ngcontent-ng-c4050667118="" class="product-image-wrapper">
					<img _ngcontent-ng-c4050667118="" alt="<?php echo esc_attr( $title ); ?>" class="product-image" loading="lazy" src="<?php echo esc_url( $image_url ); ?>" width="430" height="491"/>
				</div>
			</a>
			<div _ngcontent-ng-c4050667118="" class="product-details">
				<a _ngcontent-ng-c4050667118="" class="product-name-link" href="<?php echo esc_url( $permalink ); ?>">
					<h3 _ngcontent-ng-c4050667118="" class="product-name"><?php echo esc_html( $title ); ?></h3>
				</a>
				<div _ngcontent-ng-c4050667118="" class="rating-sold-section">
					<span _ngcontent-ng-c4050667118="" class="rating">
						<svg _ngcontent-ng-c4050667118="" fill="#ffc107" height="12" stroke="none" viewbox="0 0 24 24" width="12" xmlns="http://www.w3.org/2000/svg"><path _ngcontent-ng-c4050667118="" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
						(<?php echo esc_html( $rating_label ); ?>)
					</span>
					<span _ngcontent-ng-c4050667118="" class="separator">|</span>
					<span _ngcontent-ng-c4050667118="" class="sold-count"><?php echo esc_html( $sold_count ); ?> <?php esc_html_e( 'Sold', 'kids-shop' ); ?></span>
				</div>
				<div _ngcontent-ng-c4050667118="" class="price-section">
					<?php if ( $on_sale && $regular ) : ?>
						<span _ngcontent-ng-c4050667118="" class="old-price ng-star-inserted"><?php echo esc_html( kids_shop_format_price( $regular ) ); ?></span>
					<?php endif; ?>
					<span _ngcontent-ng-c4050667118="" class="new-price"><?php echo esc_html( kids_shop_format_price( $display ) ); ?></span>
				</div>
				<div _ngcontent-ng-c4050667118="" class="action-buttons">
					<a _ngcontent-ng-c4050667118="" class="buy-now-btn ng-star-inserted" href="<?php echo esc_url( $buy_now_url ); ?>"><?php esc_html_e( 'Buy Now', 'kids-shop' ); ?></a>
					<?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
						<div _ngcontent-ng-c4050667118="" class="add-cart-wrapper ng-star-inserted">
							<button _ngcontent-ng-c4050667118="" type="button" class="add-cart-icon-btn kids-shop-add-to-cart" data-product_id="<?php echo esc_attr( $product_id ); ?>" aria-label="<?php esc_attr_e( 'Add to cart', 'kids-shop' ); ?>">
								<svg _ngcontent-ng-c4050667118="" fill="none" height="18" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><circle _ngcontent-ng-c4050667118="" cx="9" cy="21" r="1"></circle><circle _ngcontent-ng-c4050667118="" cx="20" cy="21" r="1"></circle><path _ngcontent-ng-c4050667118="" d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
							</button>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</app-product-card-1>
