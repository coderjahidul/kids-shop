<?php
/**
 * Auth card header (icon + title + subtitle).
 *
 * @package Kids_Shop
 *
 * @var string $auth_title    Card title.
 * @var string $auth_subtitle Card subtitle.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$auth_title    = isset( $auth_title ) ? $auth_title : '';
$auth_subtitle = isset( $auth_subtitle ) ? $auth_subtitle : '';
?>
<div class="kids-shop-auth-card__icon" aria-hidden="true">
	<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm0 2.18l6 2.25v4.66c0 3.83-2.6 7.42-6 8.41-3.4-.99-6-4.58-6-8.41V6.43l6-2.25zM11 10h2v5h-2v-5zm0-3h2v2h-2V7z" fill="#fff"/>
	</svg>
</div>
<?php if ( $auth_title ) : ?>
	<h1 class="kids-shop-auth-card__title"><?php echo esc_html( $auth_title ); ?></h1>
<?php endif; ?>
<?php if ( $auth_subtitle ) : ?>
	<p class="kids-shop-auth-card__subtitle"><?php echo esc_html( $auth_subtitle ); ?></p>
<?php endif; ?>
