jQuery(function ($) {

	var customer_info_fetched = false;
	var customer_first_name   = '';
	var customer_last_name    = '';
	var customer_address_1    = '';
	var customer_address_2    = '';
	var customer_postcode     = '';
	var customer_city         = '';

	function mask_form_field(field) {
		if (field != null) {
			var field_split = field.split(' ');
			var field_masked = new Array();

			$.each(field_split, function (i, val) {
				if (isNaN(val)) {
					field_masked.push(val.charAt(0) + Array(val.length).join('*'));
				} else {
					field_masked.push('**' + val.substr(val.length - 3));
				}
			});

			return field_masked.join(' ');
		}
	}

	function maybe_show_pre_checkout_form(do_focus) {
		//console.log(do_focus);
		var selected_payment_method = $('input[name="payment_method"]:checked').val();
		if (selected_payment_method.indexOf('afterpay') >= 0) {
			// Do not allow separate shipping address for AfterPay
			$( 'div.shipping_address' ).hide();
			$( '#ship-to-different-address' ).hide();
			// Show pno
			$('#afterpay-pre-check-customer').slideDown(250);
			if ('yes' == do_focus) {
				$('#afterpay-pre-check-customer-number').focus();
			}
		} else {
			// Hide pno
			$('#afterpay-pre-check-customer').slideUp(250);
			// Show ship to different address checkbox
			$( '#ship-to-different-address' ).show();
		}
		
		// Only display the Get Address button if Sweden is the selected country
		var selected_customer_country = $("#billing_country").val();
		if (selected_customer_country == 'SE') {
			jQuery('.afterpay-get-address-button').fadeIn();
		} else {
			jQuery('.afterpay-get-address-button').fadeOut();
		}
	}

	function populate_afterpay_fields() {
		var selected_customer_category = $('input[name="afterpay_customer_category"]:checked').val();

		if ( 'Person' == selected_customer_category ) {
			$('#billing_first_name').val(mask_form_field(customer_first_name)).prop('readonly', true);
			$('#billing_last_name').val(mask_form_field(customer_last_name)).prop('readonly', true);
			$('#billing_company').val('').prop('readonly', false);
		} else {
			$('#billing_first_name').val('').prop('readonly', false);
			$('#billing_last_name').val('').prop('readonly', false);
			$('#billing_company').val(mask_form_field(customer_last_name)).prop('readonly', true);
		}
		$('#billing_address_1').val(mask_form_field(customer_address_1)).prop('readonly', true);
		$('#billing_address_2').val(mask_form_field(customer_address_2)).prop('readonly', true);
		$('#billing_postcode').val(mask_form_field(customer_postcode)).prop('readonly', true);
		$('#billing_city').val(mask_form_field(customer_city)).prop('readonly', true);

		if ( 'Person' == selected_customer_category ) {
			$('#shipping_first_name').val(mask_form_field(customer_first_name)).prop('readonly', true);
			$('#shipping_last_name').val(mask_form_field(customer_last_name)).prop('readonly', true);
			$('#shipping_company').val('').prop('readonly', false);
		} else {
			$('#shipping_first_name').val('').prop('readonly', false);
			$('#shipping_last_name').val('').prop('readonly', false);
			$('#shipping_company').val(mask_form_field(customer_last_name)).prop('readonly', true);
		}
		$('#shipping_address_1').val(mask_form_field(customer_address_1)).prop('readonly', true);
		$('#shipping_address_2').val(mask_form_field(customer_address_2)).prop('readonly', true);
		$('#shipping_postcode').val(mask_form_field(customer_postcode)).prop('readonly', true);
		$('#shipping_city').val(mask_form_field(customer_city)).prop('readonly', true);
	}

	function wipe_afterpay_fields() {
		$('#billing_first_name').val('').prop('readonly', false);
		$('#billing_last_name').val('').prop('readonly', false);
		$('#billing_company').val('').prop('readonly', false);
		$('#billing_address_1').val('').prop('readonly', false);
		$('#billing_address_2').val('').prop('readonly', false);
		$('#billing_postcode').val('').prop('readonly', false);
		$('#billing_city').val('').prop('readonly', false);

		$('#shipping_first_name').val('').prop('readonly', false);
		$('#shipping_last_name').val('').prop('readonly', false);
		$('#shipping_company').val('').prop('readonly', false);
		$('#shipping_address_1').val('').prop('readonly', false);
		$('#shipping_address_2').val('').prop('readonly', false);
		$('#shipping_postcode').val('').prop('readonly', false);
		$('#shipping_city').val('').prop('readonly', false);
	}

	$(document).on('init_checkout', function (event) {
		var do_focus = 'yes';
		maybe_show_pre_checkout_form(do_focus);
	});
	$(document).on('updated_checkout', function (event) {
		var do_focus = 'no';
		maybe_show_pre_checkout_form(do_focus);
	});
	
	$(document).on('change', 'input[name="payment_method"]', function (event) {
		var do_focus = 'yes';
		//maybe_show_pre_checkout_form(do_focus);
		
		$('body').trigger('update_checkout');

		var selected = $('input[name="payment_method"]:checked').val();
		if (selected.indexOf('afterpay') < 0) {
			$('#afterpay-pre-check-customer-response').remove();
			wipe_afterpay_fields();
		} else {
			// If switching to AfterPay and customer info is fetched, use that to populate the fields
			if (customer_info_fetched) {
				populate_afterpay_fields();
			}
		}
	});

	// Fire PreCheckCustomer when the button is clicked
	$(document).on('click', '.afterpay-get-address-button', function (event) {
		// Prevent the form from actually submitting
		event.preventDefault();

		trigger_ajax_pre_check_customer();
	});

	// Fire PreCheckCustomer on update_checkout
	// $(document).on('update_checkout', function(event) {
		// trigger_ajax_pre_check_customer();
	// });

	function trigger_ajax_pre_check_customer() {
		// Remove success note, in case it's already there
		$('#afterpay-pre-check-customer-response').remove();

		var selected_payment_method = $('input[name="payment_method"]:checked').val();
		var selected_customer_category = $('input[name="afterpay_customer_category"]:checked').val();
		var entered_personal_number = $('#afterpay-pre-check-customer .afterpay-pre-check-customer-number').val();
		var selected_billing_country = $("#billing_country").val();
		$('.afterpay-pre-check-customer-number').val(entered_personal_number);

		if ('' != entered_personal_number) { // Check if the field is empty
			$.ajax(
				WC_AfterPay.ajaxurl,
				{
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'afterpay_pre_check_customer',
						personal_number: entered_personal_number,
						payment_method: selected_payment_method,
						customer_category: selected_customer_category,
						billing_country: selected_billing_country,
						nonce: WC_AfterPay.afterpay_pre_check_customer_nonce
					},
					success: function (response) {
						if (response.success) { // wp_send_json_success
							console.log(response.data);

							$('body').trigger('update_checkout');

							customer_data = response.data.response.Customer;

							customer_info_fetched = true;
							customer_first_name   = customer_data.FirstName;
							customer_last_name    = customer_data.LastName;
							customer_address_1    = customer_data.AddressList.Address.Street;
							customer_address_2    = customer_data.AddressList.Address.StreetNumber;
							customer_postcode     = customer_data.AddressList.Address.PostalCode;
							customer_city         = customer_data.AddressList.Address.PostalPlace;

							populate_afterpay_fields();

							$('#afterpay-pre-check-customer').append('<div id="afterpay-pre-check-customer-response" class="woocommerce-message">' + response.data.message + '</div>');
						} else { // wp_send_json_error
							console.log('ERROR:');
							console.log(response.data);

							$('body').trigger('update_checkout');

							$('#afterpay-pre-check-customer').append('<div id="afterpay-pre-check-customer-response" class="woocommerce-error">' + response.data.message + '</div>');
						}
					},
					error: function (response) {
						console.log('AJAX error');
						console.log(response);
					}
				}
			);
		} else { // If the field is empty show notification

		}
	}

});