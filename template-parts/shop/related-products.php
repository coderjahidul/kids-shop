<?php
/**
 * Single product — Related Products (same layout as cart "You May Like").
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

$product_id = $product && is_a( $product, 'WC_Product' ) ? (int) $product->get_id() : 0;
if ( ! $product_id ) {
	return;
}

$limit   = (int) apply_filters( 'kids_shop_related_products_limit', 5, $product_id );
$related = function_exists( 'kids_shop_get_related_products_for_row' )
	? kids_shop_get_related_products_for_row( $product_id, $limit )
	: array();

if ( empty( $related ) ) {
	return;
}

	get_template_part(
		'template-parts/shop/product',
		'row',
		array(
			'section_title' => __( 'Related Products', 'kids-shop' ),
			'products'      => $related,
			'wrapper_class' => 'kids-shop-related-products',
			'with_panel'    => false,
		)
	);
