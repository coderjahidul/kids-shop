<?php
/**
 * Edit account — settings layout: password + account status (KiddoMart-style).
 *
 * @package Kids_Shop
 * @version 10.5.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_edit_account_form' );

$base     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '';
$back_url = $base ? trailingslashit( $base ) : '';
?>
<div class="kids-shop-edit-account">
	<?php if ( $back_url ) : ?>
		<a class="kids-shop-edit-account__back" href="<?php echo esc_url( $back_url ); ?>">
			<span class="kids-shop-edit-account__back-icon" aria-hidden="true">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
			</span>
			<?php esc_html_e( 'Back to My Account', 'kids-shop' ); ?>
		</a>
	<?php endif; ?>

	<header class="kids-shop-edit-account__page-header">
		<span class="kids-shop-edit-account__page-icon" aria-hidden="true">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
		</span>
		<div class="kids-shop-edit-account__page-intro">
			<h1 class="kids-shop-edit-account__page-title"><?php esc_html_e( 'Settings', 'kids-shop' ); ?></h1>
			<p class="kids-shop-edit-account__page-subtitle"><?php esc_html_e( 'Manage your password and account preferences.', 'kids-shop' ); ?></p>
		</div>
	</header>

	<div class="kids-shop-edit-account__shell">
		<div class="kids-shop-edit-account__grid">
			<form class="woocommerce-EditAccountForm edit-account kids-shop-edit-account__form" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?>>
				<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

				<input type="hidden" name="account_first_name" value="<?php echo esc_attr( $user->first_name ); ?>" autocomplete="given-name" />
				<input type="hidden" name="account_last_name" value="<?php echo esc_attr( $user->last_name ); ?>" autocomplete="family-name" />
				<input type="hidden" name="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>" />
				<input type="hidden" name="account_email" value="<?php echo esc_attr( $user->user_email ); ?>" autocomplete="email" />

				<div class="kids-shop-edit-account__panel kids-shop-edit-account__panel--password">
					<h2 class="kids-shop-edit-account__heading"><?php esc_html_e( 'Password Manage', 'kids-shop' ); ?></h2>

					<div class="kids-shop-edit-account__extra-fields">
						<?php do_action( 'woocommerce_edit_account_form_fields' ); ?>
					</div>

					<fieldset class="kids-shop-edit-account__fieldset">
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide kids-shop-edit-account__password-row">
							<label for="password_current"><?php esc_html_e( 'Old Password', 'kids-shop' ); ?></label>
							<span class="kids-shop-password-wrap">
								<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" autocomplete="current-password" placeholder="<?php esc_attr_e( 'Leave blank to keep current password', 'kids-shop' ); ?>" />
								<button type="button" class="kids-shop-password-toggle" aria-label="<?php esc_attr_e( 'Show password', 'kids-shop' ); ?>" aria-pressed="false" data-target="password_current"></button>
							</span>
						</p>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide kids-shop-edit-account__password-row">
							<label for="password_1"><?php esc_html_e( 'Password', 'kids-shop' ); ?></label>
							<span class="kids-shop-password-wrap">
								<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" autocomplete="new-password" placeholder="<?php esc_attr_e( 'New password', 'kids-shop' ); ?>" />
								<button type="button" class="kids-shop-password-toggle" aria-label="<?php esc_attr_e( 'Show password', 'kids-shop' ); ?>" aria-pressed="false" data-target="password_1"></button>
							</span>
						</p>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide kids-shop-edit-account__password-row">
							<label for="password_2"><?php esc_html_e( 'Confirm Password', 'kids-shop' ); ?></label>
							<span class="kids-shop-password-wrap">
								<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" autocomplete="new-password" placeholder="<?php esc_attr_e( 'Confirm new password', 'kids-shop' ); ?>" />
								<button type="button" class="kids-shop-password-toggle" aria-label="<?php esc_attr_e( 'Show password', 'kids-shop' ); ?>" aria-pressed="false" data-target="password_2"></button>
							</span>
						</p>
					</fieldset>

					<div class="kids-shop-edit-account__after-password">
						<?php do_action( 'woocommerce_edit_account_form' ); ?>
					</div>

					<p class="kids-shop-edit-account__submit-wrap">
						<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
						<button type="submit" class="woocommerce-Button button kids-shop-edit-account__save<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>">
							<?php esc_html_e( 'Save & Changes', 'kids-shop' ); ?>
						</button>
						<input type="hidden" name="action" value="save_account_details" />
					</p>
				</div>

				<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
			</form>

			<div class="kids-shop-edit-account__panel kids-shop-edit-account__panel--status" aria-labelledby="kids-shop-account-status-heading">
				<h2 id="kids-shop-account-status-heading" class="kids-shop-edit-account__heading"><?php esc_html_e( 'Account Status', 'kids-shop' ); ?></h2>
				<p class="kids-shop-edit-account__status-text">
					<?php
					printf(
						/* translators: wrapped word "active" */
						wp_kses_post( __( 'Your account status is <strong class="kids-shop-edit-account__status-active">active</strong>. You can disable or inactive account below.', 'kids-shop' ) )
					);
					?>
				</p>
				<label class="kids-shop-edit-account__reason-label" for="kids_shop_account_inactive_reason"><?php esc_html_e( 'Explain the reason', 'kids-shop' ); ?></label>
				<textarea id="kids_shop_account_inactive_reason" class="kids-shop-edit-account__reason" rows="4" placeholder="<?php esc_attr_e( 'Write Reason for inactive account', 'kids-shop' ); ?>"></textarea>
				<button type="button" class="kids-shop-edit-account__deactivate" id="kids-shop-deactivate-account">
					<?php esc_html_e( 'Deactivate Account', 'kids-shop' ); ?>
				</button>
				<p class="kids-shop-edit-account__deactivate-note"><?php esc_html_e( 'This does not close your account automatically. Contact the store to remove your account.', 'kids-shop' ); ?></p>
			</div>
		</div>
	</div>
</div>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
