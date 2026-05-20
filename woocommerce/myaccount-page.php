<?php
/**
 * My Account page template (full page).
 *
 * @package Kids_Shop
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="site-main kids-shop-myaccount">
	<div class="kids-shop-myaccount__container">
		<?php
		if ( function_exists( 'wc_print_notices' ) ) {
			wc_print_notices();
		}

		if ( is_user_logged_in() ) {
			get_template_part( 'template-parts/myaccount/layout' );
		} else {
			?>
			<div class="kids-shop-myaccount-guest">
				<?php echo do_shortcode( '[woocommerce_my_account]' ); ?>
			</div>
			<?php
		}
		?>
	</div>
</main>
<?php
get_footer();
