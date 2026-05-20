<?php
/**
 * Login form.
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$redirect   = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';
$signup_url = kids_shop_get_signup_url();

if ( ! $redirect ) {
	$redirect = kids_shop_get_auth_redirect_url();
}
?>
<form class="kids-shop-auth-form" name="loginform" id="kids-shop-loginform" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post" novalidate aria-labelledby="kids-shop-login-form-label">
	<p id="kids-shop-login-form-label" class="kids-shop-auth-form__label"><?php esc_html_e( 'Login', 'kids-shop' ); ?></p>

	<div class="kids-shop-auth-field">
		<label for="user_login"><?php esc_html_e( 'Email or Phone No', 'kids-shop' ); ?> <span class="required">*</span></label>
		<input
			type="text"
			name="log"
			id="user_login"
			class="kids-shop-auth-input"
			placeholder="<?php esc_attr_e( 'Enter your phone number or email', 'kids-shop' ); ?>"
			autocomplete="username"
			required
		/>
	</div>

	<div class="kids-shop-auth-field">
		<label for="user_pass"><?php esc_html_e( 'Password', 'kids-shop' ); ?> <span class="required">*</span></label>
		<div class="kids-shop-auth-input-wrap">
			<input
				type="password"
				name="pwd"
				id="user_pass"
				class="kids-shop-auth-input"
				placeholder="<?php esc_attr_e( 'Enter your password', 'kids-shop' ); ?>"
				autocomplete="current-password"
				minlength="6"
				required
			/>
			<button type="button" class="kids-shop-auth-toggle-pw" aria-label="<?php esc_attr_e( 'Show password', 'kids-shop' ); ?>" data-target="user_pass">
				<svg class="kids-shop-auth-eye kids-shop-auth-eye--show" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M12 5C7 5 2.73 8.11 1 12c1.73 3.89 6 7 11 7s9.27-3.11 11-7c-1.73-3.89-6-7-11-7zm0 12a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z" fill="currentColor"/>
				</svg>
				<svg class="kids-shop-auth-eye kids-shop-auth-eye--hide" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M12 6.5c2.76 0 5.26 1.12 7.07 2.93l1.41-1.41C18.1 5.39 15.22 4 12 4c-1.85 0-3.55.5-5.03 1.37l1.44 1.44C9.17 6.83 10.53 6.5 12 6.5zM3.27 3 2 4.27l2.04 2.04C2.51 7.97 1.5 9.88 1 12c1.73 3.89 6 7 11 7 1.89 0 3.67-.5 5.24-1.35L20.73 22 22 20.73 3.27 3zm6.53 6.53C9.76 8.49 10.83 8 12 8c2.21 0 4 1.79 4 4 0 1.17-.49 2.24-1.2 3.2l1.42 1.42A6.92 6.92 0 0019 12c0-3.31-2.69-6-6-6-1.66 0-3.14.68-4.22 1.78l1.52 1.75z" fill="currentColor"/>
				</svg>
			</button>
		</div>
		<div class="kids-shop-auth-field-meta">
			<span class="kids-shop-auth-field-hint"><?php esc_html_e( 'Password must have 6 characters', 'kids-shop' ); ?></span>
			<a class="kids-shop-auth-link kids-shop-auth-link--forget" href="<?php echo esc_url( wp_lostpassword_url( kids_shop_get_login_url() ) ); ?>"><?php esc_html_e( 'Forget password?', 'kids-shop' ); ?></a>
		</div>
	</div>

	<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect ); ?>" />
	<input type="hidden" name="testcookie" value="1" />

	<button type="submit" name="wp-submit" class="kids-shop-auth-btn"><?php esc_html_e( 'Login', 'kids-shop' ); ?></button>

	<p class="kids-shop-auth-switch">
		<?php esc_html_e( "Don't have an account?", 'kids-shop' ); ?>
		<a href="<?php echo esc_url( $signup_url ); ?>"><?php esc_html_e( 'Create an account', 'kids-shop' ); ?></a>
	</p>
</form>
