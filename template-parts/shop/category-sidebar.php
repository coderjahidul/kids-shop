<?php
/**
 * Category sidebar (Kiddo Mart design).
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$kids_shop_sidebar_layout = isset( $GLOBALS['kids_shop_sidebar_layout'] ) ? (string) $GLOBALS['kids_shop_sidebar_layout'] : 'archive';

$active_slug = kids_shop_get_active_category_slug();
$categories  = kids_shop_get_sidebar_categories();
$shop_url    = kids_shop_get_products_url();
$sidebar_title = ( 'single-product' === $kids_shop_sidebar_layout )
	? __( 'Category', 'kids-shop' )
	: __( 'Categories', 'kids-shop' );
?>
<app-products-category-view _nghost-ng-c1609131114="" class="ng-star-inserted <?php echo 'single-product' === $kids_shop_sidebar_layout ? 'kids-shop-category-sidebar--single' : ''; ?>">
	<div _ngcontent-ng-c1609131114="" class="nested-category-sidebar">
		<div _ngcontent-ng-c1609131114="" class="sidebar-header">
			<h3 _ngcontent-ng-c1609131114=""><?php echo esc_html( $sidebar_title ); ?></h3>
		</div>
		<div _ngcontent-ng-c1609131114="" class="category-tree">
			<?php foreach ( $categories as $term ) : ?>
				<?php
				$is_selected = ( $active_slug === $term->slug );
				$children    = kids_shop_get_child_categories( $term->term_id );
				$cat_url     = kids_shop_get_products_url( $term->slug );
				$img_url     = kids_shop_get_category_image_url( $term );
				$item_class  = 'category-item' . ( $is_selected ? ' selected' : '' );
				?>
				<div _ngcontent-ng-c1609131114="" class="<?php echo esc_attr( $item_class ); ?> ng-star-inserted">
					<a _ngcontent-ng-c1609131114="" class="category-row category-row-link" href="<?php echo esc_url( $cat_url ); ?>">
						<div _ngcontent-ng-c1609131114="" class="category-content">
							<img _ngcontent-ng-c1609131114="" alt="<?php echo esc_attr( $term->name ); ?>" class="category-image ng-star-inserted" src="<?php echo esc_url( $img_url ); ?>" loading="lazy" width="40" height="40"/>
							<span _ngcontent-ng-c1609131114="" class="category-name"><?php echo esc_html( $term->name ); ?></span>
						</div>
						<?php if ( ! empty( $children ) && ! $is_selected ) : ?>
							<div _ngcontent-ng-c1609131114="" class="expand-icon ng-star-inserted" aria-hidden="true">
								<svg _ngcontent-ng-c1609131114="" height="12" viewbox="0 0 12 12" width="12"><path _ngcontent-ng-c1609131114="" d="M4 2L8 6L4 10" fill="none" stroke="currentColor" stroke-width="2"></path></svg>
							</div>
						<?php endif; ?>
					</a>
					<?php if ( ! empty( $children ) ) : ?>
						<div _ngcontent-ng-c1609131114="" class="category-children" style="<?php echo $is_selected ? '' : 'display:none;'; ?>">
							<?php foreach ( $children as $child ) : ?>
								<?php
								$child_selected = ( $active_slug === $child->slug );
								$child_url      = kids_shop_get_products_url( $child->slug );
								$child_img      = kids_shop_get_category_image_url( $child );
								?>
								<a _ngcontent-ng-c1609131114="" class="category-row category-row-link category-child<?php echo $child_selected ? ' selected' : ''; ?>" href="<?php echo esc_url( $child_url ); ?>">
									<div _ngcontent-ng-c1609131114="" class="category-content">
										<img _ngcontent-ng-c1609131114="" alt="<?php echo esc_attr( $child->name ); ?>" class="category-image" src="<?php echo esc_url( $child_img ); ?>" loading="lazy" width="32" height="32"/>
										<span _ngcontent-ng-c1609131114="" class="category-name"><?php echo esc_html( $child->name ); ?></span>
									</div>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ( $active_slug && 'single-product' !== $kids_shop_sidebar_layout ) : ?>
			<div _ngcontent-ng-c1609131114="" class="clear-filters ng-star-inserted">
				<a _ngcontent-ng-c1609131114="" class="clear-btn" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Clear All Filters', 'kids-shop' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</app-products-category-view>
