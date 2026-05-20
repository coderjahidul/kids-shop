<?php
/**
 * Thank you page — KiddoMart reference design.
 *
 * @package Kids_Shop
 * @version 8.1.0
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="woocommerce-order kids-shop-thankyou-order">
	<?php if ( $order ) : ?>

		<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<div class="kids-shop-thankyou-card kids-shop-thankyou-card--failed">
				<div class="kids-shop-thankyou-card__icon kids-shop-thankyou-card__icon--failed" aria-hidden="true">
					<svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="#fff"/>
					</svg>
				</div>
				<p class="kids-shop-thankyou-card__eyebrow"><?php esc_html_e( 'ORDER FAILED', 'kids-shop' ); ?></p>
				<h1 class="kids-shop-thankyou-card__headline"><?php esc_html_e( 'PAYMENT NOT COMPLETED', 'kids-shop' ); ?></h1>
				<p class="kids-shop-thankyou-card__message">
					<?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?>
				</p>
				<div class="kids-shop-thankyou-card__actions">
					<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="kids-shop-thankyou-card__btn">
						<?php esc_html_e( 'Pay', 'woocommerce' ); ?>
						<span class="kids-shop-thankyou-card__btn-arrow" aria-hidden="true">&rarr;</span>
					</a>
					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="kids-shop-thankyou-card__btn kids-shop-thankyou-card__btn--outline">
							<?php esc_html_e( 'My account', 'woocommerce' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

		<?php else : ?>

			<div class="kids-shop-thankyou-card">
				<div class="kids-shop-thankyou-card__icon" aria-hidden="true">
					<svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M9.55 16.45L5.1 12l1.41-1.41 3.04 3.04 7.05-7.05L18 7.91l-8.45 8.54z" fill="#fff"/>
					</svg>
				</div>
				<p class="kids-shop-thankyou-card__eyebrow"><?php esc_html_e( 'THANK YOU', 'kids-shop' ); ?></p>
				<h1 class="kids-shop-thankyou-card__headline"><?php esc_html_e( 'YOUR ORDER IS PLACED', 'kids-shop' ); ?></h1>
				<p class="kids-shop-thankyou-card__message">
					<?php esc_html_e( 'We received your order and will begin processing it soon. Your order information appears below.', 'kids-shop' ); ?>
				</p>
				<p class="kids-shop-thankyou-card__order">
					<?php
					printf(
						/* translators: %s: order number */
						esc_html__( 'Your order Number #%s', 'kids-shop' ),
						esc_html( $order->get_order_number() )
					);
					?>
				</p>
				<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="kids-shop-thankyou-card__btn">
					<?php esc_html_e( 'View Order', 'kids-shop' ); ?>
					<span class="kids-shop-thankyou-card__btn-arrow" aria-hidden="true">&rarr;</span>
				</a>
			</div>

		<?php endif; ?>

		<div class="kids-shop-thankyou-extras">
			<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
			<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
		</div>

	<?php else : ?>

		<div class="kids-shop-thankyou-card">
			<div class="kids-shop-thankyou-card__icon" aria-hidden="true">
				<svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9.55 16.45L5.1 12l1.41-1.41 3.04 3.04 7.05-7.05L18 7.91l-8.45 8.54z" fill="#fff"/>
				</svg>
			</div>
			<p class="kids-shop-thankyou-card__eyebrow"><?php esc_html_e( 'THANK YOU', 'kids-shop' ); ?></p>
			<h1 class="kids-shop-thankyou-card__headline"><?php esc_html_e( 'YOUR ORDER IS PLACED', 'kids-shop' ); ?></h1>
			<p class="kids-shop-thankyou-card__message">
				<?php
				echo esc_html(
					apply_filters(
						'woocommerce_thankyou_order_received_text',
						__( 'Thank you. Your order has been received.', 'woocommerce' ),
						false
					)
				);
				?>
			</p>
		</div>

	<?php endif; ?>
</div>
