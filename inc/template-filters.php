<?php
/**
 * Apply theme options to static header/footer HTML.
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Replacement map for legacy static markup.
 *
 * @param string $context header|footer.
 * @return array<string, string>
 */
function kids_shop_get_template_replacements( $context = 'header' ) {
	$defaults = kids_shop_get_default_options();
	$email    = kids_shop_get_option( 'contact_email', $defaults['contact_email'] );
	$phone    = kids_shop_get_option( 'contact_phone', $defaults['contact_phone'] );
	$address  = kids_shop_get_option( 'contact_address', $defaults['contact_address'] );
	$footer   = kids_shop_get_option( 'footer_description', $defaults['footer_description'] );
	$logo     = kids_shop_get_logo_url_for( $context );
	$logo_alt = sanitize_text_field( (string) kids_shop_get_option( 'logo_alt', $defaults['logo_alt'] ) );
	$home     = home_url( '/' );
	$shop     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$cart     = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
	$checkout = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' );
	$wa       = kids_shop_get_whatsapp_url();

	$map = array(
		'mail@gmail.com'                          => $email,
		'mailto:mail@gmail.com'                   => 'mailto:' . $email,
		'+8801000000000'                          => $phone,
		'tel:+8801000000000'                      => 'tel:' . preg_replace( '/\s+/', '', $phone ),
		'Mirpur 10, Dhaka, Bangladesh'            => $address,
		'A Best Online shop in Bangladesh, All the product are available online.' => $footer,
		'https://kiddomart.softlabit.shop/'       => trailingslashit( $home ),
		'https://kiddomart.softlabit.shop'        => untrailingslashit( $home ),
		'href="https://kiddomart.softlabit.shop/products"' => 'href="' . esc_url( $shop ) . '"',
		'https://kiddomart.softlabit.shop/cart'           => untrailingslashit( $cart ),
		'href="https://kiddomart.softlabit.shop/cart"'    => 'href="' . esc_url( $cart ) . '"',
		'https://kiddomart.softlabit.shop/checkout'       => untrailingslashit( $checkout ),
		'href="https://kiddomart.softlabit.shop/checkout"' => 'href="' . esc_url( $checkout ) . '"',
		'https://facebook.com/'                   => esc_url( kids_shop_get_option( 'social_facebook', $defaults['social_facebook'] ) ),
		'https://instagram.com/'                  => esc_url( kids_shop_get_option( 'social_instagram', $defaults['social_instagram'] ) ),
		'https://youtube.com/'                    => esc_url( kids_shop_get_option( 'social_youtube', $defaults['social_youtube'] ) ),
		'alt="KiddoMart"'                         => 'alt="' . esc_attr( $logo_alt ) . '"',
	);

	if ( $wa ) {
		$map['https://wa.me/+8801000000000?text=Hello!%20I%20need%20help%20with%20your%20services'] = esc_url( $wa );
	}

	$default_logo = kids_shop_get_default_logo_url();
	$cdn_logo     = kids_shop_get_legacy_cdn_logo_url();

	$map[ $default_logo ] = esc_url( $logo );
	$map[ $cdn_logo ]     = esc_url( $logo );

	// CDN srcset query variants from exported Angular markup.
	$map[ $cdn_logo . '?auto=format&amp;w=384' ] = esc_url( $logo );
	$map[ $cdn_logo . '?auto=format&amp;w=640' ] = esc_url( $logo );
	$map[ $cdn_logo . '?resolution=1632_640' ]   = esc_url( $logo );

	if ( 'header' === $context ) {
		$placeholder = (string) kids_shop_get_option( 'search_placeholder', $defaults['search_placeholder'] );
		$empty_msg   = (string) kids_shop_get_option( 'search_empty_message', $defaults['search_empty_message'] );
		$btn_text = (string) kids_shop_get_option( 'search_button_text', $defaults['search_button_text'] );

		$map['placeholder="Search produc|"']            = 'placeholder="' . esc_attr( $placeholder ) . '"';
		$map['Sorry! We couldn\'t find your Product. '] = esc_html( $empty_msg ) . ' ';
		$map['>Search</p>']                            = '>' . esc_html( $btn_text ) . '</p>';
	}

	return apply_filters( 'kids_shop_template_replacements', $map, $context );
}

