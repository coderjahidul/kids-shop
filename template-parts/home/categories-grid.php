<?php
/**
 * Home categories marquee grid.
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories = kids_shop_get_home_categories();
if ( empty( $categories ) ) {
	return;
}

$shop_url = kids_shop_get_products_url();
$count    = count( $categories );
$duration = max( 12, min( 36, $count * 1.5 ) );
?>
<app-categories _ngcontent-ng-c1450992309="" _nghost-ng-c1492694961="">
	<div _ngcontent-ng-c1492694961="" class="container">
		<div _ngcontent-ng-c1492694961="" class="categories-header">
			<h2 _ngcontent-ng-c1492694961=""><?php esc_html_e( 'Categories', 'kids-shop' ); ?></h2>
			<a _ngcontent-ng-c1492694961="" href="<?php echo esc_url( $shop_url ); ?>">
				<?php esc_html_e( 'View All', 'kids-shop' ); ?>
				<svg _ngcontent-ng-c1492694961="" class="arrow-icon" fill="#5f6368" height="24px" viewbox="0 0 24 24" width="24px" xmlns="http://www.w3.org/2000/svg"><path _ngcontent-ng-c1492694961="" d="M0 0h24v24H0z" fill="none"></path><path _ngcontent-ng-c1492694961="" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"></path></svg>
			</a>
		</div>
		<div _ngcontent-ng-c1492694961="" class="categories-row">
			<div _ngcontent-ng-c1492694961="" class="categories-marquee-wrapper">
				<div _ngcontent-ng-c1492694961="" class="categories-row-container" style="--marquee-duration: <?php echo esc_attr( $duration ); ?>s;">
					<?php foreach ( $categories as $term ) : ?>
						<?php
						$cat_url = kids_shop_get_products_url( $term->slug );
						$img_url = kids_shop_get_category_image_url( $term );
						?>
						<div _ngcontent-ng-c1492694961="" class="category-row-item">
							<app-categories-card _ngcontent-ng-c1492694961="" _nghost-ng-c159124279="">
								<a _ngcontent-ng-c159124279="" class="card-container" href="<?php echo esc_url( $cat_url ); ?>">
									<div _ngcontent-ng-c159124279="" class="card-icon">
										<img _ngcontent-ng-c159124279="" alt="<?php echo esc_attr( $term->name ); ?>" loading="lazy" src="<?php echo esc_url( $img_url ); ?>" width="207" height="207"/>
									</div>
									<div _ngcontent-ng-c159124279="" class="card-text"><?php echo esc_html( $term->name ); ?></div>
								</a>
							</app-categories-card>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</app-categories>
