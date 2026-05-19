<?php
/**
 * Theme options storage and accessors.
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KIDS_SHOP_OPTIONS_KEY', 'kids_shop_theme_options' );

/**
 * Default option values.
 *
 * @return array<string, mixed>
 */
function kids_shop_get_default_options() {
	$assets = get_template_directory_uri() . '/assets/';

	return array(
		// General.
		'logo_id'              => 0,
		'footer_description'   => __( 'A Best Online shop in Bangladesh, All the product are available online.', 'kids-shop' ),

		// Contact.
		'contact_email'        => 'mail@gmail.com',
		'contact_phone'        => '+8801000000000',
		'contact_address'      => 'Mirpur 10, Dhaka, Bangladesh',

		// Social.
		'social_facebook'      => 'https://facebook.com/',
		'social_instagram'     => 'https://instagram.com/',
		'social_youtube'       => 'https://youtube.com/',
		'social_whatsapp'      => '+8801000000000',

		// Colors.
		'color_primary'        => '#27A7B8',
		'color_secondary'      => '#D12C60',
		'color_tertiary'       => '#e8007c',

		// Hero slider slides (repeater).
		'hero_slides'          => array(),

		// Home product sections (repeater).
		'home_sections'            => array(),

		// Shop.
		'shop_products_per_page' => 12,
	);
}

/**
 * All saved options merged with defaults.
 *
 * @return array<string, mixed>
 */
function kids_shop_get_all_options() {
	$saved = get_option( KIDS_SHOP_OPTIONS_KEY, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return wp_parse_args( $saved, kids_shop_get_default_options() );
}

/**
 * Get a single theme option.
 *
 * @param string $key     Option key.
 * @param mixed  $default Fallback default.
 * @return mixed
 */
function kids_shop_get_option( $key, $default = null ) {
	$options = kids_shop_get_all_options();
	if ( array_key_exists( $key, $options ) ) {
		return $options[ $key ];
	}
	if ( null !== $default ) {
		return $default;
	}
	$defaults = kids_shop_get_default_options();
	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
}

/**
 * Logo URL (custom upload or theme default).
 *
 * @return string
 */
function kids_shop_get_logo_url() {
	$logo_id = (int) kids_shop_get_option( 'logo_id', 0 );
	if ( $logo_id ) {
		$url = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( $url ) {
			return $url;
		}
	}
	return get_template_directory_uri() . '/assets/gemini-generated-image-dzqentdzqentdzqe-29a1.webp';
}

/**
 * Validate a media library image attachment ID.
 *
 * @param mixed $attachment_id Attachment ID.
 * @return int Valid attachment ID or 0.
 */
function kids_shop_validate_image_attachment_id( $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id ) {
		return 0;
	}

	$post = get_post( $attachment_id );
	if ( ! $post || 'attachment' !== $post->post_type ) {
		return 0;
	}

	if ( ! wp_attachment_is_image( $attachment_id ) ) {
		return 0;
	}

	return $attachment_id;
}

/**
 * Normalize one hero slide row.
 *
 * @param array<string, mixed> $slide Raw slide data.
 * @return array{image: int, image_url: string, link: string, alt: string}
 */
function kids_shop_normalize_hero_slide( $slide ) {
	$defaults = array(
		'image'     => 0,
		'image_url' => '',
		'link'      => '',
		'alt'       => '',
	);

	$slide    = wp_parse_args( is_array( $slide ) ? $slide : array(), $defaults );
	$image_id = kids_shop_validate_image_attachment_id( $slide['image'] );
	$url      = '';

	if ( $image_id ) {
		$url = wp_get_attachment_image_url( $image_id, 'full' );
		$url = $url ? $url : '';
	} elseif ( ! empty( $slide['image_url'] ) ) {
		$url = esc_url_raw( (string) $slide['image_url'] );
	}

	return array(
		'image'     => $image_id,
		'image_url' => $url ? $url : '',
		'link'      => kids_shop_sanitize_view_all_url( $slide['link'] ),
		'alt'       => sanitize_text_field( (string) $slide['alt'] ),
	);
}

/**
 * Migrate legacy hero_slide_1_* … hero_slide_4_* options.
 *
 * @return array<int, array{image: int, link: string, alt: string}>
 */
