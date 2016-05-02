jQuery( function( $ ) {

	function mask_form_field(field) {
		if (field != null) {
			var field_split = field.split(' ');
			var field_masked = new Array();

			$.each(field_split, function(i, val) {
				if (isNaN(val)) {
					field_masked.push(val.charAt(0) + Array(val.length).join('*'));
				} else {
					field_masked.push('**' + field.substr(field.length - 3));
				}
			});

			return field_masked.join(' ');
		}
	}

	function maybe_show_pre_checkout_form() {
		var selected_payment_method = $('input[name="payment_method"]:checked').val();
		if (selected_payment_method.indexOf('afterpay') >= 0) {
			$('#afterpay-pre-check-customer').slideDown(250);
			$('#afterpay-pre-check-customer-number').focus();
		} else {
			$('#afterpay-pre-check-customer').slideUp(250);
		}
	}

	$(document).on('init_checkout', function(event) {
		maybe_show_pre_checkout_form();
	});

	$(document).on('change', 'input[name="payment_method"]', function(event) {
		maybe_show_pre_checkout_form();

		var selected = $('input[name="payment_method"]:checked').val();
		if (selected.indexOf('afterpay') < 0) {
			$('#afterpay-pre-check-customer-response').remove();
		}
	});

	// Prevent the form from actually submitting
	$(document).on('click', '.afterpay-get-address-button', function (event) {
		event.preventDefault();

		// Remove success note, in case it's already there
		$('#afterpay-pre-check-customer-response').remove();

		var selected_payment_method = $('input[name="payment_method"]:checked').val();
		var selected_customer_category = $('input[name="afterpay_customer_category"]:checked').val();
		var entered_personal_number = $(this).parent().parent().find('.afterpay-pre-check-customer-number').val();
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
						nonce: WC_AfterPay.afterpay_pre_check_customer_nonce
					},
					success: function (response) {
						if (response.success) { // wp_send_json_success
							console.log(response.data);

							$('body').trigger('update_checkout');

							customer_data = response.data.response.Customer;

							$('#billing_first_name').val(mask_form_field(customer_data.FirstName)).prop('disabled', true);
							$('#billing_last_name').val(mask_form_field(customer_data.LastName)).prop('disabled', true);
							$('#billing_address_1').val(mask_form_field(customer_data.AddressList.Address.Street)).prop('disabled', true);
							$('#billing_address_2').val(mask_form_field(customer_data.AddressList.Address.StreetNumber)).prop('disabled', true);
							$('#billing_postcode').val(mask_form_field(customer_data.AddressList.Address.PostalCode)).prop('disabled', true);
							$('#billing_city').val(mask_form_field(customer_data.AddressList.Address.PostalPlace)).prop('disabled', true);

							$('#shipping_first_name').val(mask_form_field(customer_data.FirstName)).prop('disabled', true);
							$('#shipping_last_name').val(mask_form_field(customer_data.LastName)).prop('disabled', true);
							$('#shipping_address_1').val(mask_form_field(customer_data.AddressList.Address.Street)).prop('disabled', true);
							$('#shipping_address_2').val(mask_form_field(customer_data.AddressList.Address.StreetNumber)).prop('disabled', true);
							$('#shipping_postcode').val(mask_form_field(customer_data.AddressList.Address.PostalCode)).prop('disabled', true);
							$('#shipping_city').val(mask_form_field(customer_data.AddressList.Address.PostalPlace)).prop('disabled', true);

							$('#afterpay-pre-check-customer').append('<div id="afterpay-pre-check-customer-response" class="woocommerce-message">' + response.data.message + '</div>');
						} else { // wp_send_json_error
							console.log('ERROR:');
							console.log(response.data);
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
	});

});