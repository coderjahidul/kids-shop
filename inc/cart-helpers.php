<?php
/**
 * Cart page helpers.
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Products for cart "You May Like" section.
 *
 * @param int $limit Max products.
 * @return WC_Product[]
 */
function kids_shop_get_cart_suggestion_products( $limit = 5 ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$exclude = array();
	if ( WC()->cart && ! WC()->cart->is_empty() ) {
		foreach ( WC()->cart->get_cart() as $item ) {
			$exclude[] = (int) $item['product_id'];
		}
	}

	$cross_sell_ids = WC()->cart ? WC()->cart->get_cross_sells() : array();
	if ( ! empty( $cross_sell_ids ) ) {
		$products = array();
		foreach ( array_slice( array_diff( $cross_sell_ids, $exclude ), 0, $limit ) as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product && $product->is_visible() ) {
				$products[] = $product;
			}
		}
		if ( count( $products ) >= $limit ) {
			return $products;
		}
		$exclude = array_merge( $exclude, wp_list_pluck( $products, 'id' ) );
		$limit   = $limit - count( $products );
	} else {
		$products = array();
	}

	$query_products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => $limit,
			'exclude' => $exclude,
			'orderby' => 'popularity',
			'order'   => 'DESC',
		)
	);

	return array_merge( $products, $query_products );
}

/**
 * First product category name for display.
 *
 * @param int $product_id Product ID.
 * @return string
 */
function kids_shop_get_product_category_label( $product_id ) {
	$terms = get_the_terms( $product_id, 'product_cat' );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}
	return $terms[0]->name;
}

/**
 * Cart subtotal before discounts (sum of regular prices × qty).
 *
 * @return float
 */
function kids_shop_cart_regular_subtotal() {
	if ( ! WC()->cart ) {
		return 0.0;
	}

	$total = 0.0;
	foreach ( WC()->cart->get_cart() as $item ) {
		$product = $item['data'];
		if ( ! $product ) {
			continue;
		}
		$price = (float) $product->get_regular_price();
		if ( $price <= 0 ) {
			$price = (float) $product->get_price();
		}
		$total += $price * (int) $item['quantity'];
	}

	return $total;
}

/**
 * Cart discount amount (regular subtotal minus cart subtotal).
 *
 * @return float
 */
function kids_shop_cart_discount_total() {
	if ( ! WC()->cart ) {
		return 0.0;
	}
	$discount = kids_shop_cart_regular_subtotal() - (float) WC()->cart->get_subtotal();
	return max( 0, $discount );
}

/**
 * Update header mini-cart counts after cart changes.
 *
 * @param array $fragments Cart fragments.
 * @return array
 */
function kids_shop_cart_fragments( $fragments ) {
	if ( ! WC()->cart ) {
		return $fragments;
	}

	$count = WC()->cart->get_cart_contents_count();
	$total = WC()->cart->get_cart_total();

	$fragments['.cart-button span'] = '<span>' . esc_html( (string) $count ) . '</span>';
	$fragments['.cart-fixed-box .cart-box-top span'] = '<span>' . esc_html( sprintf(
		/* translators: %d: item count */
		_n( '%d Item', '%d Items', $count, 'kids-shop' ),
		$count
	) ) . '</span>';
	$fragments['.cart-price span'] = '<span>' . wp_kses_post( $total ) . '</span>';

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'kids_shop_cart_fragments' );

/**
 * Whether cart-specific formatting should apply.
 *
 * @return bool
 */
function kids_shop_is_cart_context() {
	return function_exists( 'is_cart' ) && is_cart();
}

/**
 * Format price for cart display (৳ 2,200 style).
 *
 * @param float|string $amount Raw amount.
 * @return string
 */
function kids_shop_cart_format_price( $amount ) {
	$amount = (float) $amount;

	return wc_price(
		$amount,
		array(
			'decimals'           => 0,
			'currency'           => get_woocommerce_currency(),
			'decimal_separator'  => wc_get_price_decimal_separator(),
			'thousand_separator' => wc_get_price_thousand_separator(),
		)
	);
}

/**
 * Line total in TOTAL column (number only, like reference).
 *
 * @param float|string $amount Raw amount.
 * @return string
 */
function kids_shop_cart_format_line_total( $amount ) {
	$amount = (float) $amount;
	$sep    = wc_get_price_thousand_separator();

	return number_format( $amount, 0, wc_get_price_decimal_separator(), $sep );
}

/**
 * Cart page: symbol before amount with a space (৳ 2,200).
 *
 * @param string $format Price format.
 * @return string
 */
function kids_shop_cart_price_format( $format ) {
	if ( kids_shop_is_cart_context() ) {
		return '%1$s&nbsp;%2$s';
	}
	return $format;
}
add_filter( 'woocommerce_price_format', 'kids_shop_cart_price_format' );

/**
 * Cart page: drop trailing .00 when whole numbers.
 *
 * @param bool $trim Whether to trim zeros.
 * @return bool
 */
function kids_shop_cart_trim_price_zeros( $trim ) {
	if ( kids_shop_is_cart_context() ) {
		return true;
	}
	return $trim;
}
add_filter( 'woocommerce_price_trim_zeros', 'kids_shop_cart_trim_price_zeros' );

/**
 * Cart page: no decimal places on line items and summary.
 *
 * @param int $decimals Decimal count.
 * @return int
 */
function kids_shop_cart_price_decimals( $decimals ) {
	if ( kids_shop_is_cart_context() ) {
		return 0;
	}
	return $decimals;
}
add_filter( 'woocommerce_price_num_decimals', 'kids_shop_cart_price_decimals' );
