<?php
/**
 * Cart page template (full page).
 *
 * @package Kids_Shop
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="kids-shop-cart-page">
	<?php
	if ( function_exists( 'wc_print_notices' ) ) {
		wc_print_notices();
	}

	if ( ! WC()->cart || WC()->cart->is_empty() ) {
		get_template_part( 'template-parts/cart/empty' );
	} else {
		get_template_part( 'template-parts/cart/content' );
	}
	?>
</main>
<?php
get_footer();
