<?php
/**
 * Home product row section (Winter Collection, Flash Deals, etc.).
 *
 * @package Kids_Shop
 * @var string $section_title
 * @var string $section_view_all
 * @var string $section_view_all_text
 * @var array  $section_query_args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Variables from get_template_part( ..., ..., $args ) (WP 5.5+).
if ( isset( $args ) && is_array( $args ) ) {
	$section_title         = isset( $args['section_title'] ) ? $args['section_title'] : '';
	$section_view_all      = isset( $args['section_view_all'] ) ? $args['section_view_all'] : kids_shop_get_products_url();
	$section_view_all_text = isset( $args['section_view_all_text'] ) ? $args['section_view_all_text'] : __( 'View All', 'kids-shop' );
	$section_query_args    = isset( $args['section_query_args'] ) ? $args['section_query_args'] : array( 'limit' => 5 );
} else {
	$section_title         = isset( $section_title ) ? $section_title : '';
	$section_view_all      = isset( $section_view_all ) ? $section_view_all : kids_shop_get_products_url();
	$section_view_all_text = isset( $section_view_all_text ) ? $section_view_all_text : __( 'View All', 'kids-shop' );
	$section_query_args    = isset( $section_query_args ) ? $section_query_args : array( 'limit' => 5 );
}

$section_view_all_text = '' !== trim( $section_view_all_text ) ? $section_view_all_text : __( 'View All', 'kids-shop' );
$section_view_all      = $section_view_all ? $section_view_all : kids_shop_get_products_url();

$products = kids_shop_get_home_products( $section_query_args );

if ( empty( $products ) ) {
	return;
}
?>
<app-tag-products _ngcontent-ng-c1450992309="" _nghost-ng-c2554372288="" class="kids-shop-home-section">
	<div _ngcontent-ng-c2554372288="" class="container">
		<div _ngcontent-ng-c2554372288="" class="products-header">
			<div _ngcontent-ng-c2554372288="" class="flash-title-counter">
				<h2 _ngcontent-ng-c2554372288=""><?php echo esc_html( $section_title ); ?></h2>
			</div>
			<div _ngcontent-ng-c2554372288="" class="more">
				<a _ngcontent-ng-c2554372288="" href="<?php echo esc_url( $section_view_all ); ?>">
					<?php echo esc_html( $section_view_all_text ); ?>
					<svg _ngcontent-ng-c2554372288="" class="arrow-icon" fill="black" height="24px" viewbox="0 0 24 24" width="24px" xmlns="http://www.w3.org/2000/svg"><path _ngcontent-ng-c2554372288="" d="M0 0h24v24H0z" fill="none"></path><path _ngcontent-ng-c2554372288="" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"></path></svg>
				</a>
			</div>
		</div>
		<div _ngcontent-ng-c2554372288="" class="products-cards">
			<?php
			foreach ( $products as $product ) {
				$GLOBALS['product'] = $product;
				get_template_part( 'template-parts/shop/product', 'card' );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
</app-tag-products>
