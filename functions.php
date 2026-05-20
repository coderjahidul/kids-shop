<?php
/**
 * Kids Shop theme functions.
 *
 * @package Kids_Shop
 */

function kids_shop_enqueue_styles()
{
	$theme_version = wp_get_theme()->get('Version');
	wp_enqueue_style('kids-shop-style', get_stylesheet_uri(), array(), $theme_version);

	$logo_css = get_template_directory() . '/assets/kids-shop-logo.css';
	wp_enqueue_style(
		'kids-shop-logo',
		get_template_directory_uri() . '/assets/kids-shop-logo.css',
		array('kids-shop-style'),
		file_exists($logo_css) ? (string) filemtime($logo_css) : $theme_version
	);

	$header_cart_css = get_template_directory() . '/assets/kids-shop-header-cart.css';
	wp_enqueue_style(
		'kids-shop-header-cart',
		get_template_directory_uri() . '/assets/kids-shop-header-cart.css',
		array('kids-shop-style'),
		file_exists($header_cart_css) ? (string) filemtime($header_cart_css) : $theme_version
	);

	if (function_exists('is_woocommerce') && (is_woocommerce() || is_shop() || is_product_taxonomy() || (function_exists('is_product') && is_product()))) {
		$shop_css = get_template_directory() . '/assets/kids-shop-shop.css';
		wp_enqueue_style(
			'kids-shop-shop',
			get_template_directory_uri() . '/assets/kids-shop-shop.css',
			array('kids-shop-style'),
			file_exists($shop_css) ? (string) filemtime($shop_css) : wp_get_theme()->get('Version')
		);

		$shop_deps = kids_shop_enqueue_cart_fragment_scripts();
		$shop_js   = get_template_directory() . '/assets/shop.js';

		wp_enqueue_script(
			'kids-shop-shop-js',
			get_template_directory_uri() . '/assets/shop.js',
			$shop_deps,
			file_exists($shop_js) ? (string) filemtime($shop_js) : wp_get_theme()->get('Version'),
			true
		);

		wp_localize_script(
			'kids-shop-shop-js',
			'kidsShop',
			array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'cartUrl' => function_exists('wc_get_cart_url') ? wc_get_cart_url() : '',
				'i18n'    => array(
					'addedToCart' => __( 'Product added to cart!', 'kids-shop' ),
					'viewCart'    => __( 'View Cart', 'kids-shop' ),
					'close'       => __( 'Close', 'kids-shop' ),
					'error'       => __( 'Could not add to cart. Please try again.', 'kids-shop' ),
				),
			)
		);
	}
}
add_action('wp_enqueue_scripts', 'kids_shop_enqueue_styles');

function kids_shop_setup()
{
	add_theme_support('automatic-feed-links');
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');

	register_nav_menus(
		array(
			'footer-quick-links' => __('Footer — Quick Links', 'kids-shop'),
			'footer-useful-links' => __('Footer — Useful Links', 'kids-shop'),
		)
	);

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 430,
			'single_image_width' => 700,
			'product_grid' => array(
				'default_rows' => 4,
				'min_rows' => 1,
				'max_rows' => 8,
				'default_columns' => 3,
				'min_columns' => 2,
				'max_columns' => 4,
			),
		)
	);
}
add_action('after_setup_theme', 'kids_shop_setup');

require get_template_directory() . '/inc/theme-options.php';
require get_template_directory() . '/inc/theme-settings.php';
require get_template_directory() . '/inc/template-filters.php';
require get_template_directory() . '/inc/header-search.php';
require get_template_directory() . '/inc/shop-helpers.php';
require get_template_directory() . '/inc/home-helpers.php';
require get_template_directory() . '/inc/cart-helpers.php';
require get_template_directory() . '/inc/auth-helpers.php';
require get_template_directory() . '/inc/myaccount-helpers.php';

/**
 * Settings shortcut on Themes screen.
 *
 * @param array $links Theme links.
 * @return array
 */
