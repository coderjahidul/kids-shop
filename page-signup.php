<?php
/**
 * Sign up page template (slug: signup).
 *
 * @package Kids_Shop
 */

get_header();

get_template_part(
	'template-parts/auth/layout',
	'',
	array(
		'auth_variant'  => 'signup',
		'auth_title'    => __( 'Signup', 'kids-shop' ),
		'auth_subtitle' => __( 'Please provide the information below to get started.', 'kids-shop' ),
	)
);

get_footer();
