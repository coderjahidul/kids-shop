<?php
/**
 * Order details — card layout for view order page.
 *
 * @package Kids_Shop
 * @version 10.1.0
 *
 * @var bool $show_downloads Controls whether the downloads table should be rendered.
 */

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if ( ! $order ) {
	return;
}

$order_items        = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$downloads          = $order->get_downloadable_items();
$actions            = array_filter(
	wc_get_account_orders_actions( $order ),
	function ( $key ) {
		return 'view' !== $key;
	},
	ARRAY_FILTER_USE_KEY
);

$show_customer_details = $order->get_user_id() === get_current_user_id();

if ( $show_downloads ) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}
?>
<section class="kids-shop-view-order-items woocommerce-order-details">
	<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

	<h2 class="kids-shop-view-order__section-title"><?php esc_html_e( 'Order Items', 'kids-shop' ); ?></h2>

	<div class="kids-shop-view-order-items__list">
		<?php
		do_action( 'woocommerce_order_details_before_order_table_items', $order );

		foreach ( $order_items as $item_id => $item ) {
			$product = $item->get_product();
			$thumb   = '';
			if ( $product ) {
				$thumb = $product->get_image( 'thumbnail', array( 'class' => 'kids-shop-view-order-items__thumb-img' ) );
			}
			if ( ! $thumb ) {
				$thumb = '<span class="kids-shop-view-order-items__thumb-placeholder" aria-hidden="true"></span>';
			}

			$is_visible        = $product && $product->is_visible();
			$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );
			$item_name         = apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, $is_visible );
			$qty               = $item->get_quantity();
			$refunded_qty      = $order->get_qty_refunded_for_item( $item_id );

			if ( $refunded_qty ) {
				$qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
			} else {
				$qty_display = esc_html( $qty );
			}
			?>
			<article class="kids-shop-view-order-items__card">
				<div class="kids-shop-view-order-items__thumb"><?php echo wp_kses_post( $thumb ); ?></div>
				<div class="kids-shop-view-order-items__info">
					<h3 class="kids-shop-view-order-items__name">
						<?php
						if ( $product_permalink ) {
							echo wp_kses_post( sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $item_name ) );
						} else {
							echo wp_kses_post( $item_name );
						}
						?>
					</h3>
					<p class="kids-shop-view-order-items__qty">
						<?php
						printf(
							/* translators: %s: quantity */
							esc_html__( 'Qty: %s', 'kids-shop' ),
							wp_kses_post( $qty_display )
						);
						?>
					</p>
					<?php
					do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );
					$item_meta = wc_display_item_meta( $item, array( 'echo' => false ) );
					if ( $item_meta ) {
						echo '<div class="kids-shop-view-order-items__meta">' . wp_kses_post( $item_meta ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
					?>
				</div>
				<div class="kids-shop-view-order-items__price">
					<?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
				</div>
			</article>
			<?php
			if ( $show_purchase_note && $product && $product->get_purchase_note() ) {
				echo '<div class="kids-shop-view-order-items__note">' . wp_kses_post( wpautop( do_shortcode( $product->get_purchase_note() ) ) ) . '</div>';
			}
		}

		do_action( 'woocommerce_order_details_after_order_table_items', $order );
		?>
	</div>

	<div class="kids-shop-view-order-summary">
		<h3 class="kids-shop-view-order-summary__title"><?php esc_html_e( 'Order Summary', 'kids-shop' ); ?></h3>
		<dl class="kids-shop-view-order-summary__rows">
			<?php foreach ( $order->get_order_item_totals() as $key => $total ) : ?>
				<div class="kids-shop-view-order-summary__row kids-shop-view-order-summary__row--<?php echo esc_attr( sanitize_html_class( $key ) ); ?>">
					<dt><?php echo esc_html( $total['label'] ); ?></dt>
					<dd><?php echo wp_kses_post( $total['value'] ); ?></dd>
				</div>
			<?php endforeach; ?>
			<?php if ( $order->get_customer_note() ) : ?>
				<div class="kids-shop-view-order-summary__row kids-shop-view-order-summary__row--note">
					<dt><?php esc_html_e( 'Note:', 'woocommerce' ); ?></dt>
					<dd>
						<?php
						$customer_note = wc_wptexturize_order_note( $order->get_customer_note() );
						echo wp_kses( nl2br( $customer_note ), array( 'br' => array() ) );
						?>
					</dd>
				</div>
			<?php endif; ?>
		</dl>

		<?php if ( ! empty( $actions ) ) : ?>
			<div class="kids-shop-view-order-summary__actions">
				<?php
				$wp_button_class = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';
				foreach ( $actions as $key => $action ) {
					if ( empty( $action['aria-label'] ) ) {
						$action_aria_label = sprintf(
							/* translators: 1: Action name, 2: Order number. */
							__( '%1$s order number %2$s', 'woocommerce' ),
							$action['name'],
							$order->get_order_number()
						);
					} else {
						$action_aria_label = $action['aria-label'];
					}
					echo '<a href="' . esc_url( $action['url'] ) . '" class="kids-shop-myaccount-btn kids-shop-myaccount-btn--outline ' . esc_attr( sanitize_html_class( $key ) ) . ' order-actions-button' . esc_attr( $wp_button_class ) . '" aria-label="' . esc_attr( $action_aria_label ) . '">' . esc_html( $action['name'] ) . '</a>';
					unset( $action_aria_label );
				}
				?>
			</div>
		<?php endif; ?>
	</div>

	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
</section>

<?php
do_action( 'woocommerce_after_order_details', $order );

if ( $show_customer_details ) {
	wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) );
}
