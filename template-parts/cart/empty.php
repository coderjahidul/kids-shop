<?php
/**
 * Empty cart state.
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>
<app-cart _nghost-ng-c713332739="" class="kids-shop-cart-empty ng-star-inserted">
	<div _ngcontent-ng-c713332739="" class="container">
		<div _ngcontent-ng-c713332739="" class="no-data">
			<app-empty-data _ngcontent-ng-c713332739="" _nghost-ng-c1872529743="">
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
	</div>

	<?php get_template_part( 'template-parts/cart/suggestions' ); ?>
</app-cart>