function kids_shop_theme_action_links($links)
{
	$links[] = '<a href="' . esc_url(admin_url('themes.php?page=kids-shop-theme-settings')) . '">' . esc_html__('Theme Settings', 'kids-shop') . '</a>';
	return $links;
}
add_filter('theme_action_links', 'kids_shop_theme_action_links');

/**
 * Enqueue home page assets.
 */
function kids_shop_enqueue_home_assets()
{
	if (!is_front_page()) {
		return;
	}

	$home_css = get_template_directory() . '/assets/kids-shop-home.css';
	wp_enqueue_style(
		'kids-shop-home',
		get_template_directory_uri() . '/assets/kids-shop-home.css',
		array('kids-shop-style'),
		file_exists($home_css) ? (string) filemtime($home_css) : wp_get_theme()->get('Version')
	);

	wp_enqueue_style(
		'kids-shop-shop',
		get_template_directory_uri() . '/assets/kids-shop-shop.css',
		array('kids-shop-style'),
		wp_get_theme()->get('Version')
	);

	$deps = kids_shop_enqueue_cart_fragment_scripts();

	$home_js = get_template_directory() . '/assets/home.js';
	wp_enqueue_script(
		'kids-shop-home-js',
		get_template_directory_uri() . '/assets/home.js',
		$deps,
		file_exists($home_js) ? (string) filemtime($home_js) : wp_get_theme()->get('Version'),
		true
	);

	wp_localize_script(
		'kids-shop-home-js',
		'kidsShopHome',
		array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'cartUrl' => function_exists('wc_get_cart_url') ? wc_get_cart_url() : '',
			'i18n' => array(
				'addedToCart' => __('Product added to cart!', 'kids-shop'),
				'viewCart' => __('View Cart', 'kids-shop'),
				'close' => __('Close', 'kids-shop'),
				'error' => __('Could not add to cart. Please try again.', 'kids-shop'),
			),
		)
	);
}
add_action('wp_enqueue_scripts', 'kids_shop_enqueue_home_assets', 20);

/**
 * Use 3 columns on shop (matches reference design).
 */
function kids_shop_loop_columns()
{
	return 3;
}
add_filter('loop_shop_columns', 'kids_shop_loop_columns');

/**
 * Products per page (Theme Settings → Shop).
 */
function kids_shop_products_per_page()
{
	return (int) kids_shop_get_option('shop_products_per_page', 12);
}
add_filter('loop_shop_per_page', 'kids_shop_products_per_page');

/**
 * Always apply theme products-per-page on shop archives.
 *
 * WooCommerce skips loop_shop_per_page when posts_per_page is already set
 * (e.g. WordPress Reading → "Blog pages show at most 20 posts").
 */
function kids_shop_apply_shop_products_per_page($query)
{
	$query->set('posts_per_page', kids_shop_products_per_page());
}
add_action('woocommerce_product_query', 'kids_shop_apply_shop_products_per_page');

/**
 * Shop page body class for layout CSS.
 */
function kids_shop_body_class($classes)
{
	if (function_exists('is_woocommerce') && (is_shop() || is_product_taxonomy())) {
		$classes[] = 'kids-shop-archive';
	}
	if (function_exists('is_product') && is_product()) {
		$classes[] = 'kids-shop-single-product-page';
	}
	if (is_front_page()) {
		$classes[] = 'kids-shop-home-page';
	}
	if (function_exists('is_cart') && is_cart()) {
		$classes[] = 'kids-shop-cart-page';
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
		$classes[] = 'kids-shop-checkout-page';
	}
	if (is_page('login')) {
		$classes[] = 'kids-shop-auth-page-body';
		$classes[] = 'kids-shop-login-page';
	}
	if (is_page('signup')) {
		$classes[] = 'kids-shop-auth-page-body';
		$classes[] = 'kids-shop-signup-page';
	}
	if (function_exists('is_account_page') && is_account_page()) {
		$classes[] = 'kids-shop-myaccount-page';
	}
	return $classes;
}
add_filter('body_class', 'kids_shop_body_class');

