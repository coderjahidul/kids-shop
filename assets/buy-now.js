(function ($) {
	'use strict';

	var cfg = (typeof kidsShopBuyNow !== 'undefined') ? kidsShopBuyNow : {};
	var checkoutUrl = cfg.checkoutUrl || '';

	function getProductId($el) {
		var id = $el.data('product_id');
		if (id) {
			return id;
		}
		var $card = $el.closest('[data-product-id]');
		if ($card.length) {
			return $card.data('product-id');
		}
		return 0;
	}

	function getQuantity($el) {
		var $cart = $el.closest('.kids-shop-sp-cart');
		if ($cart.length) {
			var $qty = $cart.find('input.qty, input.kids-shop-sp-qty');
			if ($qty.length) {
				var q = parseInt($qty.val(), 10);
				return (isNaN(q) || q < 1) ? 1 : q;
			}
		}
		return 1;
	}

	$(document).on('click', '.kids-shop-buy-now', function (e) {
		e.preventDefault();

		var $link = $(this);
		var href = $link.attr('href') || checkoutUrl;
		var productId = getProductId($link);

		if (!productId || typeof wc_add_to_cart_params === 'undefined') {
			if (href) {
				window.location = href;
			}
			return;
		}

		if ($link.hasClass('ks-loading')) {
			return;
		}

		$link.addClass('ks-loading');
		var qty = getQuantity($link);

		$.post(
			wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
			{ product_id: productId, quantity: qty },
			function (response) {
				$link.removeClass('ks-loading');

				if (!response) {
					if (href) {
						window.location = href;
					}
					return;
				}

				if (response.error && response.product_url) {
					window.location = response.product_url;
					return;
				}

				if (response.fragments) {
					if (typeof window.kidsShopApplyCartFragments === 'function') {
						window.kidsShopApplyCartFragments(response.fragments);
					}
					$(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
				}

				window.location = checkoutUrl || href;
			}
		).fail(function () {
			$link.removeClass('ks-loading');
			if (href) {
				window.location = href;
			}
		});
	});
})(jQuery);
