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

	$limit = max( 1, (int) $limit );
	$pool  = kids_shop_collect_cart_suggestion_ids( $limit * 4 );
	$found = kids_shop_products_from_ids( $pool, $limit );

	if ( count( $found ) >= $limit ) {
		return $found;
	}

	$exclude = kids_shop_product_list_ids( $found );
	$need    = $limit - count( $found );

	$popular = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => $need,
			'exclude' => $exclude,
			'orderby' => 'popularity',
			'order'   => 'DESC',
		)
	);

	$found = array_merge( $found, $popular );
	if ( count( $found ) >= $limit ) {
		return array_slice( $found, 0, $limit );
	}

	$exclude = array_merge( $exclude, kids_shop_product_list_ids( $found ) );
	$need    = $limit - count( $found );

	$recent = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => $need,
			'exclude' => $exclude,
			'orderby' => 'date',
			'order'   => 'DESC',
		)
	);

	return array_slice( array_merge( $found, $recent ), 0, $limit );
}

/**
 * Product IDs currently in the cart (including variations).
 *
 * @return int[]
 */
function kids_shop_get_cart_product_ids() {
	$ids = array();

	if ( ! WC()->cart || WC()->cart->is_empty() ) {
		return $ids;
	}

	foreach ( WC()->cart->get_cart() as $item ) {
		if ( ! empty( $item['product_id'] ) ) {
			$ids[] = (int) $item['product_id'];
		}
		if ( ! empty( $item['variation_id'] ) ) {
			$ids[] = (int) $item['variation_id'];
		}
	}

	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Gather related / cross-sell / upsell / category product IDs from cart contents.
 *
 * @param int   $max_ids  Max candidate IDs.
 * @return int[]
 */
function kids_shop_collect_cart_suggestion_ids( $max_ids = 20 ) {
	$candidate_ids = array();

	if ( WC()->cart && ! WC()->cart->is_empty() ) {
		$cross_sells = WC()->cart->get_cross_sells();
		if ( ! empty( $cross_sells ) ) {
			$candidate_ids = array_merge( $candidate_ids, array_map( 'intval', $cross_sells ) );
		}

		foreach ( WC()->cart->get_cart() as $item ) {
			$product = isset( $item['data'] ) ? $item['data'] : null;
			if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
				continue;
			}

			$product_id = (int) $product->get_id();
			$parent_id  = (int) $product->get_parent_id();
			$base_id    = $parent_id > 0 ? $parent_id : $product_id;

			if ( function_exists( 'wc_get_related_products' ) ) {
				$related = wc_get_related_products( $base_id, 6 );
				if ( ! empty( $related ) ) {
					$candidate_ids = array_merge( $candidate_ids, array_map( 'intval', $related ) );
				}
			}

			$upsells = $product->get_upsell_ids();
			if ( ! empty( $upsells ) ) {
				$candidate_ids = array_merge( $candidate_ids, array_map( 'intval', $upsells ) );
			}

			$cross = $product->get_cross_sell_ids();
			if ( ! empty( $cross ) ) {
				$candidate_ids = array_merge( $candidate_ids, array_map( 'intval', $cross ) );
			}

			$terms = wp_get_post_terms( $base_id, 'product_cat', array( 'fields' => 'ids' ) );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$cat_products = wc_get_products(
					array(
						'status'   => 'publish',
						'limit'    => 6,
						'category' => array_map( 'intval', $terms ),
						'orderby'  => 'popularity',
						'order'    => 'DESC',
						'return'   => 'ids',
					)
				);
				if ( ! empty( $cat_products ) ) {
					$candidate_ids = array_merge( $candidate_ids, array_map( 'intval', $cat_products ) );
				}
			}
		}
	}

	$candidate_ids = array_values(
		array_unique(
			array_filter(
				array_map( 'intval', $candidate_ids ),
				function ( $id ) {
					return $id > 0;
				}
			)
		)
	);

	return array_slice( $candidate_ids, 0, max( 1, (int) $max_ids ) );
}

/**
 * Load visible products from an ordered list of IDs.
 *
 * @param int[] $ids     Product IDs in priority order.
 * @param int   $limit   Max products to return.
 * @param int[] $exclude IDs already picked (dedupe only).
 * @return WC_Product[]
 */
function kids_shop_products_from_ids( $ids, $limit, $exclude = array() ) {
	$products = array();
	$seen     = array();

	foreach ( $ids as $product_id ) {
		if ( count( $products ) >= $limit ) {
			break;
		}

		$product_id = (int) $product_id;
		if ( $product_id <= 0 || in_array( $product_id, $exclude, true ) || isset( $seen[ $product_id ] ) ) {
			continue;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product || ! $product->is_visible() || ! $product->is_purchasable() ) {
			continue;
		}

		$seen[ $product_id ] = true;
		$products[]            = $product;
	}

	return $products;
}

/**
 * Extract product IDs from WC_Product objects.
 *
 * @param WC_Product[] $products Products.
 * @return int[]
 */
