(function ($) {
	'use strict';

	// Mobile category sidebar toggle.
	$(document).on('click', '.kids-shop-open-sidebar', function () {
		$('#kids-shop-category-sidebar').addClass('is-open');
		$('body').addClass('kids-shop-sidebar-open');
	});

	$(document).on('click', '.kids-shop-sidebar-close', function () {
		$('#kids-shop-category-sidebar').removeClass('is-open');
		$('body').removeClass('kids-shop-sidebar-open');
	});

	// AJAX add to cart.
	$(document).on('click', '.kids-shop-add-to-cart', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var productId = $btn.data('product_id');
		if (!productId || typeof wc_add_to_cart_params === 'undefined') {
			return;
		}

		$btn.prop('disabled', true);

		$.post(
			wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
			{ product_id: productId, quantity: 1 },
			function (response) {
				if (!response) {
					$btn.prop('disabled', false);
					return;
				}
				if (response.error && response.product_url) {
					window.location = response.product_url;
					return;
				}
				$(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $btn]);
				$btn.prop('disabled', false);
			}
		).fail(function () {
			$btn.prop('disabled', false);
		});
	});
})(jQuery);
