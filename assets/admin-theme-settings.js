(function ($) {
	'use strict';

	function initColorPickers($context) {
		$context.find('.kids-shop-color-picker').each(function () {
			var $el = $(this);
			if (!$el.hasClass('wp-color-picker')) {
				$el.wpColorPicker();
			}
		});
	}

	function reindexHomeSections() {
		var $list = $('#kids-shop-home-sections-list');
		if (!$list.length) {
			return;
		}

		$list.find('.kids-shop-home-section-item').each(function (index) {
			var $item = $(this);
			$item.attr('data-index', index);
			$item.find('.kids-shop-section-heading').first().text(
				(kidsShopAdmin.sectionLabel || 'Section') + ' ' + (index + 1)
			);

			$item.find('[name]').each(function () {
				var name = $(this).attr('name');
				if (!name) {
					return;
				}
				name = name.replace(/\[home_sections\]\[[^\]]+\]/, '[home_sections][' + index + ']');
				$(this).attr('name', name);
			});
		});
	}

	function toggleCategoryRow($item) {
		var type = $item.find('.kids-shop-section-type').val();
		$item.find('.kids-shop-section-category-row').toggle(type === 'category');
	}

	function bindSectionEvents($item) {
		$item.find('.kids-shop-section-type').off('change.kidsShop').on('change.kidsShop', function () {
			toggleCategoryRow($item);
		});

		$item.find('.kids-shop-remove-section-btn').off('click.kidsShop').on('click.kidsShop', function (e) {
			e.preventDefault();
			var $list = $('#kids-shop-home-sections-list');
			if ($list.find('.kids-shop-home-section-item').length <= 1) {
				return;
			}
			$item.remove();
			reindexHomeSections();
		});

		toggleCategoryRow($item);
	}

	function addHomeSection() {
		var $list = $('#kids-shop-home-sections-list');
		var $template = $('#kids-shop-home-section-template');

		if (!$list.length || !$template.length) {
			return;
		}

		var count = $list.find('.kids-shop-home-section-item').length;
		var max = parseInt(kidsShopAdmin.maxSections, 10) || 12;

		if (count >= max) {
			window.alert(kidsShopAdmin.maxSectionsAlert);
			return;
		}

		var html = $template.html();
		if (!html || !html.trim()) {
			return;
		}

		html = html.replace(/__INDEX__/g, String(count));
		$list.append(html);

		var $item = $list.find('.kids-shop-home-section-item').last();
		bindSectionEvents($item);
		reindexHomeSections();
	}

	$(function () {
		initColorPickers($(document));

		var frame;

		$(document).on('click', '.kids-shop-upload-btn', function (e) {
			e.preventDefault();
			var $field = $(this).closest('.kids-shop-media-field');
			var $input = $field.find('.kids-shop-media-id');
			var $preview = $field.find('.kids-shop-media-preview');

			if (frame) {
				frame.open();
				frame.off('select');
			} else {
				frame = wp.media({
					title: kidsShopAdmin.chooseImage,
					button: { text: kidsShopAdmin.useImage },
					multiple: false
				});
			}

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				$input.val(attachment.id);
				$preview.html('<img src="' + attachment.url + '" alt="" />');
			});

			frame.open();
		});

		$(document).on('click', '.kids-shop-remove-media-btn', function (e) {
			e.preventDefault();
			var $field = $(this).closest('.kids-shop-media-field');
			$field.find('.kids-shop-media-id').val('');
			$field.find('.kids-shop-media-preview').empty();
		});

		$('#kids-shop-home-sections-list .kids-shop-home-section-item').each(function () {
			bindSectionEvents($(this));
		});

		$(document).on('click', '#kids-shop-add-section-btn', function (e) {
			e.preventDefault();
			addHomeSection();
		});
	});
})(jQuery);
