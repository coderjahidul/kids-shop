<?php
/**
 * Product row — shared grid (cart "You May Like", single product "Related Products").
 *
 * @package Kids_Shop
 *
 * @var string       $section_title  Section heading.
 * @var WC_Product[] $products       Products to render.
 * @var string       $wrapper_class  Extra wrapper classes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $args ) && is_array( $args ) ) {
	$section_title = isset( $args['section_title'] ) ? $args['section_title'] : '';
	$products      = isset( $args['products'] ) ? $args['products'] : array();
	$wrapper_class = isset( $args['wrapper_class'] ) ? $args['wrapper_class'] : 'kids-shop-product-row';
	$with_panel    = ! empty( $args['with_panel'] );
}

$section_title = isset( $section_title ) ? $section_title : '';
$products      = isset( $products ) && is_array( $products ) ? $products : array();
$wrapper_class = isset( $wrapper_class ) ? trim( $wrapper_class ) : 'kids-shop-product-row';
$with_panel    = isset( $with_panel ) ? (bool) $with_panel : false;

if ( empty( $products ) ) {
	return;
}
?>
<div class="container ng-star-inserted kids-shop-product-row <?php echo esc_attr( $wrapper_class ); ?>">
	<?php if ( $with_panel ) : ?>
	<div class="container">
		<div class="kids-shop-product-row-panel">
		<?php endif; ?>
			<div class="suggestion-section">
				<?php if ( $section_title ) : ?>
					<h3 class="title kids-shop-product-row__title"><?php echo esc_html( $section_title ); ?></h3>
					<div class="suggestion-border-element"></div>
				<?php endif; ?>
				<div class="products-cards">
					<?php
					foreach ( $products as $product ) {
						$GLOBALS['product'] = $product;
						get_template_part( 'template-parts/shop/product', 'card' );
					}
					wp_reset_postdata();
					?>
				</div>
			</div>
		<?php if ( $with_panel ) : ?>
		</div>
	</div>
	<?php endif; ?>
</div>
