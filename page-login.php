<?php
/**
 * Login page template (slug: login).
 *
 * @package Kids_Shop
 */

get_header();

get_template_part(
	'template-parts/auth/layout',
	'',
	array(
		'auth_variant'  => 'login',
		'auth_title'    => __( 'Login', 'kids-shop' ),
		'auth_subtitle' => __( 'Please provide phone no or email to get started.', 'kids-shop' ),
	)
);

get_footer();
