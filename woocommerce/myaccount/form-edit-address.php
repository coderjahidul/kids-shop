<?php
/**
 * Edit address form — KiddoMart-style layout.
 *
 * @package Kids_Shop
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_edit_account_address_form' );

if ( ! $load_address ) {
	wc_get_template( 'myaccount/my-address.php' );
	return;
}

$page_title = ( 'billing' === $load_address ) ? esc_html__( 'Billing address', 'woocommerce' ) : esc_html__( 'Shipping address', 'woocommerce' );
$page_title = apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address );

$base     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '';
$back_url = $base ? wc_get_endpoint_url( 'edit-address', '', $base ) : '';
?>
<div class="kids-shop-edit-address-form kids-shop-edit-address-form--<?php echo esc_attr( $load_address ); ?>">
	<?php if ( $back_url ) : ?>
		<a class="kids-shop-edit-address-form__back" href="<?php echo esc_url( $back_url ); ?>">
			<span class="kids-shop-edit-address-form__back-icon" aria-hidden="true">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
			</span>
			<?php esc_html_e( 'Back to addresses', 'kids-shop' ); ?>
		</a>
	<?php endif; ?>

	<header class="kids-shop-edit-address-form__header">
		<span class="kids-shop-edit-address-form__header-icon" aria-hidden="true">
			<?php if ( 'billing' === $load_address ) : ?>
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
			<?php else : ?>
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
			<?php endif; ?>
		</span>
		<div class="kids-shop-edit-address-form__header-text">
			<h1 class="kids-shop-edit-address-form__title"><?php echo esc_html( $page_title ); ?></h1>
			<p class="kids-shop-edit-address-form__subtitle">
				<?php
				echo 'billing' === $load_address
					? esc_html__( 'Update your billing details. These are used at checkout and on invoices.', 'kids-shop' )
					: esc_html__( 'Update where we should deliver your orders.', 'kids-shop' );
				?>
			</p>
		</div>
	</header>

	<form class="kids-shop-edit-address-form__form" method="post" novalidate>
		<div class="woocommerce-address-fields">
			<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

			<div class="woocommerce-address-fields__field-wrapper kids-shop-edit-address-form__fields">
				<?php
				foreach ( $address as $key => $field ) {
					woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
				}
				?>
			</div>

			<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

			<div class="kids-shop-edit-address-form__actions">
				<button type="submit" class="kids-shop-edit-address-form__submit button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_address" value="<?php esc_attr_e( 'Save address', 'woocommerce' ); ?>">
					<?php esc_html_e( 'Save address', 'woocommerce' ); ?>
				</button>
				<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
				<input type="hidden" name="action" value="edit_address" />
			</div>
		</div>
	</form>
</div>

<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>
