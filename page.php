<?php
/**
 * Default page template.
 *
 * @package Kids_Shop
 */

get_header();
?>

<main id="primary" class="site-main kids-shop-page">
	<div class="container">
		<?php
		if ( function_exists( 'wc_print_notices' ) ) {
			wc_print_notices();
		}

		while ( have_posts() ) {
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php if ( ! is_account_page() ) : ?>
					<header class="kids-shop-page__header">
						<h1 class="kids-shop-page__title"><?php the_title(); ?></h1>
					</header>
				<?php endif; ?>
				<div class="kids-shop-page__content entry-content">
					<?php the_content(); ?>
				</div>
			</article>
			<?php
		}
		?>
	</div>
</main>

<?php
get_footer();