function kids_shop_product_list_ids( $products ) {
	$ids = array();

	foreach ( $products as $product ) {
		if ( $product && is_a( $product, 'WC_Product' ) ) {
			$ids[] = (int) $product->get_id();
		}
	}

	return $ids;
}

/**
 * Render cart suggestion section HTML.
 *
 * @param int $limit Max products.
 * @return string
 */
function kids_shop_get_cart_suggestions_html( $limit = 5 ) {
	if ( kids_shop_using_output_handler_buffer() ) {
		return '';
	}

	return kids_shop_capture_template_part(
		'template-parts/cart/suggestions',
		null,
		array(
			'suggestion_limit' => $limit,
		)
	);
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
 * Ensure WooCommerce cart session is available for header mini-cart rendering.
 */
function kids_shop_ensure_cart_loaded() {
	if ( ! function_exists( 'WC' ) ) {
		return;
	}

	if ( null === WC()->cart ) {
		wc_load_cart();
	}

	if ( WC()->cart ) {
		WC()->cart->get_cart();
	}
}

/**
 * Cart badge / floating mini-cart display values.
 *
 * @return array{count:int,items_text:string,total_html:string}
 */
function kids_shop_get_cart_display_state() {
	kids_shop_ensure_cart_loaded();

	$state = array(
		'count'       => 0,
		'items_text'  => __( '0 Items', 'kids-shop' ),
		'total_html'  => '৳ 0',
	);

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $state;
	}

	$count = (int) WC()->cart->get_cart_contents_count();

	$state['count']      = $count;
	$state['items_text'] = sprintf(
		/* translators: %d: item count */
		_n( '%d Item', '%d Items', $count, 'kids-shop' ),
		$count
	);
	$state['total_html'] = wp_kses_post( WC()->cart->get_cart_total() );

	return $state;
}

/**
 * Header cart badge markup (teal circle with item count).
 *
 * @param int    $count Cart item count.
 * @param string $attr  Optional extra attributes on the span (e.g. Angular _ngcontent).
 * @return string
 */
function kids_shop_header_cart_count_html( $count, $attr = '' ) {
	$count   = max( 0, (int) $count );
	$classes = 'kids-shop-header-cart-count';

	if ( $count < 1 ) {
		$classes .= ' kids-shop-header-cart-count--empty';
	}

	$attr_html = $attr ? trim( $attr ) . ' ' : '';

	return sprintf(
		'<span %sclass="%s" data-cart-count="%d">%s</span>',
		$attr_html,
		esc_attr( $classes ),
		$count,
		esc_html( (string) $count )
	);
}

/**
 * Whether PHP is flushing an output-handler buffer (ob_start callback).
 * Nested ob_start() is forbidden inside those handlers.
 *
 * @return bool
 */
function kids_shop_using_output_handler_buffer() {
	$buffers = ob_get_status( true );

	if ( ! is_array( $buffers ) ) {
		return false;
	}

	foreach ( $buffers as $buffer ) {
		// PHP_OUTPUT_HANDLER === 1 (not defined in all PHP builds).
		if ( isset( $buffer['type'] ) && 1 === (int) $buffer['type'] ) {
			return true;
		}
	}

	return false;
}

/**
 * Capture a template part as HTML (safe outside output-handler buffers only).
 *
 * @param string               $slug Template slug.
 * @param string|null          $name Optional template name.
 * @param array<string, mixed> $args Variables for the template.
 * @return string
 */
function kids_shop_capture_template_part( $slug, $name = null, $args = array() ) {
	$templates = array();

	if ( $name ) {
		$templates[] = "{$slug}-{$name}.php";
	}

	$templates[] = "{$slug}.php";

	foreach ( $templates as $template ) {
		if ( 0 !== validate_file( $template ) ) {
			continue;
		}

		$located = locate_template( $template, false, false );

		if ( ! $located ) {
			continue;
		}

		ob_start();
		load_template( $located, false, $args );

		return (string) ob_get_clean();
	}

	return '';
}

/**
 * Clear cached header dropdown markup for this request.
 */
function kids_shop_flush_header_cart_dropdown_cache() {
	unset( $GLOBALS['kids_shop_header_cart_dropdown_html'] );
}

/**
 * Build header dropdown markup before header output buffers run.
 */
function kids_shop_prime_header_cart_dropdown_cache() {
	if ( is_admin() || ! function_exists( 'WC' ) ) {
		return;
	}

	kids_shop_ensure_cart_loaded();
	$GLOBALS['kids_shop_header_cart_dropdown_html'] = kids_shop_capture_template_part( 'template-parts/cart/header-dropdown' );
}
add_action( 'wp', 'kids_shop_prime_header_cart_dropdown_cache', 20 );
add_action( 'wp_enqueue_scripts', 'kids_shop_prime_header_cart_dropdown_cache', 5 );
add_action( 'woocommerce_add_to_cart', 'kids_shop_flush_header_cart_dropdown_cache' );
add_action( 'woocommerce_cart_item_removed', 'kids_shop_flush_header_cart_dropdown_cache' );
add_action( 'woocommerce_after_cart_item_quantity_update', 'kids_shop_flush_header_cart_dropdown_cache' );

