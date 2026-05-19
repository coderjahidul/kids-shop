<?php
/**
 * Archive fallback — shop layout for product archives when WooCommerce is active.
 *
 * @package Kids_Shop
 */

if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || ( is_post_type_archive( 'product' ) && taxonomy_exists( 'product_cat' ) ) ) ) {
	wc_get_template( 'archive-product.php' );
	return;
}

get_header();
?>
<main class="site-main">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class(); ?>>
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<?php the_excerpt(); ?>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found.', 'kids-shop' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
