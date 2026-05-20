<?php
/**
 * Account sidebar navigation.
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = kids_shop_get_account_nav_items();
?>
<nav class="kids-shop-myaccount-nav">
	<ul class="kids-shop-myaccount-nav__list">
		<?php foreach ( $items as $item ) : ?>
			<?php
			$active = kids_shop_is_account_nav_active( $item );
			$class  = 'kids-shop-myaccount-nav__item';
			if ( $active ) {
				$class .= ' is-active';
			}
			?>
			<li class="<?php echo esc_attr( $class ); ?>">
				<a class="kids-shop-myaccount-nav__link" href="<?php echo esc_url( $item['url'] ); ?>">
					<span class="kids-shop-myaccount-nav__icon"><?php echo kids_shop_account_nav_icon( $item['icon'] ); ?></span>
					<span class="kids-shop-myaccount-nav__label"><?php echo esc_html( $item['label'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
