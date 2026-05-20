<?php
/**
 * Account profile summary card.
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$profile = kids_shop_get_account_profile_data();
?>
<div class="kids-shop-myaccount-profile">
	<div class="kids-shop-myaccount-profile__avatar" aria-hidden="true">
		<?php if ( $profile['initials'] ) : ?>
			<span class="kids-shop-myaccount-profile__initials"><?php echo esc_html( $profile['initials'] ); ?></span>
		<?php else : ?>
			<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
				<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
				<circle cx="12" cy="7" r="4"/>
			</svg>
		<?php endif; ?>
	</div>

	<?php if ( $profile['name'] ) : ?>
		<h2 class="kids-shop-myaccount-profile__name"><?php echo esc_html( $profile['name'] ); ?></h2>
	<?php endif; ?>

	<ul class="kids-shop-myaccount-profile__meta">
		<li>
			<span class="kids-shop-myaccount-profile__meta-label"><?php esc_html_e( 'Phone No:', 'kids-shop' ); ?></span>
			<?php echo esc_html( $profile['phone'] ); ?>
		</li>
		<li>
			<span class="kids-shop-myaccount-profile__meta-label"><?php esc_html_e( 'Email:', 'kids-shop' ); ?></span>
			<?php echo esc_html( $profile['email'] ); ?>
		</li>
		<?php if ( $profile['member_since'] ) : ?>
			<li>
				<span class="kids-shop-myaccount-profile__meta-label"><?php esc_html_e( 'Member Since:', 'kids-shop' ); ?></span>
				<?php echo esc_html( $profile['member_since'] ); ?>
			</li>
		<?php endif; ?>
	</ul>

	<?php if ( $profile['edit_url'] ) : ?>
		<a class="kids-shop-myaccount-btn kids-shop-myaccount-btn--outline kids-shop-myaccount-profile__edit" href="<?php echo esc_url( $profile['edit_url'] ); ?>">
			<?php esc_html_e( 'Edit', 'kids-shop' ); ?>
		</a>
	<?php endif; ?>
</div>