/**
 * Use theme cart layout on the WooCommerce cart page.
 *
 * @param string $template Path to template.
 * @return string
 */
function kids_shop_cart_page_template($template)
{
	if (function_exists('is_cart') && is_cart() && !is_admin()) {
		$custom = get_template_directory() . '/woocommerce/cart-page.php';
		if (file_exists($custom)) {
			return $custom;
		}
	}
	return $template;
}
add_filter('template_include', 'kids_shop_cart_page_template', 99);

/**
 * Use theme checkout layout on the WooCommerce checkout page.
 *
 * @param string $template Path to template.
 * @return string
 */
function kids_shop_checkout_page_template( $template ) {
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-received' ) && ! is_admin() ) {
		$custom = get_template_directory() . '/woocommerce/checkout-page.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	return $template;
}
add_filter( 'template_include', 'kids_shop_checkout_page_template', 99 );

/**
 * Use theme My Account layout on the WooCommerce account page.
 *
 * @param string $template Path to template.
 * @return string
 */
function kids_shop_myaccount_page_template( $template ) {
	if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_admin() ) {
		$custom = get_template_directory() . '/woocommerce/myaccount-page.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	return $template;
}
add_filter( 'template_include', 'kids_shop_myaccount_page_template', 99 );

/**
 * Enqueue cart page assets.
 */
function kids_shop_enqueue_cart_assets()
{
	if (!function_exists('is_cart') || !is_cart()) {
		return;
	}

	$theme_version = wp_get_theme()->get('Version');

	wp_enqueue_style(
		'kids-shop-shop',
		get_template_directory_uri() . '/assets/kids-shop-shop.css',
		array('kids-shop-style'),
		$theme_version
	);

	$cart_css = get_template_directory() . '/assets/kids-shop-cart.css';
	wp_enqueue_style(
		'kids-shop-cart',
		get_template_directory_uri() . '/assets/kids-shop-cart.css',
		array('kids-shop-style'),
		file_exists($cart_css) ? (string) filemtime($cart_css) : $theme_version
	);

	wp_enqueue_script('wc-cart');
	wp_enqueue_script('wc-cart-fragments');

	$cart_js = get_template_directory() . '/assets/cart.js';
	wp_enqueue_script(
		'kids-shop-cart-js',
		get_template_directory_uri() . '/assets/cart.js',
		array('jquery', 'wc-cart', 'wc-cart-fragments'),
		file_exists($cart_js) ? (string) filemtime($cart_js) : $theme_version,
		true
	);

	if (WC()->cart) {
		$display = kids_shop_get_cart_display_state();

		$count = (int) $display['count'];
		wp_add_inline_script(
			'kids-shop-cart-js',
			'jQuery(function($){var c=' . $count . ';$(".kids-shop-header-cart-count").each(function(){$(this).text(c).attr("data-cart-count",c).toggleClass("kids-shop-header-cart-count--empty",c<1);});$(".cart-fixed-box .cart-box-top span").text(' . wp_json_encode($display['items_text']) . ');$(".cart-price span").html(' . wp_json_encode($display['total_html']) . ');});',
			'after'
		);
	}
}
add_action('wp_enqueue_scripts', 'kids_shop_enqueue_cart_assets', 20);

/**
 * Enqueue checkout page assets.
 */
function kids_shop_enqueue_checkout_assets() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}

	$theme_version = wp_get_theme()->get( 'Version' );
	$css_path      = get_template_directory() . '/assets/kids-shop-checkout.css';

	wp_enqueue_style(
		'kids-shop-checkout',
		get_template_directory_uri() . '/assets/kids-shop-checkout.css',
		array( 'kids-shop-style' ),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : $theme_version
	);
}
add_action( 'wp_enqueue_scripts', 'kids_shop_enqueue_checkout_assets', 20 );

/**
 * Match checkout CTA text to design.
 *
 * @return string
 */
