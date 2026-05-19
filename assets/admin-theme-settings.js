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

	function getSlideIndex($item) {
		var idx = $item.attr('data-index');
		if (idx === undefined || idx === '' || idx === '__INDEX__') {
			return $item.index();
		}
		return parseInt(idx, 10);
	}

	function reindexRepeaterItems($list, itemSelector, headingSelector, label, namePattern) {
		$list.find(itemSelector).each(function (index) {
			var $item = $(this);
			$item.attr('data-index', index);
			$item.find(headingSelector).first().text((label || 'Item') + ' ' + (index + 1));

			$item.find('input[name], select[name], textarea[name]').each(function () {
				var name = $(this).attr('name');
				if (!name || $(this).hasClass('kids-shop-hero-image-flat')) {
					return;
				}
				name = name.replace(namePattern, '[' + index + ']');
				$(this).attr('name', name);
			});
		});
	}

	function reindexHomeSections() {
		var $list = $('#kids-shop-home-sections-list');
		if (!$list.length) {
			return;
		}
		reindexRepeaterItems(
			$list,
			'.kids-shop-home-section-item',
			'.kids-shop-section-heading',
			kidsShopAdmin.sectionLabel || 'Section',
			/\[home_sections\]\[[^\]]+\]/
		);
	}

	function reindexHeroSlides() {
		var $list = $('#kids-shop-hero-slides-list');
		if (!$list.length) {
			return;
		}
		reindexRepeaterItems(
			$list,
			'.kids-shop-hero-slide-item',
			'.kids-shop-slide-heading',
			kidsShopAdmin.slideLabel || 'Slide',
			/\[hero_slides\]\[[^\]]+\]/
		);

		$list.find('.kids-shop-hero-slide-item').each(function (index) {
			var $item = $(this);
			$item.attr('data-index', index);
			$item.find('.kids-shop-media-field').attr('data-slide-index', index);
			$item.find('.kids-shop-hero-image-flat').attr('name', 'kids_shop_hero_image_ids[' + index + ']');
		});
	}

	function getAttachmentPreviewUrl(attachment) {
		if (attachment.sizes) {
			if (attachment.sizes.medium) {
				return attachment.sizes.medium.url;
			}
			if (attachment.sizes.thumbnail) {
				return attachment.sizes.thumbnail.url;
			}
		}
		return attachment.url;
	}

	function setMediaFieldValues($field, attachment) {
		var id = attachment.id;
		var previewUrl = getAttachmentPreviewUrl(attachment);
		var fullUrl = attachment.url || previewUrl;

		$field.find('.kids-shop-media-id').val(id);
		$field.find('.kids-shop-hero-image-flat').val(id);
		$field.find('.kids-shop-media-url').val(fullUrl);
		$field.find('.kids-shop-media-preview').html(
			'<img src="' + previewUrl + '" alt="" style="max-width:220px;height:auto;display:block;" />'
		);
		$field.closest('td').find('.kids-shop-slide-missing-image').remove();
	}

	function ajaxSaveHeroImage($field, imageId) {
		var $item = $field.closest('.kids-shop-hero-slide-item');
		if (!$item.length) {
			return;
		}

		var slideIndex = getSlideIndex($item);
		var $status = $field.find('.kids-shop-media-status');

		$status.text('');

		$.post(kidsShopAdmin.ajaxUrl, {
			action: 'kids_shop_save_hero_slide_image',
			nonce: kidsShopAdmin.nonce,
			slide_index: slideIndex,
			image_id: imageId
		})
			.done(function (response) {
				if (response && response.success) {
					$status.text(kidsShopAdmin.imageSaved || 'Image saved.');
					if (response.data && response.data.preview) {
						$field.find('.kids-shop-media-preview').html(
							'<img src="' +
								response.data.preview +
								'" alt="" style="max-width:220px;height:auto;display:block;" />'
						);
					}
				} else {
					$status.text(kidsShopAdmin.imageSaveError || 'Could not save image.');
				}
			})
			.fail(function () {
				$status.text(kidsShopAdmin.imageSaveError || 'Could not save image.');
			});
	}

	function openMediaLibrary($field) {
		if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
			window.alert(
				kidsShopAdmin.mediaError ||
					'WordPress media library did not load. Please refresh the page and try again.'
			);
			return;
		}

		var frame = wp.media({
			title: kidsShopAdmin.chooseImage,
			button: { text: kidsShopAdmin.useImage },
			library: { type: 'image' },
			multiple: false
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first();
			if (!attachment) {
				return;
			}
			var data = attachment.toJSON();
			if (!data.id) {
				return;
			}
			setMediaFieldValues($field, data);
			ajaxSaveHeroImage($field, data.id);
		});

		frame.open();
	}

	function bindMediaField($field) {
		$field.find('.kids-shop-upload-btn').off('click.kidsShopMedia').on('click.kidsShopMedia', function (e) {
			e.preventDefault();
			openMediaLibrary($field);
		});

		$field.find('.kids-shop-remove-media-btn').off('click.kidsShopMedia').on('click.kidsShopMedia', function (e) {
			e.preventDefault();
			$field.find('.kids-shop-media-id, .kids-shop-hero-image-flat').val('');
			$field.find('.kids-shop-media-url').val('');
			$field.find('.kids-shop-media-preview').empty();
			$field.find('.kids-shop-media-status').text('');
		});
	}

	function initMediaFields($context) {
		$context.find('.kids-shop-media-field').each(function () {
			bindMediaField($(this));
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
			$item.remove();
			reindexHomeSections();
		});

		toggleCategoryRow($item);
	}

	function bindHeroSlideEvents($item) {
		$item.find('.kids-shop-remove-slide-btn').off('click.kidsShop').on('click.kidsShop', function (e) {
			e.preventDefault();
			var $list = $('#kids-shop-hero-slides-list');
			if (!$list.find('.kids-shop-hero-slide-item').length) {
				return;
			}
			$item.remove();
			reindexHeroSlides();
		});
		initMediaFields($item);
	}

	function appendFromTemplate($list, $template, max, alertMessage, itemSelector, bindFn, reindexFn) {
		if (!$list.length || !$template.length) {
			return;
		}

		var count = $list.find(itemSelector).length;
		if (count >= max) {
			window.alert(alertMessage);
			return;
		}

		var html = $template.html();
		if (!html || !html.trim()) {
			return;
		}

		html = html.replace(/__INDEX__/g, String(count));
		$list.append(html);

		var $item = $list.find(itemSelector).last();
		if (typeof bindFn === 'function') {
			bindFn($item);
		}
		if (typeof reindexFn === 'function') {
			reindexFn();
		}
	}

	function collectHomeSectionsForSave() {
		var sections = [];

		$('#kids-shop-home-sections-list .kids-shop-home-section-item').each(function () {
			var $item = $(this);
			var title = $.trim($item.find('.kids-shop-section-title').val() || '');

			if (!title) {
				title = (kidsShopAdmin.sectionLabel || 'Section') + ' ' + (sections.length + 1);
				$item.find('.kids-shop-section-title').val(title);
			}

			sections.push({
				title: title,
				type: $item.find('.kids-shop-section-type').val() || 'category',
				category: $.trim($item.find('.kids-shop-section-category').val() || ''),
				limit: parseInt($item.find('.kids-shop-section-limit').val(), 10) || 5,
				view_all_text: $.trim($item.find('.kids-shop-section-view-all-text').val() || '') || 'View All',
				view_all_url: $.trim($item.find('.kids-shop-section-view-all-url').val() || '')
			});
		});

		return sections;
	}

	function syncHomeSectionsJsonField() {
		var $json = $('#kids-shop-home-sections-json');
		if (!$json.length) {
			return;
		}
		$json.val(JSON.stringify(collectHomeSectionsForSave()));
	}

	function addHomeSection() {
		appendFromTemplate(
			$('#kids-shop-home-sections-list'),
			$('#kids-shop-home-section-template'),
			parseInt(kidsShopAdmin.maxSections, 10) || 12,
			kidsShopAdmin.maxSectionsAlert,
			'.kids-shop-home-section-item',
			bindSectionEvents,
			reindexHomeSections
		);
	}

	function addHeroSlide() {
		appendFromTemplate(
			$('#kids-shop-hero-slides-list'),
			$('#kids-shop-hero-slide-template'),
			parseInt(kidsShopAdmin.maxSlides, 10) || 12,
			kidsShopAdmin.maxSlidesAlert,
			'.kids-shop-hero-slide-item',
			bindHeroSlideEvents,
			reindexHeroSlides
		);
	}

	$(function () {
		initColorPickers($(document));
		initMediaFields($(document));

		$('#kids-shop-home-sections-list .kids-shop-home-section-item').each(function () {
			bindSectionEvents($(this));
		});

		$('#kids-shop-hero-slides-list .kids-shop-hero-slide-item').each(function () {
			bindHeroSlideEvents($(this));
		});

		reindexHeroSlides();

		$(document).on('click', '#kids-shop-add-section-btn', function (e) {
			e.preventDefault();
			addHomeSection();
		});

		$(document).on('click', '#kids-shop-add-slide-btn', function (e) {
			e.preventDefault();
			addHeroSlide();
		});

		$('.kids-shop-settings-form').on('submit', function () {
			reindexHomeSections();
			reindexHeroSlides();
			syncHomeSectionsJsonField();
			$('#kids-shop-hero-slide-template, #kids-shop-home-section-template').find(':input').prop('disabled', true);
		});
	});
})(jQuery);
