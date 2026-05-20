(function () {
	'use strict';

	function initOrderFilters() {
		var root = document.querySelector('.kids-shop-orders__filters');
		var list = document.querySelector('.kids-shop-orders__list');
		if (!root || !list) {
			return;
		}

		var buttons = root.querySelectorAll('[data-order-filter]');
		var cards = list.querySelectorAll('[data-order-status]');

		function statusMatchesFilter(status, filter) {
			if (filter === 'all') {
				return true;
			}
			if (filter === 'cancelled') {
				return status === 'cancelled' || status === 'refunded' || status === 'failed';
			}
			return status === filter;
		}

		function setActive(filter) {
			buttons.forEach(function (btn) {
				var slug = btn.getAttribute('data-order-filter');
				var on = slug === filter;
				btn.classList.toggle('is-active', on);
				btn.setAttribute('aria-selected', on ? 'true' : 'false');
			});

			cards.forEach(function (card) {
				var st = card.getAttribute('data-order-status') || '';
				card.hidden = !statusMatchesFilter(st, filter);
			});
		}

		root.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-order-filter]');
			if (!btn || !root.contains(btn)) {
				return;
			}
			e.preventDefault();
			setActive(btn.getAttribute('data-order-filter') || 'all');
		});
	}

	function initPasswordToggles() {
		document.querySelectorAll('.kids-shop-password-toggle').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var id = btn.getAttribute('data-target');
				var input = id ? document.getElementById(id) : null;
				if (!input) {
					return;
				}
				var show = input.getAttribute('type') === 'password';
				input.setAttribute('type', show ? 'text' : 'password');
				btn.setAttribute('aria-pressed', show ? 'true' : 'false');
				btn.setAttribute(
					'aria-label',
					show
						? btn.getAttribute('data-label-hide') || 'Hide password'
						: btn.getAttribute('data-label-show') || 'Show password'
				);
			});
		});
	}

	function initDeactivateNotice() {
		var btn = document.getElementById('kids-shop-deactivate-account');
		if (!btn) {
			return;
		}
		btn.addEventListener('click', function () {
			var ta = document.getElementById('kids_shop_account_inactive_reason');
			var reason = ta && ta.value ? ta.value.trim() : '';
			var msg =
				reason.length > 0
					? 'Thanks for your feedback. Please contact the store to complete account closure.'
					: 'To deactivate your account, please contact the store with your request.';
			window.alert(msg);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			initOrderFilters();
			initPasswordToggles();
			initDeactivateNotice();
		});
	} else {
		initOrderFilters();
		initPasswordToggles();
		initDeactivateNotice();
	}
})();
