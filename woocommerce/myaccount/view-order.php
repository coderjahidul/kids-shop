<?php
/**
 * View Order — KiddoMart-style order details.
 *
 * @package Kids_Shop
 * @version 10.6.0
 */

defined( 'ABSPATH' ) || exit;

$notes      = $order->get_customer_order_notes();
$status     = $order->get_status();
$status_name = wc_get_order_status_name( $status );
$created    = $order->get_date_created();
$num        = $order->get_order_number();
$padded     = is_numeric( $num ) ? str_pad( (string) $num, 4, '0', STR_PAD_LEFT ) : $num;
$orders_url = wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) );
$datetime   = $created ? wc_format_datetime( $created, get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ) : '';
?>
<div class="kids-shop-view-order">
	<a class="kids-shop-view-order__back" href="<?php echo esc_url( $orders_url ); ?>">
		<span class="kids-shop-view-order__back-icon" aria-hidden="true">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
		</span>
		<?php esc_html_e( 'Back to Orders', 'kids-shop' ); ?>
	</a>

	<header class="kids-shop-view-order__header">
		<div class="kids-shop-view-order__header-main">
			<h1 class="kids-shop-view-order__title"><?php esc_html_e( 'Order Details', 'kids-shop' ); ?></h1>
			<p class="kids-shop-view-order__meta">
				<span class="kids-shop-view-order__meta-id">
					<?php
					printf(
						/* translators: %s: order number */
						esc_html__( 'Order ID: %s', 'kids-shop' ),
						esc_html( $padded )
					);
					?>
				</span>
				<?php if ( $datetime ) : ?>
					<span class="kids-shop-view-order__meta-sep" aria-hidden="true">&middot;</span>
					<time datetime="<?php echo esc_attr( $created->date( 'c' ) ); ?>"><?php echo esc_html( $datetime ); ?></time>
				<?php endif; ?>
				<?php if ( $order->get_payment_method_title() ) : ?>
					<span class="kids-shop-view-order__meta-sep" aria-hidden="true">&middot;</span>
					<span><?php echo esc_html( $order->get_payment_method_title() ); ?></span>
				<?php endif; ?>
			</p>
		</div>
		<span class="kids-shop-view-order__badge kids-shop-view-order__badge--status-<?php echo esc_attr( sanitize_html_class( $status ) ); ?>">
			<span class="kids-shop-view-order__badge-dot" aria-hidden="true"></span>
			<?php echo esc_html( $status_name ); ?>
		</span>
	</header>

	<?php if ( $notes ) : ?>
		<section class="kids-shop-view-order__updates">
			<h2 class="kids-shop-view-order__section-title"><?php esc_html_e( 'Order Updates', 'kids-shop' ); ?></h2>
			<ol class="kids-shop-view-order__timeline">
				<?php foreach ( $notes as $note ) : ?>
					<li class="kids-shop-view-order__timeline-item">
						<time class="kids-shop-view-order__timeline-date" datetime="<?php echo esc_attr( gmdate( 'c', strtotime( $note->comment_date ) ) ); ?>">
							<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), strtotime( $note->comment_date ) ) ); ?>
						</time>
						<div class="kids-shop-view-order__timeline-text">
							<?php echo wp_kses_post( wpautop( wptexturize( $note->comment_content ) ) ); ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<?php do_action( 'woocommerce_view_order', $order_id ); ?>
</div>
