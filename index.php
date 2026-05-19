<?php
/**
 * Main template fallback.
 *
 * @package Kids_Shop
 */

if ( is_front_page() ) {
	load_template( get_template_directory() . '/front-page.php' );
	return;
}

get_header();
?>

<main id="primary" class="site-main">
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			the_content();
		}
	}
	?>
</main>

<?php
get_footer();
