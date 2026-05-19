/**
 * Cart page interactions.
 */
(function ($) {
	'use strict';

	function getCartForm() {
		return $('.kids-shop-cart-form');
	}

	function submitCartForm() {
		var $form = getCartForm();
		if (!$form.length) {
			return;
		}
		var $btn = $form.find('button[name="update_cart"]');
		if ($btn.length) {
			$btn.prop('disabled', false);
			if ($btn[0] && typeof $btn[0].click === 'function') {
				$btn[0].click();
			} else {
				$btn.trigger('click');
			}
			return;
		}
		$form.trigger('submit');
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

	$(document).on('click', '.kids-shop-qty-minus', function (e) {
		e.preventDefault();
		var $input = $(this).siblings('.quantity-input');
		changeQty($input, -1);
	});

	$(document).on('click', '.kids-shop-qty-plus', function (e) {
		e.preventDefault();
		var $input = $(this).siblings('.quantity-input');
		changeQty($input, 1);
	});

	var qtyTimer;
	$(document).on('change', '.kids-shop-cart-form .quantity-input', function () {
		clearTimeout(qtyTimer);
		qtyTimer = setTimeout(submitCartForm, 400);
	});

	$(document).on('change', '.kids-shop-select-all', function () {
		var checked = $(this).is(':checked');
		$('.kids-shop-select-all').prop('checked', checked);
		$('.kids-shop-cart-item-check').prop('checked', checked);
	});

	$(document).on('click', '.kids-shop-summary-toggle', function () {
		var $wrap = $(this).closest('.order-summary-container');
		$wrap.find('.kids-shop-order-details').toggleClass('show-content');
		$(this).find('.arrow').toggleClass('arrow-rotate');
		var expanded = $wrap.find('.kids-shop-order-details').hasClass('show-content');
		$(this).attr('aria-expanded', expanded ? 'true' : 'false');
	});

	$(document).on('keydown', '.kids-shop-summary-toggle', function (e) {
		if (e.key === 'Enter' || e.key === ' ') {
			e.preventDefault();
			$(this).trigger('click');
		}
	});
})(jQuery);
