<?php
/**
 * Kids Shop theme functions.
 *
 * @package Kids_Shop
 */

function kids_shop_enqueue_styles() {
	wp_enqueue_style( 'kids-shop-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );

	if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_shop() || is_product_taxonomy() ) ) {
		wp_enqueue_style(
			'kids-shop-shop',
			get_template_directory_uri() . '/assets/kids-shop-shop.css',
			array( 'kids-shop-style' ),
			wp_get_theme()->get( 'Version' )
		);

		wp_enqueue_script( 'wc-add-to-cart' );

		wp_enqueue_script(
			'kids-shop-shop-js',
			get_template_directory_uri() . '/assets/shop.js',
			array( 'jquery', 'wc-add-to-cart' ),
			wp_get_theme()->get( 'Version' ),
			true
		);

		wp_localize_script(
			'kids-shop-shop-js',
			'kidsShop',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'cartUrl' => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '',
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'kids_shop_enqueue_styles' );

function kids_shop_setup() {
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 430,
			'single_image_width'    => 700,
			'product_grid'          => array(
				'default_rows'    => 4,
				'min_rows'        => 1,
				'max_rows'        => 8,
				'default_columns' => 3,
				'min_columns'     => 2,
				'max_columns'     => 4,
			),
		)
	);
}
add_action( 'after_setup_theme', 'kids_shop_setup' );

require get_template_directory() . '/inc/theme-options.php';
require get_template_directory() . '/inc/theme-settings.php';
require get_template_directory() . '/inc/template-filters.php';
require get_template_directory() . '/inc/shop-helpers.php';
require get_template_directory() . '/inc/home-helpers.php';

/**
 * Settings shortcut on Themes screen.
 *
 * @param array $links Theme links.
 * @return array
 */
function kids_shop_theme_action_links( $links ) {
	$links[] = '<a href="' . esc_url( admin_url( 'themes.php?page=kids-shop-theme-settings' ) ) . '">' . esc_html__( 'Theme Settings', 'kids-shop' ) . '</a>';
	return $links;
}
add_filter( 'theme_action_links', 'kids_shop_theme_action_links' );

/**
 * Enqueue home page assets.
 */
function kids_shop_enqueue_home_assets() {
	if ( ! is_front_page() ) {
		return;
	}

	wp_enqueue_style(
		'kids-shop-home',
		get_template_directory_uri() . '/assets/kids-shop-home.css',
		array( 'kids-shop-style' ),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_style(
		'kids-shop-shop',
		get_template_directory_uri() . '/assets/kids-shop-shop.css',
		array( 'kids-shop-style' ),
		wp_get_theme()->get( 'Version' )
	);

	$deps = array( 'jquery' );
	if ( function_exists( 'wc_get_products' ) ) {
		wp_enqueue_script( 'wc-add-to-cart' );
		$deps[] = 'wc-add-to-cart';
	}

	wp_enqueue_script(
		'kids-shop-home-js',
		get_template_directory_uri() . '/assets/home.js',
		$deps,
		wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'kids_shop_enqueue_home_assets', 20 );

/**
 * Use 3 columns on shop (matches reference design).
 */
function kids_shop_loop_columns() {
	return 3;
}
add_filter( 'loop_shop_columns', 'kids_shop_loop_columns' );

/**
 * Products per page.
 */
function kids_shop_products_per_page() {
	return (int) kids_shop_get_option( 'shop_products_per_page', 12 );
}
add_filter( 'loop_shop_per_page', 'kids_shop_products_per_page' );

/**
 * Shop page body class for layout CSS.
 */
function kids_shop_body_class( $classes ) {
	if ( function_exists( 'is_woocommerce' ) && ( is_shop() || is_product_taxonomy() ) ) {
		$classes[] = 'kids-shop-archive';
	}
	if ( is_front_page() ) {
		$classes[] = 'kids-shop-home-page';
	}
	return $classes;
}
add_filter( 'body_class', 'kids_shop_body_class' );
