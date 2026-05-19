<?php
/**
 * Header product search (WooCommerce).
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Current search query string for the header input.
 *
 * @return string
 */
function kids_shop_get_header_search_query() {
	if ( isset( $_GET['s'] ) ) {
		return sanitize_text_field( wp_unslash( $_GET['s'] ) );
	}
	return get_search_query();
}

/**
 * Desktop header product search form markup.
 *
 * @return string
 */
function kids_shop_get_header_search_form_html() {
	$action      = kids_shop_get_product_search_url();
	$placeholder = (string) kids_shop_get_option( 'search_placeholder', __( 'Search products...', 'kids-shop' ) );
	$value       = kids_shop_get_header_search_query();
	$label       = esc_attr__( 'Search', 'kids-shop' );
	$sr          = esc_html__( 'Search products', 'kids-shop' );

	return sprintf(
		'<form class="kids-shop-search-form" action="%1$s" method="get" role="search">'
		. '<label class="screen-reader-text" for="searchInput">%2$s</label>'
		. '<input type="search" name="s" id="searchInput" class="tw kids-shop-search-input" placeholder="%3$s" value="%4$s" autocomplete="off" required/>'
		. '<input type="hidden" name="post_type" value="product"/>'
		. '<button type="submit" class="search-icon kids-shop-search-submit" aria-label="%5$s">'
		. '<svg fill="currentColor" height="24" viewBox="0 -960 960 960" width="24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
		. '<path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/>'
		. '</svg></button></form>',
		esc_url( $action ),
		$sr,
		esc_attr( $placeholder ),
		esc_attr( $value ),
		$label
	);
}

/**
 * Replace legacy Angular search markup with a working WooCommerce form.
 *
 * @param string $html Header HTML.
 * @return string
 */
function kids_shop_replace_header_search_markup( $html ) {
	$form = kids_shop_get_header_search_form_html();

	// Desktop: replace Angular form + duplicate icon button.
	$html = preg_replace(
		'#<form _ngcontent-ng-c566241760=""[^>]*>.*?</form>\s*<div _ngcontent-ng-c566241760="" class="search-icon">.*?</div>#s',
		$form,
		$html,
		1
	);


	// Mobile: enable tap-to-search (no Angular /search route in WordPress).
	$html = str_replace(
		'class="search-area" routerlink="/search" tabindex="0"',
		'class="search-area kids-shop-mobile-search" data-kids-shop-mobile-search="1" tabindex="0" role="button"',
		$html
	);

	return $html;
}

/**
 * Enqueue header search script.
 */
function kids_shop_enqueue_header_search_assets() {
	$path = get_template_directory() . '/assets/header-search.js';
	if ( ! file_exists( $path ) ) {
		return;
	}

	wp_enqueue_script(
		'kids-shop-header-search',
		get_template_directory_uri() . '/assets/header-search.js',
		array(),
		(string) filemtime( $path ),
		true
	);

	wp_localize_script(
		'kids-shop-header-search',
		'kidsShopSearch',
		array(
			'shopUrl' => kids_shop_get_product_search_url(),
			'prompt'  => __( 'Search products', 'kids-shop' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'kids_shop_enqueue_header_search_assets', 25 );

/**
 * Product search on the shop page (?s=…&post_type=product).
 *
 * @param WP_Query $query Main query.
 */
function kids_shop_shop_product_search( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! function_exists( 'is_shop' ) || ! is_shop() ) {
		return;
	}

	if ( empty( $_GET['s'] ) ) {
		return;
	}

	$query->set( 's', sanitize_text_field( wp_unslash( $_GET['s'] ) ) );
	$query->set( 'post_type', 'product' );
}
add_action( 'pre_get_posts', 'kids_shop_shop_product_search', 20 );