/**
 * Inner HTML for the header hover mini-cart dropdown.
 *
 * @return string
 */
function kids_shop_get_header_cart_dropdown_html() {
	kids_shop_ensure_cart_loaded();

	if ( isset( $GLOBALS['kids_shop_header_cart_dropdown_html'] ) ) {
		return (string) $GLOBALS['kids_shop_header_cart_dropdown_html'];
	}

	if ( kids_shop_using_output_handler_buffer() ) {
		return '';
	}

	$html = kids_shop_capture_template_part( 'template-parts/cart/header-dropdown' );
	$GLOBALS['kids_shop_header_cart_dropdown_html'] = $html;

	return $html;
}

/**
 * Full header mini-cart dropdown element (for fragment replacement).
 *
 * @return string
 */
function kids_shop_get_header_cart_dropdown_wrap_html() {
	return '<div _ngcontent-ng-c3456407154="" class="cart-dropdown-wrap kids-shop-cart-dropdown">' . kids_shop_get_header_cart_dropdown_html() . '</div>';
}

/**
 * Build WooCommerce cart fragment map for header UI.
 *
 * @return array<string, string>
 */
function kids_shop_get_cart_fragments() {
	$display = kids_shop_get_cart_display_state();
	$count   = $display['count'];
	$badge   = kids_shop_header_cart_count_html( $count );

	$dropdown = kids_shop_get_header_cart_dropdown_wrap_html();

	return array(
		'.kids-shop-header-cart-count'         => $badge,
		'.cart .cart-dropdown-wrap'            => $dropdown,
		'.kids-shop-cart-dropdown'            => $dropdown,
		'.cart-fixed-box .cart-box-top span'   => '<span>' . esc_html( $display['items_text'] ) . '</span>',
		'.cart-price span'                     => '<span>' . $display['total_html'] . '</span>',
	);
}

/**
 * Update header mini-cart counts after cart changes.
 *
 * @param array $fragments Cart fragments.
 * @return array
 */
function kids_shop_cart_fragments( $fragments ) {
	return array_merge( $fragments, kids_shop_get_cart_fragments() );
}
add_filter( 'woocommerce_add_to_cart_fragments', 'kids_shop_cart_fragments' );

/**
 * Enqueue WooCommerce cart scripts required for AJAX fragment refresh.
 *
 * @return string[] Script handles to use as dependencies.
 */
function kids_shop_enqueue_cart_fragment_scripts() {
	$deps = array( 'jquery' );

	if ( ! function_exists( 'WC' ) ) {
		return $deps;
	}

	wp_enqueue_script( 'wc-add-to-cart' );
	wp_enqueue_script( 'wc-cart-fragments' );

	$sync_js = get_template_directory() . '/assets/cart-sync.js';
	wp_enqueue_script(
		'kids-shop-cart-sync',
		get_template_directory_uri() . '/assets/cart-sync.js',
		array( 'jquery', 'wc-cart-fragments' ),
		file_exists( $sync_js ) ? (string) filemtime( $sync_js ) : wp_get_theme()->get( 'Version' ),
		true
	);

	$buy_now_js = get_template_directory() . '/assets/buy-now.js';
	wp_enqueue_script(
		'kids-shop-buy-now',
		get_template_directory_uri() . '/assets/buy-now.js',
		array( 'jquery', 'wc-add-to-cart', 'kids-shop-cart-sync' ),
		file_exists( $buy_now_js ) ? (string) filemtime( $buy_now_js ) : wp_get_theme()->get( 'Version' ),
		true
	);
	wp_localize_script(
		'kids-shop-buy-now',
		'kidsShopBuyNow',
		array(
			'checkoutUrl' => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' ),
		)
	);

	$deps[] = 'wc-add-to-cart';
	$deps[] = 'wc-cart-fragments';
	$deps[] = 'kids-shop-cart-sync';
	$deps[] = 'kids-shop-buy-now';

	return $deps;
}

/**
 * Whether cart-specific formatting should apply.
 *
 * @return bool
 */
function kids_shop_is_cart_context() {
	return function_exists( 'is_cart' ) && is_cart();
}

/**
 * Whether checkout-specific formatting should apply.
 *
 * @return bool
 */
function kids_shop_is_checkout_context() {
	return function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-received' );
}

/**
 * Cart and checkout share the same price presentation.
 *
 * @return bool
 */
function kids_shop_is_cart_or_checkout_context() {
	return kids_shop_is_cart_context() || kids_shop_is_checkout_context();
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
	if ( kids_shop_is_cart_or_checkout_context() ) {
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
	if ( kids_shop_is_cart_or_checkout_context() ) {
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
	if ( kids_shop_is_cart_or_checkout_context() ) {
		return 0;
	}
	return $decimals;
}
add_filter( 'woocommerce_price_num_decimals', 'kids_shop_cart_price_decimals' );
