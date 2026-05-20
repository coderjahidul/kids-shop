<?php
/**
 * Shop / category page helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default category thumbnail by slug (theme assets).
 */
function kids_shop_category_default_images() {
	return array(
		'accessories'        => 'kids-accessories-a919-bc2e.webp',
		'learning'           => 'kids-learning-fb2a-93dc.webp',
		'classy-home'        => 'classy-home-2f14-865d.webp',
		'health'             => 'health-safety-9a62-a21c.webp',
		'boys'               => 'kids-books-365e-813d.webp',
		'winter-collection'  => 'winter-collection-3010b-a1b8.webp',
		'girls'              => 'clothes-3f2c-6f96.webp',
		'toys'               => 'kids-toys-ee14-dcc6.webp',
		'shoes'              => 'footwear-b2e2-22b6.webp',
		'feeding'            => 'feeding-10610-db9f.webp',
		'moms-care'          => 'moms-care-9f8e-c31f.webp',
		'diapers'            => 'diapers-4caf-e501.webp',
	);
}

/**
 * Shop page URL, optionally filtered by category slug (?categories=boys).
 */
function kids_shop_get_products_url( $category_slug = '' ) {
	$url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	if ( $category_slug ) {
		$url = add_query_arg( 'categories', sanitize_title( $category_slug ), $url );
	}
	return $url;
}

/**
 * Active category slug: ?categories=, product_cat archive, or product_cat query var.
 */
function kids_shop_get_active_category_slug() {
	if ( ! empty( $_GET['categories'] ) ) {
		return sanitize_title( wp_unslash( $_GET['categories'] ) );
	}
	if ( is_tax( 'product_cat' ) ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) ) {
			return $term->slug;
		}
	}
	return '';
}

/**
 * WP_Term for active category or null.
 */
function kids_shop_get_active_category() {
	$slug = kids_shop_get_active_category_slug();
	if ( ! $slug || ! taxonomy_exists( 'product_cat' ) ) {
		return null;
	}
	return get_term_by( 'slug', $slug, 'product_cat' );
}

/**
 * Category image URL (thumbnail meta, then slug fallback).
 */
function kids_shop_get_category_image_url( $term ) {
	if ( ! $term || is_wp_error( $term ) ) {
		return '';
	}

	$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
	if ( $thumb_id ) {
		$url = wp_get_attachment_image_url( (int) $thumb_id, 'thumbnail' );
		if ( $url ) {
			return $url;
		}
	}

	$defaults = kids_shop_category_default_images();
	$slug     = $term->slug;
	if ( isset( $defaults[ $slug ] ) ) {
		return get_template_directory_uri() . '/assets/' . $defaults[ $slug ];
	}

	return get_template_directory_uri() . '/assets/kids-accessories-a919-bc2e.webp';
}

/**
 * Top-level product categories for sidebar.
 */
function kids_shop_get_sidebar_categories() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => false,
			'orderby'    => 'menu_order',
			'order'      => 'ASC',
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Child categories for expand chevron rows.
 */
