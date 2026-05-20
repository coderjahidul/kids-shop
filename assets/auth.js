/**
 * Login / Sign up page interactions.
 */
(function () {
	'use strict';

	document.querySelectorAll('.kids-shop-auth-toggle-pw').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var id = btn.getAttribute('data-target');
			var input = id ? document.getElementById(id) : null;
			if (!input) {
				return;
			}
			var show = input.type === 'password';
			input.type = show ? 'text' : 'password';
			btn.classList.toggle('is-visible', show);
			btn.setAttribute(
				'aria-label',
				show ? 'Hide password' : 'Show password'
			);
		});
	});
})();
