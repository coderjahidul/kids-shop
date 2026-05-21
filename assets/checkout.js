/**
 * Checkout order panel interactions.
 */
(function ($) {
	'use strict';

	function refreshCheckout() {
		$(document.body).trigger('update_checkout');
	}

	function changeQty($input, delta) {
		var min = parseInt($input.attr('min'), 10) || 1;
		var max = parseInt($input.attr('max'), 10) || 0;
		var val = parseInt($input.val(), 10) || min;
		val += delta;
		if (val < min) {
			val = min;
		}
		if (max > 0 && val > max) {
			val = max;
		}
		$input.val(val).trigger('change');
	}

	function updateCartItem($input) {
		if (typeof kidsShopCheckout === 'undefined') {
			return;
		}

		$.post(
			kidsShopCheckout.ajaxUrl,
			{
				action: 'kids_shop_update_checkout_cart_item',
				security: kidsShopCheckout.nonce,
				cart_item_key: $input.data('cart-item-key'),
				quantity: parseInt($input.val(), 10) || 1
			}
		).always(refreshCheckout);
	}

	function removeCartItem(cartItemKey) {
		if (typeof kidsShopCheckout === 'undefined' || !cartItemKey) {
			return;
		}

		var removeUrl = kidsShopCheckout.wcAjaxUrl.replace('%%endpoint%%', 'remove_from_cart');
		$.post(removeUrl, { cart_item_key: cartItemKey }).always(refreshCheckout);
	}

	$(document).on('click', '.kids-shop-checkout-order-panel .kids-shop-qty-minus', function (e) {
		e.preventDefault();
		changeQty($(this).siblings('.quantity-input'), -1);
	});

	$(document).on('click', '.kids-shop-checkout-order-panel .kids-shop-qty-plus', function (e) {
		e.preventDefault();
		changeQty($(this).siblings('.quantity-input'), 1);
	});

	var qtyTimer;
	$(document).on('change', '.kids-shop-checkout-order-panel .kids-shop-checkout-qty-input', function () {
		var $input = $(this);
		clearTimeout(qtyTimer);
		qtyTimer = setTimeout(function () {
			updateCartItem($input);
		}, 400);
	});

	$(document).on('click', '.kids-shop-checkout-remove-item', function (e) {
		e.preventDefault();
		var cartItemKey = $(this).closest('.kids-shop-checkout-item').data('cart-item-key');
		removeCartItem(cartItemKey);
	});

	$(document).on('click', '.kids-shop-checkout-coupon-toggle', function (e) {
		e.preventDefault();
		var $couponForm = $('#woocommerce-checkout-form-coupon');
		var expanded = $couponForm.is(':visible');
		$couponForm.slideToggle(180);
		$(this).attr('aria-expanded', expanded ? 'false' : 'true');
	});

	// Dynamic Address Book Interactions
	var divisions = {
		'dhaka': 'Dhaka',
		'chattogram': 'Chattogram',
		'rajshahi': 'Rajshahi',
		'khulna': 'Khulna',
		'barishal': 'Barishal',
		'sylhet': 'Sylhet',
		'rangpur': 'Rangpur',
		'mymensingh': 'Mymensingh'
	};

	function selectAddress(addr) {
		if (!addr) return;

		// Fill inputs
		$('#billing_first_name').val(addr.first_name).trigger('change');
		$('#billing_phone').val(addr.phone).trigger('change');
		$('#billing_state').val(addr.state).trigger('change');
		$('#billing_address_1').val(addr.address_1).trigger('change');

		// Update preview card
		var divisionLabel = divisions[addr.state] || addr.state;
		$('.kids-shop-address-preview-name').text(addr.first_name);
		$('.kids-shop-address-preview-phone').text(addr.phone);
		$('.kids-shop-address-preview-address').text(addr.address_1 + ', ' + divisionLabel);

		// Toggle views
		$('.kids-shop-checkout-billing').hide();
		$('.kids-shop-address-form-actions').hide();
		$('.kids-shop-address-preview-card').fadeIn(200);
	}

	// Restore active selection from localStorage after save/reload
	var savedActiveId = localStorage.getItem('kids_shop_active_address_id');
	if (savedActiveId) {
		var $targetSwitch = $('.kids-shop-address-switch[data-address-id="' + savedActiveId + '"]');
		if ($targetSwitch.length) {
			$('.kids-shop-address-switch').removeClass('kids-shop-address-switch--active');
			$targetSwitch.addClass('kids-shop-address-switch--active');
			$('#kids_shop_selected_address_id').val(savedActiveId);
			localStorage.removeItem('kids_shop_active_address_id');
		}
	}

	// Init default active address
	var $activeSwitch = $('.kids-shop-address-switch--active:not(.kids-shop-address-switch--add)');
	if ($activeSwitch.length) {
		var initialAddr = $activeSwitch.data('address');
		if (initialAddr) {
			selectAddress(initialAddr);
		}
	}

	// Address switch click handler
	$(document).on('click', '.kids-shop-address-switch:not(.kids-shop-address-switch--add)', function (e) {
		e.preventDefault();
		var $this = $(this);
		var addrId = $this.data('address-id');
		var addr = $this.data('address');

		$('.kids-shop-address-switch').removeClass('kids-shop-address-switch--active');
		$this.addClass('kids-shop-address-switch--active');
		$('#kids_shop_selected_address_id').val(addrId);

		selectAddress(addr);
	});

	// "+ Add New Address" click handler
	$(document).on('click', '.kids-shop-address-switch--add', function (e) {
		e.preventDefault();
		var $this = $(this);

		$('.kids-shop-address-switch').removeClass('kids-shop-address-switch--active');
		$this.addClass('kids-shop-address-switch--active');

		$('.kids-shop-address-preview-card').hide();

		// Clear fields
		$('#billing_first_name').val('').trigger('change');
		$('#billing_phone').val('').trigger('change');
		$('#billing_state').val('').trigger('change');
		$('#billing_address_1').val('').trigger('change');

		$('#kids_shop_address_label').val('');

		$('.kids-shop-checkout-billing').fadeIn(200);
		$('.kids-shop-address-form-actions').fadeIn(200);
	});

	// "Cancel Address" click handler
	$(document).on('click', '.kids-shop-cancel-address-btn', function (e) {
		e.preventDefault();

		var $homeSwitch = $('.kids-shop-address-switch[data-address-id="home"]');
		if (!$homeSwitch.length) {
			$homeSwitch = $('.kids-shop-address-switch:not(.kids-shop-address-switch--add)').first();
		}

		if ($homeSwitch.length) {
			$homeSwitch.trigger('click');
		} else {
			$('.kids-shop-address-form-actions').hide();
		}
	});

	// "Edit Address" click handler
	$(document).on('click', '.kids-shop-address-edit-btn', function (e) {
		e.preventDefault();

		var $activeSwitch = $('.kids-shop-address-switch--active:not(.kids-shop-address-switch--add)');
		if ($activeSwitch.length) {
			var addr = $activeSwitch.data('address');
			if (addr) {
				$('#kids_shop_address_label').val(addr.label);
				$('.kids-shop-address-preview-card').hide();
				$('.kids-shop-checkout-billing').fadeIn(200);
				$('.kids-shop-address-form-actions').fadeIn(200);
			}
		}
	});

	// "Save Address" click handler
	$(document).on('click', '.kids-shop-save-address-btn', function (e) {
		e.preventDefault();

		var label = $('#kids_shop_address_label').val().trim();
		var firstName = $('#billing_first_name').val().trim();
		var phone = $('#billing_phone').val().trim();
		var state = $('#billing_state').val();
		var address1 = $('#billing_address_1').val().trim();

		if (!label || !firstName || !phone || !state || !address1) {
			alert('Please fill out all address fields and provide a label.');
			return;
		}

		var $btn = $(this);
		$btn.prop('disabled', true).text('Saving...');

		$.post(
			kidsShopCheckout.ajaxUrl,
			{
				action: 'kids_shop_save_address',
				security: kidsShopCheckout.nonce,
				label: label,
				first_name: firstName,
				phone: phone,
				state: state,
				address_1: address1
			},
			function (response) {
				$btn.prop('disabled', false).text('Save Address');
				if (response.success) {
					localStorage.setItem('kids_shop_active_address_id', response.data.address.id);
					window.location.reload();
				} else {
					alert(response.data.message || 'Error saving address.');
				}
			}
		);
	});
})(jQuery);
