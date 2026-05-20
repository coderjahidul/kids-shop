<?php
/**
 * Login & Sign Up page helpers.
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login page URL.
 *
 * @return string
 */
function kids_shop_get_login_url() {
	$page = get_page_by_path( 'login' );
	if ( $page ) {
		return get_permalink( $page );
	}
	return home_url( '/login/' );
}

/**
 * Sign up page URL.
 *
 * @return string
 */
function kids_shop_get_signup_url() {
	$page = get_page_by_path( 'signup' );
	if ( $page ) {
		return get_permalink( $page );
	}
	return home_url( '/signup/' );
}

/**
 * My Account page URL.
 *
 * @return string
 */
function kids_shop_get_account_url() {
	return kids_shop_get_auth_redirect_url();
}

/**
 * Where to send users after login / registration.
 *
 * @return string
 */
function kids_shop_get_auth_redirect_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$account = wc_get_page_permalink( 'myaccount' );
		if ( $account ) {
			return $account;
		}
	}
	return home_url( '/' );
}

/**
 * Whether WooCommerce customer registration is enabled.
 *
 * @return bool
 */
function kids_shop_is_wc_registration_enabled() {
	return function_exists( 'wc' ) && 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
}

/**
 * Create Login / Sign Up pages if missing.
 */
function kids_shop_maybe_create_auth_pages() {
	if ( get_option( 'kids_shop_auth_pages_created' ) ) {
		return;
	}

	$pages = array(
		'login'  => array(
			'title'   => __( 'Login', 'kids-shop' ),
			'content' => '',
		),
		'signup' => array(
			'title'   => __( 'Sign Up', 'kids-shop' ),
			'content' => '',
		),
	);

	foreach ( $pages as $slug => $data ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}

		wp_insert_post(
			array(
				'post_title'   => $data['title'],
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => $data['content'],
			)
		);
	}

	update_option( 'kids_shop_auth_pages_created', 1 );
}
add_action( 'after_switch_theme', 'kids_shop_maybe_create_auth_pages' );
add_action( 'init', 'kids_shop_maybe_create_auth_pages' );

/**
 * Redirect logged-in users away from auth pages.
 */
function kids_shop_auth_redirect_logged_in() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( is_page( 'login' ) || is_page( 'signup' ) ) {
		wp_safe_redirect( kids_shop_get_auth_redirect_url() );
		exit;
	}
}
add_action( 'template_redirect', 'kids_shop_auth_redirect_logged_in' );

/**
 * Let WooCommerce process registration posted from the themed sign up page.
 *
 * @param bool $is_account_page Whether this is the account page.
 * @return bool
 */
function kids_shop_treat_signup_as_account_page( $is_account_page ) {
	if ( is_page( 'signup' ) && isset( $_POST['register'], $_POST['email'] ) ) {
		return true;
	}
	return $is_account_page;
}
add_filter( 'woocommerce_is_account_page', 'kids_shop_treat_signup_as_account_page' );

/**
 * Redirect to themed login after registering on the sign up page.
 *
 * @param string $redirect Default redirect URL.
 * @return string
 */
function kids_shop_registration_redirect( $redirect ) {
	if ( is_page( 'signup' ) ) {
		return add_query_arg( 'registered', '1', kids_shop_get_login_url() );
	}
	return $redirect;
}
add_filter( 'woocommerce_registration_redirect', 'kids_shop_registration_redirect' );

/**
 * Derive username from email/phone when WC requires a manual username.
 */
function kids_shop_signup_prefill_username() {
	if ( empty( $_POST['register'] ) || ! is_page( 'signup' ) ) {
		return;
	}
	if ( 'no' !== get_option( 'woocommerce_registration_generate_username' ) ) {
		return;
	}
	if ( ! empty( $_POST['username'] ) ) {
		return;
	}
	if ( empty( $_POST['email'] ) ) {
		return;
	}

	$raw   = wp_unslash( $_POST['email'] );
	$email = sanitize_email( $raw );

	if ( $email && is_email( $email ) ) {
		$_POST['username'] = sanitize_user( current( explode( '@', $email ) ), true );
	} else {
		$_POST['username'] = sanitize_user( preg_replace( '/\D+/', '', $raw ), true );
	}

	if ( empty( $_POST['username'] ) ) {
		$_POST['username'] = 'user_' . wp_generate_password( 8, false, false );
	}
}
add_action( 'init', 'kids_shop_signup_prefill_username', 5 );

/**
 * Save full name from themed signup form.
 *
 * @param int $customer_id New customer user ID.
 */
function kids_shop_save_signup_name( $customer_id ) {
	if ( empty( $_POST['first_name'] ) || ! is_page( 'signup' ) ) {
		return;
	}

	$name = sanitize_text_field( wp_unslash( $_POST['first_name'] ) );
	if ( '' === $name ) {
		return;
	}

	update_user_meta( $customer_id, 'first_name', $name );
	wp_update_user(
		array(
			'ID'           => $customer_id,
			'display_name' => $name,
		)
	);
}
add_action( 'woocommerce_created_customer', 'kids_shop_save_signup_name', 10, 1 );

/**
 * Login error message from wp-login redirect query args.
 *
 * @return string
 */
function kids_shop_get_login_error_message() {
	if ( isset( $_GET['loggedout'] ) && 'true' === $_GET['loggedout'] ) {
		return __( 'You have been logged out successfully.', 'kids-shop' );
	}

	if ( isset( $_GET['login'] ) ) {
		switch ( $_GET['login'] ) {
			case 'failed':
				return __( 'Invalid email/username or password. Please try again.', 'kids-shop' );
			case 'empty':
				return __( 'Please enter your username and password.', 'kids-shop' );
			case 'false':
				return __( 'You are now logged out.', 'kids-shop' );
		}
	}

	if ( isset( $_GET['registered'] ) && '1' === $_GET['registered'] ) {
		return __( 'Registration complete. You can log in now.', 'kids-shop' );
	}

	if ( isset( $_GET['checkemail'] ) && 'confirm' === $_GET['checkemail'] ) {
		return __( 'Check your email for the confirmation link.', 'kids-shop' );
	}

	if ( isset( $_GET['reset'] ) && 'true' === $_GET['reset'] ) {
		return __( 'Your password has been reset. You can log in now.', 'kids-shop' );
	}

	return '';
}

/**
 * Sign up error / success notices from WooCommerce registration.
 */
function kids_shop_print_auth_notices() {
	if ( function_exists( 'wc_print_notices' ) ) {
		wc_print_notices();
		return;
	}

	$message = kids_shop_get_login_error_message();
	if ( ! $message ) {
		return;
	}

	$type = ( isset( $_GET['loggedout'] ) || isset( $_GET['reset'] ) || isset( $_GET['registered'] ) ) ? 'success' : 'error';
	$class = 'success' === $type ? 'kids-shop-auth-notice--success' : 'kids-shop-auth-notice--error';

	printf(
		'<div class="kids-shop-auth-notice %1$s" role="alert"><p>%2$s</p></div>',
		esc_attr( $class ),
		esc_html( $message )
	);
}
