<?php
/**
 * Reusable account empty state.
 *
 * @package Kids_Shop
 *
 * @var string $empty_title   Heading.
 * @var string $empty_message Optional subtext.
 * @var string $empty_action  Button label.
 * @var string $empty_url     Button URL.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$empty_title   = isset( $empty_title ) ? $empty_title : '';
$empty_message = isset( $empty_message ) ? $empty_message : '';
$empty_action  = isset( $empty_action ) ? $empty_action : '';
$empty_url     = isset( $empty_url ) ? $empty_url : '';
?>
<div class="kids-shop-myaccount-empty">
	<?php echo kids_shop_account_empty_illustration(); ?>
	<?php if ( $empty_title ) : ?>
		<p class="kids-shop-myaccount-empty__title"><?php echo esc_html( $empty_title ); ?></p>
	<?php endif; ?>
	<?php if ( $empty_message ) : ?>
		<p class="kids-shop-myaccount-empty__message"><?php echo esc_html( $empty_message ); ?></p>
	<?php endif; ?>
	<?php if ( $empty_action && $empty_url ) : ?>
		<a class="kids-shop-myaccount-btn kids-shop-myaccount-btn--outline" href="<?php echo esc_url( $empty_url ); ?>">
			<?php echo esc_html( $empty_action ); ?>
		</a>
	<?php endif; ?>
</div>
