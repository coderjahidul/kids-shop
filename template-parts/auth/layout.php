<?php
/**
 * Shared auth page layout — centered card (KiddoMart reference).
 *
 * @package Kids_Shop
 *
 * @var string $auth_variant  login|signup
 * @var string $auth_title    Card title.
 * @var string $auth_subtitle Card subtitle.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Variables come from get_template_part() $args; fall back to current page slug.
if ( empty( $auth_variant ) ) {
	if ( is_page( 'signup' ) ) {
		$auth_variant = 'signup';
	} else {
		$auth_variant = 'login';
	}
}

$auth_title    = isset( $auth_title ) ? $auth_title : ( 'signup' === $auth_variant ? __( 'Signup', 'kids-shop' ) : __( 'Login', 'kids-shop' ) );
$auth_subtitle = isset( $auth_subtitle ) ? $auth_subtitle : '';
?>
<main class="kids-shop-auth-page kids-shop-auth-page--<?php echo esc_attr( $auth_variant ); ?>">
	<div class="kids-shop-auth-page__wrap">
		<div class="kids-shop-auth-card">
			<?php
			get_template_part(
				'template-parts/auth/card',
				'header',
				array(
					'auth_title'    => $auth_title,
					'auth_subtitle' => $auth_subtitle,
				)
			);
			?>

			<?php kids_shop_print_auth_notices(); ?>

			<?php
			if ( 'signup' === $auth_variant ) {
				get_template_part( 'template-parts/auth/signup', 'form' );
			} else {
				get_template_part( 'template-parts/auth/login', 'form' );
			}
			?>
		</div>
	</div>
</main>
