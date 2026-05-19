<?php
/**
 * Shop / category archive — Kiddo Mart layout.
 *
 * @package Kids_Shop
 */

defined( 'ABSPATH' ) || exit;

get_header();

$active_term = kids_shop_get_active_category();
?>
<app-products _nghost-ng-c2225765461="" class="ng-star-inserted kids-shop-products-page">
	<div _ngcontent-ng-c2225765461="" class="container">
		<div _ngcontent-ng-c2225765461="" class="left-area">
			<div _ngcontent-ng-c2225765461="" class="sidebar" id="kids-shop-category-sidebar">
				<div _ngcontent-ng-c2225765461="" class="slide-top kids-shop-sidebar-close">
					<span _ngcontent-ng-c2225765461="">X</span>
				</div>
				<?php get_template_part( 'template-parts/shop/category', 'sidebar' ); ?>
			</div>
		</div>
		<div _ngcontent-ng-c2225765461="" class="content">
			<div _ngcontent-ng-c2225765461="" class="top-header">
				<div _ngcontent-ng-c2225765461="" class="results-info">
					<?php if ( $active_term ) : ?>
						<h2 class="kids-shop-category-title"><?php echo esc_html( $active_term->name ); ?></h2>
					<?php endif; ?>
				</div>
				<div _ngcontent-ng-c2225765461="" class="category-controls">
					<button type="button" _ngcontent-ng-c2225765461="" class="filter-mobile web-d-none kids-shop-open-sidebar" aria-label="<?php esc_attr_e( 'Open categories', 'kids-shop' ); ?>">
						<svg _ngcontent-ng-c2225765461="" data-name="Layer 1" id="Layer_1" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path _ngcontent-ng-c2225765461="" d="m14.221,13h9.779v1h-10.636l.857-1Zm2.572-3l-.857,1h8.065v-1h-7.207Zm.207-3.315l-6,7v8.315l-5-3.75v-4.565L0,6.685v-2.185c0-1.378,1.122-2.5,2.5-2.5h12c1.378,0,2.5,1.122,2.5,2.5v2.185Zm-1-2.185c0-.827-.673-1.5-1.5-1.5H2.5c-.827,0-1.5.673-1.5,1.5v1.815l6,7v4.435l3,2.25v-6.685l6-7v-1.815Zm-3,15.5h11v-1h-11v1Zm0-3h11v-1h-11v1Z"></path></svg>
					</button>
				</div>
			</div>
			<div _ngcontent-ng-c2225765461="" class="products-cards">
				<?php if ( woocommerce_product_loop() ) : ?>
					<?php
					while ( have_posts() ) {
						the_post();
						wc_get_template_part( 'content', 'product' );
					}
					?>
				<?php else : ?>
					<div class="kids-shop-no-products">
						<p><?php esc_html_e( 'No products found in this category.', 'kids-shop' ); ?></p>
						<a class="clear-btn" href="<?php echo esc_url( kids_shop_get_products_url() ); ?>"><?php esc_html_e( 'View all products', 'kids-shop' ); ?></a>
					</div>
				<?php endif; ?>
			</div>
			<?php
			/**
			 * Pagination.
			 */
			if ( woocommerce_product_loop() ) {
				echo '<nav class="kids-shop-pagination">';
				woocommerce_pagination();
				echo '</nav>';
			}
			?>
		</div>
	</div>
</app-products>
<?php
get_footer();