function kids_shop_checkout_order_button_text() {
	return __( 'Confirm Order', 'kids-shop' );
}
add_filter( 'woocommerce_order_button_text', 'kids_shop_checkout_order_button_text' );

/**
 * Simplify checkout fields to match reference design.
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function kids_shop_customize_checkout_fields( $fields ) {
	if ( isset( $fields['billing'] ) ) {
		unset(
			$fields['billing']['billing_last_name'],
			$fields['billing']['billing_company'],
			$fields['billing']['billing_country'],
			$fields['billing']['billing_state'],
			$fields['billing']['billing_city'],
			$fields['billing']['billing_postcode'],
			$fields['billing']['billing_email']
		);

		if ( isset( $fields['billing']['billing_first_name'] ) ) {
			$fields['billing']['billing_first_name']['label']       = __( 'Full Name', 'kids-shop' );
			$fields['billing']['billing_first_name']['placeholder'] = '';
			$fields['billing']['billing_first_name']['priority']    = 10;
			$fields['billing']['billing_first_name']['class']       = array( 'form-row-first' );
		}

		if ( isset( $fields['billing']['billing_phone'] ) ) {
			$fields['billing']['billing_phone']['label']       = __( 'Enter Your Phone Number', 'kids-shop' );
			$fields['billing']['billing_phone']['placeholder'] = '';
			$fields['billing']['billing_phone']['required']    = true;
			$fields['billing']['billing_phone']['priority']    = 20;
			$fields['billing']['billing_phone']['class']       = array( 'form-row-last' );
		}

		$fields['billing']['billing_state'] = array(
			'type'        => 'select',
			'label'       => __( 'Select Division', 'kids-shop' ),
			'required'    => true,
			'class'       => array( 'form-row-wide' ),
			'priority'    => 30,
			'options'     => array(
				''            => __( 'Select Division', 'kids-shop' ),
				'dhaka'       => __( 'Dhaka', 'kids-shop' ),
				'chattogram'  => __( 'Chattogram', 'kids-shop' ),
				'rajshahi'    => __( 'Rajshahi', 'kids-shop' ),
				'khulna'      => __( 'Khulna', 'kids-shop' ),
				'barishal'    => __( 'Barishal', 'kids-shop' ),
				'sylhet'      => __( 'Sylhet', 'kids-shop' ),
				'rangpur'     => __( 'Rangpur', 'kids-shop' ),
				'mymensingh'  => __( 'Mymensingh', 'kids-shop' ),
			),
		);

		$fields['billing']['billing_address_1']['label']       = __( 'Full Address', 'kids-shop' );
		$fields['billing']['billing_address_1']['placeholder'] = '';
		$fields['billing']['billing_address_1']['type']        = 'textarea';
		$fields['billing']['billing_address_1']['class']       = array( 'form-row-wide' );
		$fields['billing']['billing_address_1']['priority']    = 40;
		unset( $fields['billing']['billing_address_2'] );
	}

	$fields['shipping']   = array();
	$fields['account']    = array();
	$fields['order']      = array();
	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'kids_shop_customize_checkout_fields', 20 );

/**
 * Keep header cart UI in sync on all front-end pages (fragment refresh on load).
 */
function kids_shop_enqueue_global_cart_fragments()
{
	if (is_admin() || !function_exists('WC')) {
		return;
	}

	wp_enqueue_script('wc-cart-fragments');

	if ( ! function_exists( 'kids_shop_enqueue_cart_fragment_scripts' ) || ! function_exists( 'kids_shop_get_cart_fragments' ) ) {
		return;
	}

	kids_shop_enqueue_cart_fragment_scripts();
}
add_action('wp_enqueue_scripts', 'kids_shop_enqueue_global_cart_fragments', 15);

/**
 * Apply header cart fragments after the header buffer is done (avoids nested ob_start).
 */