function kids_shop_migrate_legacy_hero_slides() {
	$saved = get_option( KIDS_SHOP_OPTIONS_KEY, array() );
	if ( ! is_array( $saved ) ) {
		return array();
	}

	$slides = array();
	for ( $i = 1; $i <= 4; $i++ ) {
		$image_key = 'hero_slide_' . $i . '_image';
		$link_key  = 'hero_slide_' . $i . '_link';
		$alt_key   = 'hero_slide_' . $i . '_alt';

		if ( ! isset( $saved[ $image_key ] ) && ! isset( $saved[ $link_key ] ) && ! isset( $saved[ $alt_key ] ) ) {
			continue;
		}

		$slides[] = kids_shop_normalize_hero_slide(
			array(
				'image' => isset( $saved[ $image_key ] ) ? $saved[ $image_key ] : 0,
				'link'  => isset( $saved[ $link_key ] ) ? $saved[ $link_key ] : '',
				'alt'   => isset( $saved[ $alt_key ] ) ? $saved[ $alt_key ] : '',
			)
		);
	}

	return $slides;
}

/**
 * Saved hero slides for admin and front end.
 *
 * @return array<int, array{image: int, link: string, alt: string}>
 */
function kids_shop_get_hero_slides_config() {
	$saved  = get_option( KIDS_SHOP_OPTIONS_KEY, array() );
	$slides = ( is_array( $saved ) && ! empty( $saved['hero_slides'] ) && is_array( $saved['hero_slides'] ) )
		? $saved['hero_slides']
		: array();

	if ( empty( $slides ) ) {
		$slides = kids_shop_migrate_legacy_hero_slides();
	}

	$normalized = array();
	foreach ( $slides as $slide ) {
		$row = kids_shop_normalize_hero_slide( $slide );
		if ( $row['image'] || $row['image_url'] || '' !== $row['link'] || '' !== $row['alt'] ) {
			$normalized[] = $row;
		}
	}

	return $normalized;
}

/**
 * Hero slide image URL from media library attachment.
 *
 * @param int $image_id Attachment ID.
 * @return string
 */
function kids_shop_get_hero_slide_image_url( $image_id, $fallback_url = '' ) {
	$image_id = kids_shop_validate_image_attachment_id( $image_id );
	if ( $image_id ) {
		$url = wp_get_attachment_image_url( $image_id, 'full' );
		if ( $url ) {
			return $url;
		}
	}

	$fallback_url = esc_url_raw( (string) $fallback_url );

	return $fallback_url ? $fallback_url : '';
}

/**
 * Output CSS custom properties for brand colors.
 */
function kids_shop_output_theme_colors_css() {
	$primary   = sanitize_hex_color( kids_shop_get_option( 'color_primary', '#27A7B8' ) );
	$secondary = sanitize_hex_color( kids_shop_get_option( 'color_secondary', '#D12C60' ) );
	$tertiary  = sanitize_hex_color( kids_shop_get_option( 'color_tertiary', '#e8007c' ) );

	if ( ! $primary ) {
		$primary = '#27A7B8';
	}
	if ( ! $secondary ) {
		$secondary = '#D12C60';
	}
	if ( ! $tertiary ) {
		$tertiary = '#e8007c';
	}

	printf(
		'<style id="kids-shop-theme-colors">:root{--shop-color-primary:%1$s;--shop-color-secondary:%2$s;--shop-color-tertiary:%3$s;}html{--shop-color-primary:%1$s;--shop-color-secondary:%2$s;--shop-color-tertiary:%3$s;}</style>' . "\n",
		esc_attr( $primary ),
		esc_attr( $secondary ),
		esc_attr( $tertiary )
	);
}
add_action( 'wp_head', 'kids_shop_output_theme_colors_css', 5 );

/**
 * Default home page product sections.
 *
 * @return array<int, array{title: string, type: string, category: string, limit: int}>
 */
function kids_shop_get_default_home_sections() {
	return array(
		array(
			'title'         => 'Winter Collection',
			'type'          => 'category',
			'category'      => 'winter-collection',
			'limit'         => 5,
			'view_all_text' => 'View All',
			'view_all_url'  => '',
		),
		array(
			'title'         => 'Flash Deals',
			'type'          => 'on_sale',
			'category'      => '',
			'limit'         => 5,
			'view_all_text' => 'View All',
			'view_all_url'  => '',
		),
		array(
			'title'         => 'Popular products',
			'type'          => 'popular',
			'category'      => '',
			'limit'         => 5,
			'view_all_text' => 'View All',
			'view_all_url'  => '',
		),
	);
}

/**
 * Allowed product source types for a home section.
 *
 * @return string[]
 */
function kids_shop_get_home_section_types() {
	return array( 'category', 'on_sale', 'popular', 'featured' );
}

/**
 * Normalize one home section row.
 *
 * @param array<string, mixed> $section Raw section data.
 * @return array{title: string, type: string, category: string, limit: int, view_all_text: string, view_all_url: string}
 */
