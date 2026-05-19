<?php
/**
 * Home page helpers.
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hero slider slides from Theme Settings (or defaults).
 *
 * @return array<int, array{image: string, link: string, alt: string}>
 */
function kids_shop_get_hero_slides() {
	$fallback_assets = array(
		'image-3-min-4b80.webp',
		'image-min-2-9bba.webp',
		'image-3-min-4b80.webp',
		'image-min-2-9bba.webp',
	);
	$default_links = array(
		kids_shop_get_products_url(),
		kids_shop_get_products_url( 'winter-collection' ),
		kids_shop_get_products_url( 'boys' ),
		kids_shop_get_products_url( 'girls' ),
	);

	$slides = array();

	for ( $i = 1; $i <= 4; $i++ ) {
		$image = kids_shop_get_hero_slide_image_url( $i, $fallback_assets[ $i - 1 ] );
		$link  = kids_shop_get_option( 'hero_slide_' . $i . '_link', '' );
		$alt   = kids_shop_get_option( 'hero_slide_' . $i . '_alt', '' );

		if ( ! $link ) {
			$link = $default_links[ $i - 1 ];
		}
		if ( ! $alt ) {
			$alt = sprintf( /* translators: %d: slide number */ __( 'Slide %d', 'kids-shop' ), $i );
		}

		$slides[] = array(
			'image' => $image,
			'link'  => $link,
			'alt'   => $alt,
		);
	}

	return apply_filters( 'kids_shop_hero_slides', $slides );
}

/**
 * Home product sections from Theme Settings (repeater).
 *
 * @return array<int, array{title: string, view_all: string, query_args: array}>
 */
function kids_shop_get_home_product_sections() {
	$shop     = kids_shop_get_products_url();
	$sections = array();

	foreach ( kids_shop_get_home_sections_config() as $config ) {
		$query_args = array( 'limit' => (int) $config['limit'] );
		$view_all   = $shop;

		switch ( $config['type'] ) {
			case 'category':
				if ( ! empty( $config['category'] ) ) {
					$query_args['category'] = array( $config['category'] );
					$view_all               = kids_shop_get_products_url( $config['category'] );
				}
				break;
			case 'on_sale':
				$query_args['on_sale'] = true;
				$view_all              = add_query_arg( 'on_sale', '1', $shop );
				break;
			case 'popular':
				$query_args['orderby'] = 'popularity';
				break;
			case 'featured':
				$query_args['featured'] = true;
				break;
		}

		if ( ! empty( $config['view_all_url'] ) ) {
			$view_all = $config['view_all_url'];
		}

		$view_all_text = isset( $config['view_all_text'] ) ? trim( (string) $config['view_all_text'] ) : '';
		if ( '' === $view_all_text ) {
			$view_all_text = __( 'View All', 'kids-shop' );
		}

		$sections[] = array(
			'title'         => $config['title'],
			'view_all'      => $view_all,
			'view_all_text' => $view_all_text,
			'query_args'    => $query_args,
		);
	}

	return apply_filters( 'kids_shop_home_product_sections', $sections );
}

/**
 * Query products for a home section.
 *
 * @param array $args Section query_args.
 * @return WC_Product[]
 */
function kids_shop_get_home_products( $args = array() ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$defaults = array(
		'limit'   => 5,
		'status'  => 'publish',
		'orderby' => 'date',
		'order'   => 'DESC',
	);
	$args = wp_parse_args( $args, $defaults );

	$query = array(
		'limit'   => (int) $args['limit'],
		'status'  => $args['status'],
		'orderby' => $args['orderby'],
		'order'   => $args['order'],
	);

	if ( ! empty( $args['category'] ) ) {
		$query['category'] = (array) $args['category'];
	}

	if ( ! empty( $args['on_sale'] ) ) {
		$query['on_sale'] = true;
	}

	if ( ! empty( $args['featured'] ) ) {
		$query['featured'] = true;
	}

	if ( 'popularity' === $args['orderby'] ) {
		$query['orderby'] = 'popularity';
	}

	$products = wc_get_products( $query );

	return is_array( $products ) ? $products : array();
}

/**
 * Categories shown on home (top-level product_cat terms).
 *
 * @return WP_Term[]
 */
function kids_shop_get_home_categories() {
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