function kids_shop_print_cart_fragment_bootstrap() {
	if ( is_admin() || ! function_exists( 'WC' ) || ! function_exists( 'kids_shop_get_cart_fragments' ) ) {
		return;
	}

	$fragments = wp_json_encode( kids_shop_get_cart_fragments() );
	if ( ! $fragments || '{}' === $fragments ) {
		return;
	}

	wp_print_inline_script_tag(
		'(function($){var f=' . $fragments . ';function apply(){if(typeof window.kidsShopApplyCartFragments==="function"){window.kidsShopApplyCartFragments(f);}}$(apply);setTimeout(apply,100);setTimeout(apply,600);$(document.body).on("wc_fragments_refreshed added_to_cart",apply);})(jQuery);',
		array( 'id' => 'kids-shop-cart-fragments-bootstrap' )
	);
}
add_action( 'wp_footer', 'kids_shop_print_cart_fragment_bootstrap', 5 );

/**
 * Enqueue login & sign up page assets.
 */
function kids_shop_enqueue_auth_assets() {
	if ( ! is_page( 'login' ) && ! is_page( 'signup' ) ) {
		return;
	}

	$theme_version = wp_get_theme()->get( 'Version' );
	$auth_css      = get_template_directory() . '/assets/kids-shop-auth.css';
	$auth_js       = get_template_directory() . '/assets/auth.js';

	wp_enqueue_style(
		'kids-shop-auth',
		get_template_directory_uri() . '/assets/kids-shop-auth.css',
		array( 'kids-shop-style' ),
		file_exists( $auth_css ) ? (string) filemtime( $auth_css ) : $theme_version
	);

	wp_enqueue_script(
		'kids-shop-auth-js',
		get_template_directory_uri() . '/assets/auth.js',
		array(),
		file_exists( $auth_js ) ? (string) filemtime( $auth_js ) : $theme_version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'kids_shop_enqueue_auth_assets', 20 );

/**
 * Enqueue My Account page assets.
 */
function kids_shop_enqueue_myaccount_assets() {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}

	$theme_version = wp_get_theme()->get( 'Version' );
	$css_path      = get_template_directory() . '/assets/kids-shop-myaccount.css';

	wp_enqueue_style(
		'kids-shop-myaccount',
		get_template_directory_uri() . '/assets/kids-shop-myaccount.css',
		array( 'kids-shop-style' ),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : $theme_version
	);

	if ( is_user_logged_in() ) {
		$js_path = get_template_directory() . '/assets/myaccount.js';
		wp_enqueue_script(
			'kids-shop-myaccount',
			get_template_directory_uri() . '/assets/myaccount.js',
			array(),
			file_exists( $js_path ) ? (string) filemtime( $js_path ) : $theme_version,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'kids_shop_enqueue_myaccount_assets', 20 );

/**
 * Use theme login page instead of wp-login.php for front-end links.
 *
 * @param string $login_url Login URL.
 * @param string $redirect  Redirect target.
 * @return string
 */
function kids_shop_login_url( $login_url, $redirect ) {
	$url = kids_shop_get_login_url();
	if ( $redirect ) {
		$url = add_query_arg( 'redirect_to', urlencode( $redirect ), $url );
	}
	return $url;
}
add_filter( 'login_url', 'kids_shop_login_url', 10, 2 );

/**
 * Send failed logins back to the themed login page.
 */
function kids_shop_login_failed_redirect() {
	if ( is_admin() ) {
		return;
	}
	$url = add_query_arg( 'login', 'failed', kids_shop_get_login_url() );
	wp_safe_redirect( $url );
	exit;
}
add_action( 'wp_login_failed', 'kids_shop_login_failed_redirect' );

/**
 * Redirect empty login attempts from the themed form.
 */
function kids_shop_authenticate_empty_login( $user, $username, $password ) {
	if ( is_admin() || empty( $_POST['wp-submit'] ) ) {
		return $user;
	}
	if ( empty( $username ) || empty( $password ) ) {
		$url = add_query_arg( 'login', 'empty', kids_shop_get_login_url() );
		wp_safe_redirect( $url );
		exit;
	}
	return $user;
}
add_filter( 'authenticate', 'kids_shop_authenticate_empty_login', 30, 3 );

