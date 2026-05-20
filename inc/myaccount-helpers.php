<?php
/**
 * My Account page helpers.
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Current WooCommerce account endpoint (dashboard when none).
 *
 * @return string
 */
function kids_shop_get_account_endpoint() {
	if ( ! function_exists( 'WC' ) || ! WC()->query ) {
		return 'dashboard';
	}

	global $wp;

	foreach ( WC()->query->get_query_vars() as $key => $value ) {
		if ( isset( $wp->query_vars[ $key ] ) ) {
			return $key;
		}
	}

	return 'dashboard';
}

/**
 * Whether the account view is the dashboard home layout.
 *
 * @return bool
 */
function kids_shop_is_account_dashboard() {
	return 'dashboard' === kids_shop_get_account_endpoint();
}

/**
 * Endpoints that use sidebar + wide main (no profile column), matching reference UI.
 *
 * @return array<int, string>
 */
function kids_shop_account_two_column_endpoints() {
	return apply_filters(
		'kids_shop_account_two_column_endpoints',
		array( 'orders', 'view-order', 'edit-address', 'edit-account' )
	);
}

/**
 * Whether the current account view uses two-column layout (no profile card).
 *
 * @return bool
 */
function kids_shop_account_is_two_column_layout() {
	return in_array( kids_shop_get_account_endpoint(), kids_shop_account_two_column_endpoints(), true );
}

/**
 * Address sub-type when editing (billing|shipping), or empty on the address list view.
 *
 * @return string
 */
function kids_shop_get_account_address_type() {
	global $wp;

	if ( ! isset( $wp->query_vars['edit-address'] ) ) {
		return '';
	}

	$slug = sanitize_key( (string) $wp->query_vars['edit-address'] );

	if ( in_array( $slug, array( 'billing', 'shipping' ), true ) ) {
		return $slug;
	}

	return '';
}

/**
 * Order list filter tabs (slug => label). Slugs match WooCommerce order statuses where applicable.
 *
 * @return array<string, string>
 */
function kids_shop_get_account_order_filter_tabs() {
	return apply_filters(
		'kids_shop_account_order_filter_tabs',
		array(
			'all'        => __( 'All', 'kids-shop' ),
			'processing' => __( 'In Process', 'kids-shop' ),
			'pending'    => __( 'Pending', 'kids-shop' ),
			'on-hold'    => __( 'Confirmed', 'kids-shop' ),
			'completed'  => __( 'Delivered', 'kids-shop' ),
			'cancelled'  => __( 'Cancelled', 'kids-shop' ),
		)
	);
}

/**
 * Sidebar navigation items (KiddoMart-style).
 *
 * @return array<int, array<string, string>>
 */
function kids_shop_get_account_nav_items() {
	$base = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );

	$items = array(
		array(
			'id'       => 'dashboard',
			'label'    => __( 'My Account', 'kids-shop' ),
			'url'      => $base,
			'endpoint' => 'dashboard',
			'icon'     => 'user',
		),
		array(
			'id'       => 'orders',
			'label'    => __( 'My Orders', 'kids-shop' ),
			'url'      => wc_get_endpoint_url( 'orders', '', $base ),
			'endpoint' => 'orders',
			'icon'     => 'bag',
		),
		array(
			'id'       => 'edit-address',
			'label'    => __( 'My Address', 'kids-shop' ),
			'url'      => wc_get_endpoint_url( 'edit-address', '', $base ),
			'endpoint' => 'edit-address',
			'icon'     => 'pin',
		),
		array(
			'id'       => 'edit-account',
			'label'    => __( 'Setting', 'kids-shop' ),
			'url'      => wc_get_endpoint_url( 'edit-account', '', $base ),
			'endpoint' => 'edit-account',
			'icon'     => 'gear',
		),
		array(
			'id'       => 'customer-logout',
			'label'    => __( 'Logout', 'kids-shop' ),
			'url'      => wc_get_endpoint_url( 'customer-logout', '', $base ),
			'endpoint' => 'customer-logout',
			'icon'     => 'logout',
		),
	);

	return apply_filters( 'kids_shop_account_nav_items', $items );
}

/**
 * Whether a nav item should appear active.
 *
 * @param array<string, string> $item Nav item.
 * @return bool
 */
