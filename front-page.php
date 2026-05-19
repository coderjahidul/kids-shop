<?php
/**
 * Front page template — dynamic Kiddo Mart home.
 *
 * @package Kids_Shop
 */

get_header();
?>
<app-home _nghost-ng-c1450992309="" class="kids-shop-home">
	<div _ngcontent-ng-c1450992309="" class="home-container">
		<?php get_template_part( 'template-parts/home/hero', 'slider' ); ?>

		<app-banner-1 _ngcontent-ng-c1450992309="" _nghost-ng-c2502834194=""><!-- --></app-banner-1>

		<?php get_template_part( 'template-parts/home/categories', 'grid' ); ?>

		<?php
		$sections = kids_shop_get_home_product_sections();
		foreach ( $sections as $section ) {
			get_template_part(
				'template-parts/home/product',
				'section',
				array(
					'section_title'         => $section['title'],
					'section_view_all'      => $section['view_all'],
					'section_view_all_text' => isset( $section['view_all_text'] ) ? $section['view_all_text'] : __( 'View All', 'kids-shop' ),
					'section_query_args'    => $section['query_args'],
				)
			);
		}
		?>
	</div>
</app-home>
<?php
get_footer();
