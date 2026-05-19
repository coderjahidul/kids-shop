(function () {
	'use strict';

	/* ─── Hero Slider ─────────────────────────────────────────────── */
	function initHeroSlider() {
		var slider = document.querySelector('.kids-shop-hero-slider');
		if (!slider) { return; }

		var wrapper    = slider.querySelector('.carousel-wrapper');
		var slides     = slider.querySelectorAll('.carousel-slide');
		var indicators = slider.querySelectorAll('.kids-shop-carousel-indicators .indicator');
		var prevBtn    = slider.querySelector('.kids-shop-carousel-prev');
		var nextBtn    = slider.querySelector('.kids-shop-carousel-next');
		var total      = slides.length;
		var current    = 0;
		var timer      = null;

		if (!wrapper || total < 1) { return; }

		function goTo(index) {
			current = ((index % total) + total) % total;
			wrapper.style.transform = 'translate3d(-' + current * 100 + '%, 0, 0)';
			slides.forEach(function (slide, i) {
				slide.classList.toggle('is-active', i === current);
			});
			indicators.forEach(function (dot, i) {
				dot.classList.toggle('active', i === current);
				dot.setAttribute('aria-selected', i === current ? 'true' : 'false');
			});
		}

		function next() { goTo(current + 1); }
		function prev() { goTo(current - 1); }

		function startAutoplay() {
			stopAutoplay();
			if (total > 1) { timer = window.setInterval(next, 5000); }
		}

		function stopAutoplay() {
			if (timer) { window.clearInterval(timer); timer = null; }
		}

		if (prevBtn) { prevBtn.addEventListener('click', function () { prev(); startAutoplay(); }); }
		if (nextBtn) { nextBtn.addEventListener('click', function () { next(); startAutoplay(); }); }

		indicators.forEach(function (dot) {
			dot.addEventListener('click', function () {
				var index = parseInt(dot.getAttribute('data-index'), 10);
				if (!isNaN(index)) { goTo(index); startAutoplay(); }
			});
		});

		slider.addEventListener('mouseenter', stopAutoplay);
		slider.addEventListener('mouseleave', startAutoplay);
		goTo(0);
		startAutoplay();
	}

	/* ─── Toast Notification ──────────────────────────────────────── */
	function KidsShopToast() {
		var cfg     = (typeof kidsShopHome !== 'undefined') ? kidsShopHome : {};
		var i18n    = cfg.i18n || { addedToCart: 'Product added to cart!', viewCart: 'View Cart', close: 'Close', error: 'Could not add to cart.' };
		var cartUrl = cfg.cartUrl || '';
		var $active = null;
		var timer   = null;

		function hide() {
			clearTimeout(timer);
			if (!$active) { return; }
			$active.removeClass('ks-toast--in');
			var $t = $active;
			$active = null;
			setTimeout(function () { $t.remove(); }, 350);
		}

		function show(success, msg) {
			if ($active) { $active.remove(); }
			clearTimeout(timer);

			var icon = success
				? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>'
				: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';

			var viewCart = (success && cartUrl)
				? '<a href="' + cartUrl + '" class="ks-toast__view">' + i18n.viewCart + '</a>'
				: '';

			$active = jQuery(
				'<div class="ks-toast ' + (success ? 'ks-toast--success' : 'ks-toast--error') + '" role="status">' +
					'<span class="ks-toast__icon">' + icon + '</span>' +
					'<span class="ks-toast__msg">' + msg + '</span>' +
					viewCart +
					'<button type="button" class="ks-toast__close">' + i18n.close + '</button>' +
				'</div>'
			);

			jQuery('body').append($active);
			requestAnimationFrame(function () {
				requestAnimationFrame(function () { $active && $active.addClass('ks-toast--in'); });
			});

			$active.find('.ks-toast__close').on('click', hide);
			timer = setTimeout(hide, 5000);
		}

		return { show: show, hide: hide };
	}

	/* ─── AJAX Add to Cart ────────────────────────────────────────── */
	function initAddToCart() {
		if (typeof jQuery === 'undefined') { return; }

		var toast = KidsShopToast();
		var cfg   = (typeof kidsShopHome !== 'undefined') ? kidsShopHome : {};
		var i18n  = cfg.i18n || { addedToCart: 'Product added to cart!', error: 'Could not add to cart.' };

		var spinnerSvg =
			'<svg class="ks-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round">' +
				'<path d="M12 2a10 10 0 0 1 10 10"/>' +
			'</svg>';

		jQuery(document).on('click', '.kids-shop-add-to-cart', function (e) {
			e.preventDefault();
			var $btn      = jQuery(this);
			var productId = $btn.data('product_id');

			if (!productId || typeof wc_add_to_cart_params === 'undefined') { return; }
			if ($btn.hasClass('ks-loading')) { return; }

			var origHtml = $btn.html();
			$btn.addClass('ks-loading').prop('disabled', true).html(spinnerSvg);

			jQuery.post(
				wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
				{ product_id: productId, quantity: 1 },
				function (response) {
					// Restore button FIRST — before anything else.
					$btn.html(origHtml).removeClass('ks-loading').prop('disabled', false);

					if (!response) { toast.show(false, i18n.error); return; }
					if (response.error && response.product_url) { window.location = response.product_url; return; }

					// Trigger added_to_cart WITHOUT passing $btn so WooCommerce
					// updates the cart count fragment but does NOT rewrite our button.
					if (response.fragments) {
						jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
					}

					toast.show(true, i18n.addedToCart);
				}
			).fail(function () {
				$btn.html(origHtml).removeClass('ks-loading').prop('disabled', false);
				toast.show(false, i18n.error);
			});
		});
	}

	/* ─── Boot ────────────────────────────────────────────────────── */
	function onReady() {
		initHeroSlider();
		initAddToCart();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', onReady);
	} else {
		onReady();
	}
})();
