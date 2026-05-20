<?php
/**
 * Single product — WooCommerce loop with shop layout (sidebar + main).
 *
 * @package Kids_Shop
 * @see https://woocommerce.com/document/template-structure/
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="kids-shop-single-product-page-wrap">
	<div class="container">
		<?php
		while ( have_posts() ) {
			the_post();
			wc_get_template_part( 'content', 'single-product' );
		}
		?>
	</div>
</div>
<?php get_footer(); ?>