function kids_shop_is_account_nav_active( $item ) {
	$current = kids_shop_get_account_endpoint();

	if ( in_array( $item['endpoint'], array( 'wishlists', 'reviews' ), true ) ) {
		return 'dashboard' === $current;
	}

	if ( 'orders' === $item['endpoint'] ) {
		return in_array( $current, array( 'orders', 'view-order' ), true );
	}

	return $current === $item['endpoint'];
}

/**
 * Profile summary for the account sidebar card.
 *
 * @return array<string, string>
 */
function kids_shop_get_account_profile_data() {
	$user = wp_get_current_user();

	if ( ! $user->ID ) {
		return array(
			'name'          => '',
			'email'         => '',
			'phone'         => '-',
			'member_since'  => '',
			'edit_url'      => '',
			'initials'      => '',
		);
	}

	$name = $user->display_name;
	if ( ! $name ) {
		$name = trim( $user->first_name . ' ' . $user->last_name );
	}
	if ( ! $name ) {
		$name = $user->user_login;
	}

	$phone = get_user_meta( $user->ID, 'billing_phone', true );
	if ( ! $phone ) {
		$phone = '-';
	}

	$registered = $user->user_registered;
	$member     = $registered ? date_i18n( 'M j, Y', strtotime( $registered ) ) : '';

	$parts    = preg_split( '/\s+/', trim( $name ), 2 );
	$initials = '';
	if ( ! empty( $parts[0] ) ) {
		$initials .= mb_strtoupper( mb_substr( $parts[0], 0, 1 ) );
	}
	if ( ! empty( $parts[1] ) ) {
		$initials .= mb_strtoupper( mb_substr( $parts[1], 0, 1 ) );
	}
	if ( ! $initials ) {
		$initials = mb_strtoupper( mb_substr( $user->user_login, 0, 1 ) );
	}

	$base     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '';
	$edit_url = $base ? wc_get_endpoint_url( 'edit-account', '', $base ) : '';

	return array(
		'name'         => $name,
		'email'        => $user->user_email,
		'phone'        => $phone,
		'member_since' => $member,
		'edit_url'     => $edit_url,
		'initials'     => $initials,
	);
}

/**
 * Customer order count.
 *
 * @return int
 */
function kids_shop_get_customer_order_count() {
	if ( ! is_user_logged_in() || ! function_exists( 'wc_get_customer_order_count' ) ) {
		return 0;
	}

	return (int) wc_get_customer_order_count( get_current_user_id() );
}

/**
 * Inline SVG icon for account navigation.
 *
 * @param string $icon Icon key.
 * @return string
 */
function kids_shop_account_nav_icon( $icon ) {
	$icons = array(
		'user'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
		'bag'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
		'heart'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
		'pin'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
		'star'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
		'gear'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
		'logout' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
	);

	return isset( $icons[ $icon ] ) ? $icons[ $icon ] : $icons['user'];
}

/**
 * Empty-state folder illustration.
 *
 * @return string
 */
function kids_shop_account_empty_illustration() {
	return '<svg class="kids-shop-myaccount-empty__illus" width="120" height="80" viewBox="0 0 120 80" fill="none" aria-hidden="true">'
		. '<rect x="18" y="28" width="36" height="28" rx="4" fill="#E8ECEF"/>'
		. '<rect x="44" y="20" width="36" height="28" rx="4" fill="#D5DCE3"/>'
		. '<rect x="66" y="32" width="36" height="28" rx="4" fill="#E8ECEF"/>'
		. '<circle cx="60" cy="44" r="10" fill="#fff" stroke="#C5CED6" stroke-width="2"/>'
		. '<path d="M56 44h8M60 40v8" stroke="#9AA8B4" stroke-width="2" stroke-linecap="round"/>'
		. '</svg>';
}

/**
 * Remove default WooCommerce account navigation (theme provides its own).
 */
function kids_shop_remove_default_account_navigation() {
	remove_action( 'woocommerce_account_navigation', 'woocommerce_account_navigation' );
}
add_action( 'init', 'kids_shop_remove_default_account_navigation' );

/**
 * Remove default dashboard intro text on custom dashboard.
 */
function kids_shop_remove_default_account_dashboard() {
	if ( kids_shop_is_account_dashboard() ) {
		remove_action( 'woocommerce_account_dashboard', 'woocommerce_account_dashboard' );
	}
}
add_action( 'woocommerce_account_dashboard', 'kids_shop_remove_default_account_dashboard', 1 );
