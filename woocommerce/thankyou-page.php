<?php
/**
 * Thank you / order received page template (full page).
 *
 * @package Kids_Shop
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="kids-shop-thankyou-page">
	<div class="kids-shop-thankyou-page__wrap">
		<?php
		if ( function_exists( 'wc_print_notices' ) ) {
			wc_print_notices();
		}

		echo do_shortcode( '[woocommerce_checkout]' );
		?>
	</div>
</main>
<?php
get_footer();
