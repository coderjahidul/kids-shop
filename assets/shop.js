(function ($) {
	'use strict';

	/* ─── Sidebar toggle ──────────────────────────────────────────── */
	$(document).on('click', '.kids-shop-open-sidebar', function () {
		$('#kids-shop-category-sidebar').addClass('is-open');
		$('body').addClass('kids-shop-sidebar-open');
	});

	$(document).on('click', '.kids-shop-sidebar-close', function () {
		$('#kids-shop-category-sidebar').removeClass('is-open');
		$('body').removeClass('kids-shop-sidebar-open');
	});

	/* ─── Toast ───────────────────────────────────────────────────── */
	var cfg = (typeof kidsShop !== 'undefined') ? kidsShop : {};
	var i18n = { addedToCart: 'Product added to cart!', viewCart: 'View Cart', close: 'Close', error: 'Could not add to cart.' };
	if (cfg && cfg.i18n) {
		i18n = $.extend({}, i18n, cfg.i18n);
	}
	var cartUrl = cfg.cartUrl || '';

	var $activeToast = null;
	var toastTimer = null;

	function hideToast() {
		clearTimeout(toastTimer);
		if (!$activeToast) { return; }
		$activeToast.removeClass('ks-toast--in');
		var $t = $activeToast;
		$activeToast = null;
		setTimeout(function () { $t.remove(); }, 350);
	}

	function showToast(success, msg) {
		if ($activeToast) { $activeToast.remove(); }
		clearTimeout(toastTimer);

		var icon = success
			? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>'
			: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';

		var viewCartHtml = (success && cartUrl)
			? '<a href="' + cartUrl + '" class="ks-toast__view">' + (i18n.viewCart || 'View Cart') + '</a>'
			: '';

		$activeToast = $(
			'<div class="ks-toast ' + (success ? 'ks-toast--success' : 'ks-toast--error') + '" role="status">' +
			'<span class="ks-toast__icon">' + icon + '</span>' +
			'<span class="ks-toast__msg">' + msg + '</span>' +
			viewCartHtml +
			'<button type="button" class="ks-toast__close">' + (i18n.close || 'Close') + '</button>' +
			'</div>'
		);

		$('body').append($activeToast);
		requestAnimationFrame(function () {
			requestAnimationFrame(function () { $activeToast && $activeToast.addClass('ks-toast--in'); });
		});
		$activeToast.find('.ks-toast__close').on('click', hideToast);
		toastTimer = setTimeout(hideToast, 5000);
	}

	var spinnerSvg =
		'<svg class="ks-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round">' +
		'<path d="M12 2a10 10 0 0 1 10 10"/>' +
		'</svg>';

	function kidsShopAjaxAddToCart(productId, qty, $btn, origHtml) {
		if (!productId || typeof wc_add_to_cart_params === 'undefined') { return; }
		if ($btn && $btn.hasClass('ks-loading')) { return; }

		if ($btn) {
			$btn.addClass('ks-loading').prop('disabled', true).html(spinnerSvg);
		}

		$.post(
			wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
			{ product_id: productId, quantity: qty },
			function (response) {
				if ($btn) {
					$btn.html(origHtml).removeClass('ks-loading').prop('disabled', false);
				}

				if (!response) { showToast(false, i18n.error); return; }
				if (response.error && response.product_url) { window.location = response.product_url; return; }

				if (response.fragments) {
					if (typeof window.kidsShopApplyCartFragments === 'function') {
						window.kidsShopApplyCartFragments(response.fragments);
					}
					$(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
				}

				showToast(true, i18n.addedToCart);
			}
		).fail(function () {
			if ($btn) {
				$btn.html(origHtml).removeClass('ks-loading').prop('disabled', false);
			}
			showToast(false, i18n.error);
		});
	}

	/* ─── AJAX Add to Cart ────────────────────────────────────────── */
	$(document).on('click', '.kids-shop-add-to-cart', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var productId = $btn.data('product_id');

		if (!productId || typeof wc_add_to_cart_params === 'undefined') { return; }
		if ($btn.hasClass('ks-loading')) { return; }

		var qty = 1;
		var $cart = $btn.closest('.kids-shop-sp-cart');
		if ($cart.length) {
			var $qty = $cart.find('input.qty, input.kids-shop-sp-qty');
			if ($qty.length) {
				qty = parseInt($qty.val(), 10);
				if (isNaN(qty) || qty < 1) { qty = 1; }
			}
		}

		var origHtml = $btn.html();
		kidsShopAjaxAddToCart(productId, qty, $btn, origHtml);
	});

	$(document).on('click', '.kids-shop-single-add-to-cart', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var productId = $btn.data('product_id');
		var $cart = $btn.closest('.kids-shop-sp-cart');
		var qty = 1;
		var $qty = $cart.find('input.qty, input.kids-shop-sp-qty');
		if ($qty.length) {
			qty = parseInt($qty.val(), 10);
			if (isNaN(qty) || qty < 1) { qty = 1; }
		}
		var origHtml = $btn.html();
		kidsShopAjaxAddToCart(productId, qty, $btn, origHtml);
	});

	$(document).on('click', '.kids-shop-sp-qty-minus', function () {
		var $wrap = $(this).closest('.kids-shop-sp-qty-box');
		var $in = $wrap.find('input.qty, input.kids-shop-sp-qty');
		var min = parseInt($in.attr('min'), 10) || 1;
		var v = parseInt($in.val(), 10) || min;
		$in.val(Math.max(min, v - 1)).trigger('change');
	});

	$(document).on('click', '.kids-shop-sp-qty-plus', function () {
		var $wrap = $(this).closest('.kids-shop-sp-qty-box');
		var $in = $wrap.find('input.qty, input.kids-shop-sp-qty');
		var maxAttr = $in.attr('max');
		var max = maxAttr ? parseInt(maxAttr, 10) : 0;
		var v = parseInt($in.val(), 10) || 1;
		var next = v + 1;
		if (max && next > max) { next = max; }
		$in.val(next).trigger('change');
	});

	$(document).on('click', '.kids-shop-sp-tab-head .kids-shop-sp-tab', function () {
		var $btn = $(this);
		var tab = $btn.data('tab');
		var $root = $btn.closest('.kids-shop-sp-tabs');
		$root.find('.kids-shop-sp-tab').removeClass('is-active').attr('aria-selected', 'false');
		$btn.addClass('is-active').attr('aria-selected', 'true');
		$root.find('.kids-shop-sp-panel').removeClass('is-active').prop('hidden', true);
		var id = tab === 'reviews' ? '#kids-shop-tab-reviews' : '#kids-shop-tab-desc';
		var $panel = $root.find(id);
		$panel.addClass('is-active').prop('hidden', false);
	});

	$(document).on('click', '.kids-shop-sp-see-more[data-expand]', function () {
		var $b = $(this);
		var $long = $b.siblings('.kids-shop-sp-desc-long');
		if ($long.length && $long.prop('hidden')) {
			$long.prop('hidden', false);
			$b.text($b.data('less') || 'See less');
		} else if ($long.length) {
			$long.prop('hidden', true);
			$b.text($b.data('more') || 'See More');
		}
	});
})(jQuery);