/**
 * Build mobile search keyword markup.
 *
 * @return string
 */
function kids_shop_get_mobile_search_words_html() {
	$keywords = kids_shop_get_search_keywords();
	if ( empty( $keywords ) ) {
		return '';
	}

	$spans = '';
	foreach ( $keywords as $word ) {
		$spans .= '<span _ngcontent-ng-c454772091="">' . esc_html( $word ) . '</span>';
	}
	$spans .= '<span _ngcontent-ng-c454772091="">' . esc_html( $keywords[0] ) . '</span>';

	return '<div _ngcontent-ng-c454772091="" class="words">' . $spans . '<!-- --></div>';
}

/**
 * Apply string replacements once per request (cached map).
 *
 * @param string $html    Markup.
 * @param string $context header|footer.
 * @return string
 */
function kids_shop_apply_template_replacements( $html, $context = 'header' ) {
	static $cache = array();

	if ( ! isset( $cache[ $context ] ) ) {
		$map = kids_shop_get_template_replacements( $context );
		$cache[ $context ] = array(
			'search'  => array_keys( $map ),
			'replace' => array_values( $map ),
		);
	}

	$html = str_replace( $cache[ $context ]['search'], $cache[ $context ]['replace'], $html );

	if ( 'header' === $context ) {
		$words_html = kids_shop_get_mobile_search_words_html();
		if ( $words_html ) {
			$pattern = '#<div _ngcontent-ng-c454772091="" class="words">.*?</div>#s';
			$html    = preg_replace( $pattern, $words_html, $html, 1 );
		}

		$html = kids_shop_replace_header_search_markup( $html );
		$html = kids_shop_replace_header_cart_markup( $html );
	}

	if ( 'footer' === $context ) {
		$html = kids_shop_replace_footer_copyright_markup( $html );
		$html = kids_shop_replace_footer_link_columns( $html );
	}

	return $html;
}

/**
 * Replace footer Quick Links / Useful Links columns from Theme Settings menus.
 *
 * @param string $html Footer HTML.
 * @return string
 */
function kids_shop_replace_footer_link_columns( $html ) {
	$defaults = kids_shop_get_default_options();
	$columns  = array(
		array(
			'title'   => (string) kids_shop_get_option( 'footer_quick_links_title', $defaults['footer_quick_links_title'] ),
			'menu_id' => (int) kids_shop_get_option( 'footer_quick_links_menu', 0 ),
		),
		array(
			'title'   => (string) kids_shop_get_option( 'footer_useful_links_title', $defaults['footer_useful_links_title'] ),
			'menu_id' => (int) kids_shop_get_option( 'footer_useful_links_menu', 0 ),
		),
	);

	$index = 0;

	return preg_replace_callback(
		'#<div[^>]*class="footer-link"[^>]*>.*?</ul></div>#s',
		static function ( $matches ) use ( &$index, $columns ) {
			$block = $matches[0];
			$col   = isset( $columns[ $index ] ) ? $columns[ $index ] : null;
			++$index;

			if ( ! $col ) {
				return $block;
			}

			$title = trim( $col['title'] );
			if ( '' !== $title ) {
				$block = preg_replace(
					'#(<div[^>]*class="footer-title"[^>]*><b[^>]*>).*?(</b></div>)#s',
					'$1' . esc_html( $title ) . '$2',
					$block,
					1
				);
			}

			$menu_id = kids_shop_sanitize_nav_menu_id( $col['menu_id'] );
			if ( $menu_id ) {
				$items_html = kids_shop_build_footer_menu_items_html( $menu_id );
				$block      = preg_replace(
					'#(<ul[^>]*>).*?(</ul>)#s',
					'$1' . $items_html . '$2',
					$block,
					1
				);
			}

			return $block;
		},
		$html,
		2
	);
}

/**
 * Replace static header cart counts with live WooCommerce cart data.
 *
 * @param string $html Header HTML.
 * @return string
 */
