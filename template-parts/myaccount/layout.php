<?php
/**
 * My Account three-column layout.
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$endpoint    = kids_shop_get_account_endpoint();
$two_column  = function_exists( 'kids_shop_account_is_two_column_layout' ) && kids_shop_account_is_two_column_layout();
$grid_class  = 'kids-shop-myaccount__grid' . ( $two_column ? ' kids-shop-myaccount__grid--two-col' : '' );
$main_class  = 'kids-shop-myaccount__main' . ( $two_column ? ' kids-shop-myaccount__main--wide' : '' );
$ep_class    = 'kids-shop-myaccount__endpoint woocommerce-MyAccount-content kids-shop-myaccount__endpoint--' . esc_attr( $endpoint );
$addr_type   = function_exists( 'kids_shop_get_account_address_type' ) ? kids_shop_get_account_address_type() : '';
if ( 'edit-address' === $endpoint && $addr_type ) {
	$ep_class .= ' kids-shop-myaccount__endpoint--edit-address-' . esc_attr( $addr_type );
}
?>
<div class="<?php echo esc_attr( $grid_class ); ?>">
	<aside class="kids-shop-myaccount__nav" aria-label="<?php esc_attr_e( 'Account menu', 'kids-shop' ); ?>">
		<?php get_template_part( 'template-parts/myaccount/navigation' ); ?>
	</aside>

	<div class="<?php echo esc_attr( $main_class ); ?>">
		<?php
		if ( kids_shop_is_account_dashboard() ) {
			get_template_part( 'template-parts/myaccount/dashboard' );
		} else {
			?>
			<div class="<?php echo esc_attr( $ep_class ); ?>">
				<?php do_action( 'woocommerce_account_content' ); ?>
			</div>
			<?php
		}
		?>
	</div>

	<?php if ( ! $two_column ) : ?>
		<aside class="kids-shop-myaccount__profile">
			<?php get_template_part( 'template-parts/myaccount/profile', 'card' ); ?>
		</aside>
	<?php endif; ?>
</div>
