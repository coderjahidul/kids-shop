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
 * @return array<string, string>
 */
function kids_shop_get_template_replacements() {
	$defaults = kids_shop_get_default_options();
	$email    = kids_shop_get_option( 'contact_email', $defaults['contact_email'] );
	$phone    = kids_shop_get_option( 'contact_phone', $defaults['contact_phone'] );
	$address  = kids_shop_get_option( 'contact_address', $defaults['contact_address'] );
	$footer   = kids_shop_get_option( 'footer_description', $defaults['footer_description'] );
	$logo     = kids_shop_get_logo_url();
	$home     = home_url( '/' );
	$shop     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
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
		'https://facebook.com/'                   => esc_url( kids_shop_get_option( 'social_facebook', $defaults['social_facebook'] ) ),
		'https://instagram.com/'                  => esc_url( kids_shop_get_option( 'social_instagram', $defaults['social_instagram'] ) ),
		'https://youtube.com/'                    => esc_url( kids_shop_get_option( 'social_youtube', $defaults['social_youtube'] ) ),
	);

	if ( $wa ) {
		$map['https://wa.me/+8801000000000?text=Hello!%20I%20need%20help%20with%20your%20services'] = esc_url( $wa );
	}

	$default_logo = get_template_directory_uri() . '/assets/gemini-generated-image-dzqentdzqentdzqe-29a1.webp';
	$map[ $default_logo ] = esc_url( $logo );

	return apply_filters( 'kids_shop_template_replacements', $map );
}

/**
 * Filter buffered header HTML.
 *
 * @param string $html Markup.
 * @return string
 */
function kids_shop_filter_header_html( $html ) {
	return str_replace( array_keys( kids_shop_get_template_replacements() ), array_values( kids_shop_get_template_replacements() ), $html );
}

/**
 * Filter buffered footer HTML.
 *
 * @param string $html Markup.
 * @return string
 */
function kids_shop_filter_footer_html( $html ) {
	return kids_shop_filter_header_html( $html );
}