function kids_shop_replace_header_cart_markup( $html ) {
	if ( ! function_exists( 'kids_shop_get_cart_display_state' ) ) {
		return $html;
	}

	$display = kids_shop_get_cart_display_state();
	$count   = (int) $display['count'];

	$desktop_badge = kids_shop_header_cart_count_html( $count, '_ngcontent-ng-c3456407154=""' );
	$mobile_badge  = kids_shop_header_cart_count_html( $count, '_ngcontent-ng-c454772091=""' );

	$html = preg_replace(
		'#<span _ngcontent-ng-c3456407154="" class="kids-shop-header-cart-count[^"]*" data-cart-count="\d+">\d+</span>#',
		$desktop_badge,
		$html,
		1
	);

	if ( ! str_contains( $html, 'kids-shop-header-cart-count' ) ) {
		$html = preg_replace(
			'#(class="cart-button kids-shop-header-cart-link"[^>]*>[\s\S]*?<span _ngcontent-ng-c3456407154="">)\d+(</span>)#',
			'${1}' . esc_html( (string) $count ) . '${2}',
			$html,
			1
		);
	}

	$html = preg_replace(
		'#(class="cart-box-top"[^>]*><span _ngcontent-ng-c3456407154="">)[^<]+(</span>)#',
		'${1}' . esc_html( $display['items_text'] ) . '${2}',
		$html,
		1
	);

	$html = preg_replace(
		'#(class="cart-price"[^>]*><span _ngcontent-ng-c3456407154="">)[^<]+(</span>)#',
		'${1}' . $display['total_html'] . '${2}',
		$html,
		1
	);

	$html = preg_replace(
		'#<span _ngcontent-ng-c454772091="" class="kids-shop-header-cart-count[^"]*" data-cart-count="\d+">\d+</span>#',
		$mobile_badge,
		$html,
		1
	);

	if ( ! preg_match( '#app-header-sm-1[\s\S]*?kids-shop-header-cart-count#', $html ) ) {
		$html = preg_replace(
			'#(<app-header-sm-1[\s\S]*?class="cart kids-shop-header-cart-link"[^>]*>[\s\S]*?<span _ngcontent-ng-c454772091="">)\d+(</span>)#',
			'${1}' . esc_html( (string) $count ) . '${2}',
			$html,
			1
		);
	}

	$html = kids_shop_replace_header_cart_dropdown( $html );

	return $html;
}

/**
 * Replace static exported mini-cart dropdown with live WooCommerce cart items.
 *
 * @param string $html Header HTML.
 * @return string
 */
function kids_shop_replace_header_cart_dropdown( $html ) {
	if ( ! function_exists( 'kids_shop_get_header_cart_dropdown_wrap_html' ) ) {
		return $html;
	}

	$open_marker  = 'class="cart-dropdown-wrap';
	$close_marker = 'class="cart-fixed-box';

	$open_pos = strpos( $html, $open_marker );
	if ( false === $open_pos ) {
		return $html;
	}

	$start = strrpos( substr( $html, 0, $open_pos ), '<div' );
	if ( false === $start ) {
		return $html;
	}

	$close_pos = strpos( $html, $close_marker, $open_pos );
	if ( false === $close_pos ) {
		return $html;
	}

	$close_end = strrpos( substr( $html, 0, $close_pos ), '</div>' );
	if ( false === $close_end ) {
		$close_end = $close_pos;
	} else {
		$close_end += strlen( '</div>' );
	}

	return substr( $html, 0, $start ) . kids_shop_get_header_cart_dropdown_wrap_html() . substr( $html, $close_end );
}

/**
 * Replace static footer copyright with Theme Settings values.
 *
 * @param string $html Footer HTML.
 * @return string
 */
function kids_shop_replace_footer_copyright_markup( $html ) {
	$copyright = kids_shop_get_footer_copyright_html();

	$replacement = '<div _ngcontent-ng-c693230799="" class="ntt-left"><b _ngcontent-ng-c693230799="">' . $copyright . '</b></div>';

	return preg_replace(
		'#<div _ngcontent-ng-c693230799="" class="ntt-left"><b _ngcontent-ng-c693230799="">.*?</b></div>#s',
		$replacement,
		$html,
		1
	);
}

/**
 * Filter buffered header HTML.
 *
 * @param string $html Markup.
 * @return string
 */
function kids_shop_filter_header_html( $html ) {
	return kids_shop_apply_template_replacements( $html, 'header' );
}

/**
 * Filter buffered footer HTML.
 *
 * @param string $html Markup.
 * @return string
 */
function kids_shop_filter_footer_html( $html ) {
	return kids_shop_apply_template_replacements( $html, 'footer' );
}
