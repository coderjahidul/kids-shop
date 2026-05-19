<?php
/**
 * Kids Shop theme functions.
 *
 * @package Kids_Shop
 */

function kids_shop_enqueue_styles() {
	$theme_version = wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'kids-shop-style', get_stylesheet_uri(), array(), $theme_version );

	$logo_css = get_template_directory() . '/assets/kids-shop-logo.css';
	wp_enqueue_style(
		'kids-shop-logo',
		get_template_directory_uri() . '/assets/kids-shop-logo.css',
		array( 'kids-shop-style' ),
		file_exists( $logo_css ) ? (string) filemtime( $logo_css ) : $theme_version
	);

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

	register_nav_menus(
		array(
			'footer-quick-links'  => __( 'Footer — Quick Links', 'kids-shop' ),
			'footer-useful-links' => __( 'Footer — Useful Links', 'kids-shop' ),
		)
	);

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
require get_template_directory() . '/inc/header-search.php';
require get_template_directory() . '/inc/shop-helpers.php';
require get_template_directory() . '/inc/home-helpers.php';
require get_template_directory() . '/inc/cart-helpers.php';

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

	$home_css = get_template_directory() . '/assets/kids-shop-home.css';
	wp_enqueue_style(
		'kids-shop-home',
		get_template_directory_uri() . '/assets/kids-shop-home.css',
		array( 'kids-shop-style' ),
		file_exists( $home_css ) ? (string) filemtime( $home_css ) : wp_get_theme()->get( 'Version' )
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

	$home_js = get_template_directory() . '/assets/home.js';
	wp_enqueue_script(
		'kids-shop-home-js',
		get_template_directory_uri() . '/assets/home.js',
		$deps,
		file_exists( $home_js ) ? (string) filemtime( $home_js ) : wp_get_theme()->get( 'Version' ),
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
 * Products per page (Theme Settings → Shop).
 */
function kids_shop_products_per_page() {
	return (int) kids_shop_get_option( 'shop_products_per_page', 12 );
}
add_filter( 'loop_shop_per_page', 'kids_shop_products_per_page' );

/**
 * Always apply theme products-per-page on shop archives.
 *
 * WooCommerce skips loop_shop_per_page when posts_per_page is already set
 * (e.g. WordPress Reading → "Blog pages show at most 20 posts").
 */
function kids_shop_apply_shop_products_per_page( $query ) {
	$query->set( 'posts_per_page', kids_shop_products_per_page() );
}
add_action( 'woocommerce_product_query', 'kids_shop_apply_shop_products_per_page' );

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
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		$classes[] = 'kids-shop-cart-page';
	}
	return $classes;
}
add_filter( 'body_class', 'kids_shop_body_class' );

/**
 * Use theme cart layout on the WooCommerce cart page.
 *
 * @param string $template Path to template.
 * @return string
 */
function kids_shop_cart_page_template( $template ) {
	if ( function_exists( 'is_cart' ) && is_cart() && ! is_admin() ) {
		$custom = get_template_directory() . '/woocommerce/cart-page.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	return $template;
}
add_filter( 'template_include', 'kids_shop_cart_page_template', 99 );

/**
 * Enqueue cart page assets.
 */
function kids_shop_enqueue_cart_assets() {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return;
	}

	$theme_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'kids-shop-shop',
		get_template_directory_uri() . '/assets/kids-shop-shop.css',
		array( 'kids-shop-style' ),
		$theme_version
	);

	$cart_css = get_template_directory() . '/assets/kids-shop-cart.css';
	wp_enqueue_style(
		'kids-shop-cart',
		get_template_directory_uri() . '/assets/kids-shop-cart.css',
		array( 'kids-shop-style' ),
		file_exists( $cart_css ) ? (string) filemtime( $cart_css ) : $theme_version
	);

	wp_enqueue_script( 'wc-cart' );
	wp_enqueue_script( 'wc-cart-fragments' );

	$cart_js = get_template_directory() . '/assets/cart.js';
	wp_enqueue_script(
		'kids-shop-cart-js',
		get_template_directory_uri() . '/assets/cart.js',
		array( 'jquery', 'wc-cart', 'wc-cart-fragments' ),
		file_exists( $cart_js ) ? (string) filemtime( $cart_js ) : $theme_version,
		true
	);

	if ( WC()->cart ) {
		$count      = WC()->cart->get_cart_contents_count();
		$items_text = esc_js(
			sprintf(
				/* translators: %d: item count */
				_n( '%d Item', '%d Items', $count, 'kids-shop' ),
				$count
			)
		);
		$total_html = wp_kses_post( WC()->cart->get_cart_total() );

		wp_add_inline_script(
			'kids-shop-cart-js',
			'jQuery(function($){$(".cart-button span").text(' . (int) $count . ');$(".cart-fixed-box .cart-box-top span").text(' . wp_json_encode( $items_text ) . ');$(".cart-price span").html(' . wp_json_encode( $total_html ) . ');});',
			'after'
		);
	}
}
add_action( 'wp_enqueue_scripts', 'kids_shop_enqueue_cart_assets', 20 );
