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
	var cfg    = (typeof kidsShop !== 'undefined') ? kidsShop : {};
	var i18n   = { addedToCart: 'Product added to cart!', viewCart: 'View Cart', close: 'Close', error: 'Could not add to cart.' };
	var cartUrl = cfg.cartUrl || '';

	var $activeToast = null;
	var toastTimer   = null;

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
			? '<a href="' + cartUrl + '" class="ks-toast__view">View Cart</a>'
			: '';

		$activeToast = $(
			'<div class="ks-toast ' + (success ? 'ks-toast--success' : 'ks-toast--error') + '" role="status">' +
				'<span class="ks-toast__icon">' + icon + '</span>' +
				'<span class="ks-toast__msg">' + msg + '</span>' +
				viewCartHtml +
				'<button type="button" class="ks-toast__close">Close</button>' +
			'</div>'
		);

		$('body').append($activeToast);
		requestAnimationFrame(function () {
			requestAnimationFrame(function () { $activeToast && $activeToast.addClass('ks-toast--in'); });
		});
		$activeToast.find('.ks-toast__close').on('click', hideToast);
		toastTimer = setTimeout(hideToast, 5000);
	}

	/* ─── AJAX Add to Cart ────────────────────────────────────────── */
	var spinnerSvg =
		'<svg class="ks-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round">' +
			'<path d="M12 2a10 10 0 0 1 10 10"/>' +
		'</svg>';

	$(document).on('click', '.kids-shop-add-to-cart', function (e) {
		e.preventDefault();
		var $btn      = $(this);
		var productId = $btn.data('product_id');

		if (!productId || typeof wc_add_to_cart_params === 'undefined') { return; }
		if ($btn.hasClass('ks-loading')) { return; }

		var origHtml = $btn.html();
		$btn.addClass('ks-loading').prop('disabled', true).html(spinnerSvg);

		$.post(
			wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
			{ product_id: productId, quantity: 1 },
			function (response) {
				// Restore button immediately.
				$btn.html(origHtml).removeClass('ks-loading').prop('disabled', false);

				if (!response) { showToast(false, i18n.error); return; }
				if (response.error && response.product_url) { window.location = response.product_url; return; }

				// Fire added_to_cart WITHOUT $btn reference so WooCommerce updates
				// cart count fragments but does NOT rewrite our custom button HTML.
				if (response.fragments) {
					$(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
				}

				showToast(true, i18n.addedToCart);
			}
		).fail(function () {
			$btn.html(origHtml).removeClass('ks-loading').prop('disabled', false);
			showToast(false, i18n.error);
		});
	});
})(jQuery);
