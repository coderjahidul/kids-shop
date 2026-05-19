(function () {
	'use strict';

	function initHeroSlider() {
		var slider = document.querySelector('.kids-shop-hero-slider');
		if (!slider) {
			return;
		}

		var wrapper = slider.querySelector('.carousel-wrapper');
		var slides = slider.querySelectorAll('.carousel-slide');
		var indicators = slider.querySelectorAll('.kids-shop-carousel-indicators .indicator');
		var prevBtn = slider.querySelector('.kids-shop-carousel-prev');
		var nextBtn = slider.querySelector('.kids-shop-carousel-next');
		var total = slides.length;
		var current = 0;
		var timer = null;

		if (!wrapper || total < 1) {
			return;
		}

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

		function next() {
			goTo(current + 1);
		}

		function prev() {
			goTo(current - 1);
		}

		function startAutoplay() {
			stopAutoplay();
			if (total > 1) {
				timer = window.setInterval(next, 5000);
			}
		}

		function stopAutoplay() {
			if (timer) {
				window.clearInterval(timer);
				timer = null;
			}
		}

		if (prevBtn) {
			prevBtn.addEventListener('click', function () {
				prev();
				startAutoplay();
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function () {
				next();
				startAutoplay();
			});
		}

		indicators.forEach(function (dot) {
			dot.addEventListener('click', function () {
				var index = parseInt(dot.getAttribute('data-index'), 10);
				if (!isNaN(index)) {
					goTo(index);
					startAutoplay();
				}
			});
		});

		slider.addEventListener('mouseenter', stopAutoplay);
		slider.addEventListener('mouseleave', startAutoplay);

		goTo(0);
		startAutoplay();
	}

	function initAddToCart() {
		if (typeof jQuery === 'undefined') {
			return;
		}
		jQuery(document).on('click', '.kids-shop-add-to-cart', function (e) {
			e.preventDefault();
			var $btn = jQuery(this);
			var productId = $btn.data('product_id');
			if (!productId || typeof wc_add_to_cart_params === 'undefined') {
				return;
			}
			$btn.prop('disabled', true);
			jQuery.post(
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
					jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $btn]);
					$btn.prop('disabled', false);
				}
			).fail(function () {
				$btn.prop('disabled', false);
			});
		});
	}

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
