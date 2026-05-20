<?php
/**
 * Single product — Kiddo Mart layout (matches reference design) + AJAX add to cart.
 *
 * @package Kids_Shop
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

$product_id     = $product ? $product->get_id() : get_the_ID();
$title          = $product ? $product->get_name() : get_the_title();
$permalink      = get_permalink( $product_id );
$rating_count   = $product ? (int) $product->get_rating_count() : 0;
$average_rating = $product ? (float) $product->get_average_rating() : 0;
$main_image_id  = $product ? $product->get_image_id() : 0;
$main_image     = $main_image_id ? wp_get_attachment_image_url( $main_image_id, 'woocommerce_single' ) : wc_placeholder_img_src();
$main_image_alt = $main_image_id ? (string) get_post_meta( $main_image_id, '_wp_attachment_image_alt', true ) : $title;
if ( '' === trim( $main_image_alt ) ) {
	$main_image_alt = $title;
}

$regular = $product ? $product->get_regular_price() : '';
$sale    = $product ? $product->get_sale_price() : '';
$display = $product ? $product->get_price() : '';

$discount_pct = 0;
$save_amount  = 0;
if ( $product && $product->is_on_sale() && '' !== $regular && '' !== $sale ) {
	$reg_f = (float) $regular;
	$sal_f = (float) $sale;
	if ( $reg_f > 0 && $sal_f < $reg_f ) {
		$discount_pct = (int) round( ( ( $reg_f - $sal_f ) / $reg_f ) * 100 );
		$save_amount    = $reg_f - $sal_f;
	}
}

$min_q = $product ? $product->get_min_purchase_quantity() : 1;
$max_q = $product ? $product->get_max_purchase_quantity() : '';
$max_q = '' !== $max_q ? (int) $max_q : '';

$buy_now_url = $product ? kids_shop_buy_now_url( $product, max( 1, (int) $min_q ) ) : $permalink;

$content_raw = get_post_field( 'post_content', $product_id );
$desc_plain  = wp_strip_all_tags( $content_raw );
$desc_short  = wp_trim_words( $desc_plain, 48, '…' );
$has_more    = ( strlen( $desc_plain ) > strlen( $desc_short ) );

$is_simple_ajax = $product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock();

$svg_cart = '<svg class="kids-shop-sp-btn-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M9 22a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zM1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$svg_bag  = '<svg class="kids-shop-sp-btn-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4H6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
?>
<section id="product-<?php the_ID(); ?>" <?php wc_product_class( 'kids-shop-single-product-card', $product ); ?>>
	<div class="kids-shop-sp-main">
		<div class="kids-shop-sp-left">
			<?php if ( $discount_pct > 0 ) : ?>
				<span class="kids-shop-sp-discount">-<?php echo esc_html( $discount_pct ); ?>% <?php esc_html_e( 'OFF', 'kids-shop' ); ?></span>
			<?php endif; ?>
			<button type="button" class="kids-shop-sp-wishlist" aria-label="<?php esc_attr_e( 'Add to wishlist', 'kids-shop' ); ?>">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke="#e91e63" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
			<img class="kids-shop-sp-image" src="<?php echo esc_url( $main_image ); ?>" alt="<?php echo esc_attr( $main_image_alt ); ?>" loading="eager" decoding="async" width="500" height="500" />
		</div>

		<div class="kids-shop-sp-middle">
			<h1 class="kids-shop-sp-title"><?php echo esc_html( $title ); ?></h1>

			<div class="kids-shop-sp-rating" role="img" aria-label="<?php echo esc_attr( sprintf( __( 'Rated %s out of 5', 'kids-shop' ), $average_rating > 0 ? (string) round( $average_rating, 1 ) : '0' ) ); ?>">
				<span class="kids-shop-sp-stars" aria-hidden="true">
					<?php
					$stars_on = (int) min( 5, max( 0, round( $average_rating ) ) );
					for ( $i = 1; $i <= 5; $i++ ) {
						$cls = ( $i <= $stars_on ) ? 'kids-shop-sp-star kids-shop-sp-star--full' : 'kids-shop-sp-star kids-shop-sp-star--empty';
						echo '<span class="' . esc_attr( $cls ) . '">★</span>';
					}
					?>
				</span>
				<span class="kids-shop-sp-review-count">(<?php echo esc_html( (string) $rating_count ); ?> <?php esc_html_e( 'reviews', 'kids-shop' ); ?>)</span>
			</div>

			<div class="kids-shop-sp-price-block">
				<?php if ( $save_amount > 0 ) : ?>
					<div class="kids-shop-sp-save"><?php echo esc_html( sprintf( __( '%s Off', 'kids-shop' ), kids_shop_format_price( (string) $save_amount ) ) ); ?></div>
				<?php endif; ?>
				<div class="kids-shop-sp-price-row">
					<span class="kids-shop-sp-current"><?php echo esc_html( kids_shop_format_price( $display ) ); ?></span>
					<?php if ( $product && $product->is_on_sale() && '' !== $regular ) : ?>
						<del class="kids-shop-sp-regular"><?php echo esc_html( kids_shop_format_price( $regular ) ); ?></del>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $is_simple_ajax ) : ?>
				<div class="cart kids-shop-sp-cart">
					<div class="kids-shop-sp-qty-box">
						<button type="button" class="kids-shop-sp-qty-minus" aria-label="<?php esc_attr_e( 'Decrease quantity', 'kids-shop' ); ?>">−</button>
						<input
							type="number"
							class="input-text qty text kids-shop-sp-qty"
							name="quantity"
							value="<?php echo esc_attr( (string) max( 1, (int) $min_q ) ); ?>"
							min="<?php echo esc_attr( (string) max( 1, (int) $min_q ) ); ?>"
							<?php echo $max_q ? 'max="' . esc_attr( (string) $max_q ) . '"' : ''; ?>
							step="1"
						/>
						<button type="button" class="kids-shop-sp-qty-plus" aria-label="<?php esc_attr_e( 'Increase quantity', 'kids-shop' ); ?>">+</button>
					</div>
					<button type="button" class="kids-shop-sp-add kids-shop-single-add-to-cart kids-shop-add-to-cart" data-product_id="<?php echo esc_attr( (string) $product_id ); ?>">
						<?php echo $svg_cart; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'Add to cart', 'kids-shop' ); ?></span>
					</button>
					<a class="kids-shop-sp-buy-now kids-shop-buy-now" href="<?php echo esc_url( $buy_now_url ); ?>" data-product_id="<?php echo esc_attr( (string) $product_id ); ?>">
						<?php echo $svg_bag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'Buy Now', 'kids-shop' ); ?></span>
					</a>
				</div>
			<?php else : ?>
				<div class="kids-shop-sp-cart kids-shop-sp-cart--fallback">
					<?php woocommerce_template_single_add_to_cart(); ?>
					<?php if ( $product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) : ?>
						<a class="kids-shop-sp-buy-now kids-shop-sp-buy-now--solo kids-shop-buy-now" href="<?php echo esc_url( $buy_now_url ); ?>" data-product_id="<?php echo esc_attr( (string) $product_id ); ?>">
							<?php echo $svg_bag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php esc_html_e( 'Buy Now', 'kids-shop' ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="kids-shop-sp-meta">
				<p class="kids-shop-sp-meta-line">
					<span class="kids-shop-sp-meta-label"><?php esc_html_e( 'Category:', 'kids-shop' ); ?></span>
					<?php echo wp_kses_post( wc_get_product_category_list( $product_id, ', ' ) ); ?>
				</p>
				<?php
				$tag_list = wc_get_product_tag_list( $product_id, ', ' );
				if ( $tag_list ) :
					?>
					<p class="kids-shop-sp-meta-line">
						<span class="kids-shop-sp-meta-label"><?php esc_html_e( 'Tags:', 'kids-shop' ); ?></span>
						<?php echo wp_kses_post( $tag_list ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>

		<aside class="kids-shop-sp-right">
			<?php
			$GLOBALS['kids_shop_sidebar_layout'] = 'single-product';
			get_template_part( 'template-parts/shop/category', 'sidebar' );
			unset( $GLOBALS['kids_shop_sidebar_layout'] );
			?>
		</aside>
	</div>

	<div class="kids-shop-sp-tabs">
		<div class="kids-shop-sp-tab-head" role="tablist">
			<button type="button" class="kids-shop-sp-tab is-active" role="tab" aria-selected="true" data-tab="desc"><?php esc_html_e( 'Description', 'kids-shop' ); ?></button>
			<button type="button" class="kids-shop-sp-tab" role="tab" aria-selected="false" data-tab="reviews"><?php echo esc_html( sprintf( __( 'Reviews(%d)', 'kids-shop' ), $rating_count ) ); ?></button>
		</div>
		<div class="kids-shop-sp-tab-panels">
			<div class="kids-shop-sp-panel is-active" id="kids-shop-tab-desc" role="tabpanel">
				<?php if ( '' !== trim( $desc_short ) ) : ?>
					<div class="kids-shop-sp-desc-short"><?php echo esc_html( $desc_short ); ?></div>
				<?php else : ?>
					<p class="kids-shop-sp-desc-empty"><?php esc_html_e( 'No description available.', 'kids-shop' ); ?></p>
				<?php endif; ?>
				<?php if ( $has_more ) : ?>
					<button type="button" class="kids-shop-sp-see-more" data-expand="1" data-more="<?php echo esc_attr__( 'See More', 'kids-shop' ); ?>" data-less="<?php echo esc_attr__( 'See less', 'kids-shop' ); ?>"><?php esc_html_e( 'See More', 'kids-shop' ); ?></button>
					<div class="kids-shop-sp-desc-long" hidden>
						<?php echo apply_filters( 'the_content', $content_raw ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="kids-shop-sp-panel" id="kids-shop-tab-reviews" role="tabpanel" hidden>
				<?php
				if ( $product && function_exists( 'comments_open' ) && comments_open( $product_id ) ) {
					wc_get_template( 'single-product-reviews.php' );
				} else {
					echo '<p class="kids-shop-sp-reviews-closed">' . esc_html__( 'Reviews are closed for this product.', 'kids-shop' ) . '</p>';
				}
				?>
			</div>
		</div>
	</div>

	<?php get_template_part( 'template-parts/shop/related', 'products' ); ?>
</section>

<?php do_action( 'woocommerce_after_single_product' ); ?>
