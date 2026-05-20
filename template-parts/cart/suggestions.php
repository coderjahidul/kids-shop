<?php
/**
 * Cart page "You May Like" product suggestions.
 *
 * @package Kids_Shop
 *
 * @var int $suggestion_limit Optional max products.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$limit       = isset( $suggestion_limit ) ? max( 1, (int) $suggestion_limit ) : 5;
$suggestions = function_exists( 'kids_shop_get_cart_suggestion_products' )
	? kids_shop_get_cart_suggestion_products( $limit )
	: array();

get_template_part(
	'template-parts/shop/product',
	'row',
	array(
		'section_title' => __( 'You May Like', 'kids-shop' ),
		'products'      => $suggestions,
		'wrapper_class' => 'kids-shop-cart-suggestions',
		'with_panel'    => true,
	)
);
