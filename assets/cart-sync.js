(function ($) {
	'use strict';

	/**
	 * Apply WooCommerce cart fragments to header / floating cart UI.
	 *
	 * @param {Object.<string, string>} fragments Selector => HTML map.
	 */
	window.kidsShopApplyCartFragments = function (fragments) {
		if (!fragments) {
			return;
		}

		$.each(fragments, function (selector, html) {
			$(selector).replaceWith(html);
		});
	};
})(jQuery);