function kids_shop_normalize_home_section( $section ) {
	$defaults = array(
		'title'         => '',
		'type'          => 'category',
		'category'      => '',
		'limit'         => 5,
		'view_all_text' => 'View All',
		'view_all_url'  => '',
	);

	$section = wp_parse_args( is_array( $section ) ? $section : array(), $defaults );
	$type    = in_array( $section['type'], kids_shop_get_home_section_types(), true ) ? $section['type'] : 'category';

	return array(
		'title'         => sanitize_text_field( (string) $section['title'] ),
		'type'          => $type,
		'category'      => sanitize_title( (string) $section['category'] ),
		'limit'         => max( 1, min( 12, (int) $section['limit'] ) ),
		'view_all_text' => sanitize_text_field( (string) $section['view_all_text'] ),
		'view_all_url'  => kids_shop_sanitize_view_all_url( $section['view_all_url'] ),
	);
}

/**
 * Sanitize View All URL (absolute, protocol-relative, or site-relative).
 *
 * @param string $url Raw URL.
 * @return string
 */
function kids_shop_sanitize_view_all_url( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '';
	}

	if ( str_starts_with( $url, '/' ) ) {
		return esc_url_raw( home_url( $url ) );
	}

	if ( str_starts_with( $url, '//' ) ) {
		return esc_url_raw( 'https:' . $url );
	}

	$sanitized = esc_url_raw( $url );
	if ( $sanitized ) {
		return $sanitized;
	}

	if ( ! preg_match( '#^https?://#i', $url ) ) {
		$sanitized = esc_url_raw( 'https://' . ltrim( $url, '/' ) );
	}

	return $sanitized ? $sanitized : '';
}

/**
 * Build home sections from legacy flat option keys (pre-repeater).
 *
 * @return array<int, array{title: string, type: string, category: string, limit: int}>
 */
function kids_shop_migrate_legacy_home_sections() {
	$saved = get_option( KIDS_SHOP_OPTIONS_KEY, array() );
	if ( ! is_array( $saved ) ) {
		return kids_shop_get_default_home_sections();
	}

	if ( empty( $saved['home_section_1_title'] ) && empty( $saved['home_section_2_title'] ) ) {
		return kids_shop_get_default_home_sections();
	}

	return array(
		kids_shop_normalize_home_section(
			array(
				'title'    => isset( $saved['home_section_1_title'] ) ? $saved['home_section_1_title'] : 'Winter Collection',
				'type'     => 'category',
				'category' => isset( $saved['home_section_1_category'] ) ? $saved['home_section_1_category'] : 'winter-collection',
				'limit'    => isset( $saved['home_section_1_limit'] ) ? $saved['home_section_1_limit'] : 5,
			)
		),
		kids_shop_normalize_home_section(
			array(
				'title'    => isset( $saved['home_section_2_title'] ) ? $saved['home_section_2_title'] : 'Flash Deals',
				'type'     => isset( $saved['home_section_2_type'] ) ? $saved['home_section_2_type'] : 'on_sale',
				'category' => '',
				'limit'    => isset( $saved['home_section_2_limit'] ) ? $saved['home_section_2_limit'] : 5,
			)
		),
		kids_shop_normalize_home_section(
			array(
				'title'    => isset( $saved['home_section_3_title'] ) ? $saved['home_section_3_title'] : 'Popular products',
				'type'     => isset( $saved['home_section_3_type'] ) ? $saved['home_section_3_type'] : 'popular',
				'category' => '',
				'limit'    => isset( $saved['home_section_3_limit'] ) ? $saved['home_section_3_limit'] : 5,
			)
		),
	);
}

/**
 * Saved home sections for admin and front end.
 *
 * @return array<int, array{title: string, type: string, category: string, limit: int}>
 */
function kids_shop_get_home_sections_config() {
	$options  = kids_shop_get_all_options();
	$sections = isset( $options['home_sections'] ) && is_array( $options['home_sections'] ) ? $options['home_sections'] : array();

	if ( empty( $sections ) ) {
		$sections = kids_shop_migrate_legacy_home_sections();
	}

	$normalized = array();
	foreach ( $sections as $section ) {
		$row = kids_shop_normalize_home_section( $section );
		if ( '' !== $row['title'] ) {
			$normalized[] = $row;
		}
	}

	if ( empty( $normalized ) ) {
		$normalized = kids_shop_get_default_home_sections();
	}

	return $normalized;
}

/**
 * WhatsApp chat link from saved number.
 *
 * @return string
 */
function kids_shop_get_whatsapp_url() {
	$number = preg_replace( '/[^0-9+]/', '', (string) kids_shop_get_option( 'social_whatsapp', '' ) );
	if ( ! $number ) {
		return '';
	}
	return 'https://wa.me/' . rawurlencode( $number ) . '?text=' . rawurlencode( 'Hello! I need help with your services' );
}
