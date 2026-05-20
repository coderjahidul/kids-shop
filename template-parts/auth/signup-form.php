<?php
/**
 * Sign up form (WooCommerce customer registration).
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$login_url = kids_shop_get_login_url();
$wc_reg    = kids_shop_is_wc_registration_enabled();
$action    = kids_shop_get_signup_url();
?>
<form class="kids-shop-auth-form" method="post" action="<?php echo esc_url( $action ); ?>" novalidate aria-labelledby="kids-shop-signup-form-label">
	<p id="kids-shop-signup-form-label" class="kids-shop-auth-form__label"><?php esc_html_e( 'SignUp', 'kids-shop' ); ?></p>

	<?php if ( $wc_reg ) : ?>
		<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>

		<div class="kids-shop-auth-field">
			<label for="reg_first_name"><?php esc_html_e( 'Name', 'kids-shop' ); ?> <span class="required">*</span></label>
			<input
				type="text"
				name="first_name"
				id="reg_first_name"
				class="kids-shop-auth-input"
				placeholder="<?php esc_attr_e( 'Enter full name', 'kids-shop' ); ?>"
				autocomplete="name"
				value="<?php echo isset( $_POST['first_name'] ) ? esc_attr( wp_unslash( $_POST['first_name'] ) ) : ''; ?>"
				required
			/>
		</div>

		<div class="kids-shop-auth-field">
			<label for="reg_email"><?php esc_html_e( 'Email or Phone No', 'kids-shop' ); ?> <span class="required">*</span></label>
			<input
				type="text"
				name="email"
				id="reg_email"
				class="kids-shop-auth-input"
				placeholder="<?php esc_attr_e( 'Enter your phone number or email', 'kids-shop' ); ?>"
				autocomplete="email"
				value="<?php echo isset( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>"
				required
			/>
		</div>

		<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
			<input type="hidden" name="username" id="reg_username_hidden" value="" />
		<?php endif; ?>

		<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
			<div class="kids-shop-auth-field">
				<label for="reg_password"><?php esc_html_e( 'Password', 'kids-shop' ); ?> <span class="required">*</span></label>
				<div class="kids-shop-auth-input-wrap">
					<input
						type="password"
						name="password"
						id="reg_password"
						class="kids-shop-auth-input"
						placeholder="<?php esc_attr_e( 'Enter password', 'kids-shop' ); ?>"
						autocomplete="new-password"
						minlength="6"
						required
					/>
					<button type="button" class="kids-shop-auth-toggle-pw" aria-label="<?php esc_attr_e( 'Show password', 'kids-shop' ); ?>" data-target="reg_password">
						<svg class="kids-shop-auth-eye kids-shop-auth-eye--show" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
							<path d="M12 5C7 5 2.73 8.11 1 12c1.73 3.89 6 7 11 7s9.27-3.11 11-7c-1.73-3.89-6-7-11-7zm0 12a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z" fill="currentColor"/>
						</svg>
						<svg class="kids-shop-auth-eye kids-shop-auth-eye--hide" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
							<path d="M12 6.5c2.76 0 5.26 1.12 7.07 2.93l1.41-1.41C18.1 5.39 15.22 4 12 4c-1.85 0-3.55.5-5.03 1.37l1.44 1.44C9.17 6.83 10.53 6.5 12 6.5zM3.27 3 2 4.27l2.04 2.04C2.51 7.97 1.5 9.88 1 12c1.73 3.89 6 7 11 7 1.89 0 3.67-.5 5.24-1.35L20.73 22 22 20.73 3.27 3zm6.53 6.53C9.76 8.49 10.83 8 12 8c2.21 0 4 1.79 4 4 0 1.17-.49 2.24-1.2 3.2l1.42 1.42A6.92 6.92 0 0019 12c0-3.31-2.69-6-6-6-1.66 0-3.14.68-4.22 1.78l1.52 1.75z" fill="currentColor"/>
						</svg>
					</button>
				</div>
				<p class="kids-shop-auth-field-hint kids-shop-auth-field-hint--solo"><?php esc_html_e( 'Password must have 6 characters', 'kids-shop' ); ?></p>
			</div>
		<?php else : ?>
			<p class="kids-shop-auth-hint"><?php esc_html_e( 'A link to set your password will be sent to your email.', 'kids-shop' ); ?></p>
		<?php endif; ?>

		<?php do_action( 'woocommerce_register_form' ); ?>

		<button type="submit" class="kids-shop-auth-btn" name="register" value="<?php esc_attr_e( 'Register', 'kids-shop' ); ?>"><?php esc_html_e( 'Signup', 'kids-shop' ); ?></button>
	<?php else : ?>
		<div class="kids-shop-auth-notice kids-shop-auth-notice--info">
			<p><?php esc_html_e( 'Registration is currently disabled. Please contact the store administrator or log in if you already have an account.', 'kids-shop' ); ?></p>
		</div>
		<a class="kids-shop-auth-btn kids-shop-auth-btn--outline" href="<?php echo esc_url( $login_url ); ?>"><?php esc_html_e( 'Go to Login', 'kids-shop' ); ?></a>
	<?php endif; ?>

	<p class="kids-shop-auth-switch">
		<?php esc_html_e( 'Already have an account?', 'kids-shop' ); ?>
		<a href="<?php echo esc_url( $login_url ); ?>"><?php esc_html_e( 'Login Here', 'kids-shop' ); ?></a>
	</p>
</form>
