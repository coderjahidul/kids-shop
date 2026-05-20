<?php
/**
 * My Account orders — card layout with status filters (KiddoMart-style).
 *
 * @package Kids_Shop
 * @version 9.5.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders );

$filter_tabs = function_exists( 'kids_shop_get_account_order_filter_tabs' ) ? kids_shop_get_account_order_filter_tabs() : array( 'all' => __( 'All', 'kids-shop' ) );
?>
<div class="kids-shop-orders">
	<?php if ( $has_orders ) : ?>
		<div class="kids-shop-orders__filters" role="tablist" aria-label="<?php esc_attr_e( 'Filter orders by status', 'kids-shop' ); ?>">
			<?php foreach ( $filter_tabs as $slug => $label ) : ?>
				<button
					type="button"
					class="kids-shop-orders__filter<?php echo 'all' === $slug ? ' is-active' : ''; ?>"
					data-order-filter="<?php echo esc_attr( $slug ); ?>"
					role="tab"
					aria-selected="<?php echo 'all' === $slug ? 'true' : 'false'; ?>"
				>
					<?php echo esc_html( $label ); ?>
				</button>
			<?php endforeach; ?>
		</div>

		<div class="kids-shop-orders__list">
			<?php
			foreach ( $customer_orders->orders as $customer_order ) {
				$order = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				if ( ! $order ) {
					continue;
				}

				$status      = $order->get_status();
				$status_name = wc_get_order_status_name( $status );
				$view_url    = $order->get_view_order_url();
				$created     = $order->get_date_created();

				$items = $order->get_items( 'line_item' );
				$first = null;
				foreach ( $items as $item ) {
					$first = $item;
					break;
				}

				$thumb_html = '';
				$line_name  = '';
				$line_price = '';

				if ( $first && is_a( $first, 'WC_Order_Item_Product' ) ) {
					$product = $first->get_product();
					$line_name = $first->get_name();
					if ( $product ) {
						$thumb_html = $product->get_image( 'thumbnail', array( 'class' => 'kids-shop-orders__thumb-img' ) );
					}
					$line_price = wc_price( $first->get_total(), array( 'currency' => $order->get_currency() ) );
				}

				if ( ! $thumb_html ) {
					$thumb_html = '<span class="kids-shop-orders__thumb-placeholder" aria-hidden="true"></span>';
				}

				$num         = $order->get_order_number();
				$padded      = is_numeric( $num ) ? str_pad( (string) $num, 4, '0', STR_PAD_LEFT ) : $num;
				$order_label = sprintf(
					/* translators: %s: order number */
					__( 'Order ID: %s', 'kids-shop' ),
					$padded
				);

				$datetime = $created ? wc_format_datetime( $created, get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ) : '';
				?>
				<a
					class="kids-shop-orders__card kids-shop-orders__card--status-<?php echo esc_attr( sanitize_html_class( $status ) ); ?>"
					href="<?php echo esc_url( $view_url ); ?>"
					data-order-status="<?php echo esc_attr( $status ); ?>"
				>
					<div class="kids-shop-orders__card-top">
						<span class="kids-shop-orders__badge">
							<span class="kids-shop-orders__badge-dot" aria-hidden="true"></span>
							<?php echo esc_html( $status_name ); ?>
						</span>
						<?php if ( $datetime ) : ?>
							<time class="kids-shop-orders__date" datetime="<?php echo esc_attr( $created->date( 'c' ) ); ?>"><?php echo esc_html( $datetime ); ?></time>
						<?php endif; ?>
					</div>
					<div class="kids-shop-orders__card-body">
						<div class="kids-shop-orders__thumb"><?php echo wp_kses_post( $thumb_html ); ?></div>
						<div class="kids-shop-orders__info">
							<span class="kids-shop-orders__id"><?php echo esc_html( $order_label ); ?></span>
							<?php if ( $line_name ) : ?>
								<span class="kids-shop-orders__product"><?php echo esc_html( $line_name ); ?></span>
							<?php endif; ?>
							<?php if ( $line_price ) : ?>
								<span class="kids-shop-orders__price"><?php echo wp_kses_post( $line_price ); ?></span>
							<?php else : ?>
								<span class="kids-shop-orders__price"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
							<?php endif; ?>
						</div>
						<span class="kids-shop-orders__chevron" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
						</span>
					</div>
				</a>
				<?php
			}
			?>
		</div>

		<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

		<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
			<div class="kids-shop-orders__pagination woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
				<?php if ( 1 !== $current_page ) : ?>
					<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
				<?php endif; ?>

				<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
					<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	<?php else : ?>

		<div class="kids-shop-orders__empty">
			<?php
			if ( function_exists( 'kids_shop_account_empty_illustration' ) ) {
				echo kids_shop_account_empty_illustration(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline SVG markup.
			}
			?>
			<p class="kids-shop-orders__empty-title"><?php esc_html_e( 'Your Order List is Empty!', 'kids-shop' ); ?></p>
			<p class="kids-shop-orders__empty-text"><?php esc_html_e( 'Browse the shop to place your first order.', 'kids-shop' ); ?></p>
			<a class="kids-shop-myaccount-btn kids-shop-myaccount-btn--solid" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
				<?php esc_html_e( 'Browse products', 'kids-shop' ); ?>
			</a>
		</div>

	<?php endif; ?>
</div>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
