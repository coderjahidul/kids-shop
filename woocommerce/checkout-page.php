<?php
/**
 * Checkout page template (full page).
 *
 * @package Kids_Shop
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="kids-shop-checkout-page-wrap kids-shop-checkout-page">
	<?php
	if ( function_exists( 'wc_print_notices' ) ) {
		wc_print_notices();
	}

	$checkout = WC()->checkout();
	wc_get_template(
		'checkout/form-checkout.php',
		array(
			'checkout' => $checkout,
		)
	);
	?>
</main>
<?php
get_footer();