function kids_shop_get_child_categories( $parent_id ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => (int) $parent_id,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Format price in BDT style (৳ 1,050).
 */
function kids_shop_format_price( $price ) {
	if ( '' === $price || null === $price ) {
		return '';
	}
	return '৳ ' . number_format( (float) $price, 0, '.', ',' );
}

/**
 * Sold count from total sales meta.
 */
function kids_shop_get_sold_count( $product ) {
	if ( ! $product ) {
		return 0;
	}
	return (int) $product->get_total_sales();
}

/**
 * Related products for single product product-row (same cards as cart suggestions).
 *
 * @param int $product_id Product ID.
 * @param int $limit      Max products.
 * @return WC_Product[]
 */
function kids_shop_get_related_products_for_row( $product_id, $limit = 5 ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return array();
	}

	$product_id = absint( $product_id );
	$limit      = max( 1, (int) $limit );

	if ( ! $product_id ) {
		return array();
	}

	$products = array();
	$seen     = array( $product_id => true );

	if ( function_exists( 'wc_get_related_products' ) ) {
		$related_ids = wc_get_related_products( $product_id, $limit + 4 );
		foreach ( $related_ids as $related_id ) {
			if ( count( $products ) >= $limit ) {
				break;
			}

			$related_id = (int) $related_id;
			if ( $related_id <= 0 || isset( $seen[ $related_id ] ) ) {
				continue;
			}

			$related = wc_get_product( $related_id );
			if ( ! $related || ! $related->is_visible() ) {
				continue;
			}

			$seen[ $related_id ] = true;
			$products[]          = $related;
		}
	}

	if ( count( $products ) >= $limit ) {
		return array_slice( $products, 0, $limit );
	}

	$terms = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return $products;
	}

	$cat_products = wc_get_products(
		array(
			'status'   => 'publish',
			'limit'    => $limit - count( $products ),
			'exclude'  => array_keys( $seen ),
			'category' => array_map( 'intval', $terms ),
			'orderby'  => 'popularity',
			'order'    => 'DESC',
		)
	);

	foreach ( $cat_products as $cat_product ) {
		if ( ! $cat_product || ! $cat_product->is_visible() ) {
			continue;
		}
		$pid = (int) $cat_product->get_id();
		if ( isset( $seen[ $pid ] ) ) {
			continue;
		}
		$seen[ $pid ] = true;
		$products[]   = $cat_product;
		if ( count( $products ) >= $limit ) {
			break;
		}
	}

	return array_slice( $products, 0, $limit );
}

/**
 * Filter main shop / category queries by ?categories= slug.
 */
function kids_shop_filter_products_by_category_param( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! function_exists( 'is_shop' ) ) {
		return;
	}

	$is_shop_context = is_shop() || is_tax( 'product_cat' );
	if ( ! $is_shop_context ) {
		return;
	}

	// Native category archives are already filtered by WooCommerce.
	if ( is_tax( 'product_cat' ) && empty( $_GET['categories'] ) ) {
		return;
	}

	$slug = kids_shop_get_active_category_slug();
	if ( ! $slug ) {
		return;
	}

	$tax_query = (array) $query->get( 'tax_query' );
	$tax_query[] = array(
		'taxonomy' => 'product_cat',
		'field'    => 'slug',
		'terms'    => $slug,
	);
	$query->set( 'tax_query', $tax_query );
}
add_action( 'pre_get_posts', 'kids_shop_filter_products_by_category_param' );

/**
 * Buy Now URL: add simple product to cart and go to checkout.
 *
 * @param WC_Product $product  Product.
 * @param int        $quantity Quantity (default 1).
 * @return string
 */
function kids_shop_buy_now_url( $product, $quantity = 1 ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}

	$product_id = $product->get_id();
	$permalink  = get_permalink( $product_id );

	if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		return $permalink;
	}

	if ( ! $product->is_type( 'simple' ) ) {
		return $permalink;
	}

	$quantity = max( 1, (int) $quantity );

	if ( ! function_exists( 'wc_get_checkout_url' ) ) {
		return $permalink;
	}

	return add_query_arg(
		array(
			'add-to-cart'       => $product_id,
			'quantity'          => $quantity,
			'kids_shop_buy_now' => '1',
		),
		wc_get_checkout_url()
	);
}

/**
 * After Buy Now add-to-cart, stay on checkout (not cart/product page).
 *
 * @param string $url Redirect URL.
 * @return string
 */
function kids_shop_buy_now_add_to_cart_redirect( $url ) {
	if ( ! empty( $_REQUEST['kids_shop_buy_now'] ) && function_exists( 'wc_get_checkout_url' ) ) {
		return wc_get_checkout_url();
	}
	return $url;
}
add_filter( 'woocommerce_add_to_cart_redirect', 'kids_shop_buy_now_add_to_cart_redirect' );
