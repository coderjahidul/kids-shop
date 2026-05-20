/**
 * Checkout order panel interactions.
 */
(function ($) {
	'use strict';

	function refreshCheckout() {
		$(document.body).trigger('update_checkout');
	}

	function changeQty($input, delta) {
		var min = parseInt($input.attr('min'), 10) || 1;
		var max = parseInt($input.attr('max'), 10) || 0;
		var val = parseInt($input.val(), 10) || min;
		val += delta;
		if (val < min) {
			val = min;
		}
		if (max > 0 && val > max) {
			val = max;
		}
		$input.val(val).trigger('change');
	}

	function updateCartItem($input) {
		if (typeof kidsShopCheckout === 'undefined') {
			return;
		}

		$.post(
			kidsShopCheckout.ajaxUrl,
			{
				action: 'kids_shop_update_checkout_cart_item',
				security: kidsShopCheckout.nonce,
				cart_item_key: $input.data('cart-item-key'),
				quantity: parseInt($input.val(), 10) || 1
			}
		).always(refreshCheckout);
	}

	function removeCartItem(cartItemKey) {
		if (typeof kidsShopCheckout === 'undefined' || !cartItemKey) {
			return;
		}

		var removeUrl = kidsShopCheckout.wcAjaxUrl.replace('%%endpoint%%', 'remove_from_cart');
		$.post(removeUrl, { cart_item_key: cartItemKey }).always(refreshCheckout);
	}

	$(document).on('click', '.kids-shop-checkout-order-panel .kids-shop-qty-minus', function (e) {
		e.preventDefault();
		changeQty($(this).siblings('.quantity-input'), -1);
	});

	$(document).on('click', '.kids-shop-checkout-order-panel .kids-shop-qty-plus', function (e) {
		e.preventDefault();
		changeQty($(this).siblings('.quantity-input'), 1);
	});

	var qtyTimer;
	$(document).on('change', '.kids-shop-checkout-order-panel .kids-shop-checkout-qty-input', function () {
		var $input = $(this);
		clearTimeout(qtyTimer);
		qtyTimer = setTimeout(function () {
			updateCartItem($input);
		}, 400);
	});

	$(document).on('click', '.kids-shop-checkout-remove-item', function (e) {
		e.preventDefault();
		var cartItemKey = $(this).closest('.kids-shop-checkout-item').data('cart-item-key');
		removeCartItem(cartItemKey);
	});

	$(document).on('click', '.kids-shop-checkout-coupon-toggle', function (e) {
		e.preventDefault();
		var $couponForm = $('#woocommerce-checkout-form-coupon');
		var expanded = $couponForm.is(':visible');
		$couponForm.slideToggle(180);
		$(this).attr('aria-expanded', expanded ? 'false' : 'true');
	});
})(jQuery);
