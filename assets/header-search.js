(function () {
	'use strict';

	var config = window.kidsShopSearch || {};
	var shopUrl = config.shopUrl || '/shop/';

	function buildSearchUrl(term) {
		var url;

		try {
			url = new URL(shopUrl, window.location.origin);
		} catch (e) {
			url = new URL(window.location.origin + shopUrl);
		}

		term = (term || '').trim();
		if (term) {
			url.searchParams.set('s', term);
			url.searchParams.set('post_type', 'product');
		}

		return url.toString();
	}

	function goSearch(term) {
		window.location.href = buildSearchUrl(term);
	}

	document.addEventListener(
		'submit',
		function (event) {
			var form = event.target;

			if (!form || !form.classList || !form.classList.contains('kids-shop-search-form')) {
				return;
			}

			var input = form.querySelector('[name="s"]');
			if (!input || !input.value.trim()) {
				event.preventDefault();
			}
		},
		true
	);

	function initMobileSearch() {
		document.querySelectorAll('[data-kids-shop-mobile-search]').forEach(function (el) {
			el.addEventListener('click', function (event) {
				event.preventDefault();
				event.stopPropagation();

				var term = window.prompt(config.prompt || 'Search products');
				if (term === null) {
					return;
				}

				goSearch(term);
			});

			el.addEventListener('keydown', function (event) {
				if (event.key === 'Enter' || event.key === ' ') {
					event.preventDefault();
					el.click();
				}
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initMobileSearch);
	} else {
		initMobileSearch();
	}
})();
