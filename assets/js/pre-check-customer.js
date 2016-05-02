jQuery( function( $ ) {

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
							console.log('SUCCESS');
							console.log(response.data);

							$('body').trigger('update_checkout');

							customer_data = response.data.response.Customer;

							$('#billing_first_name').val(customer_data.FirstName);
							$('#billing_last_name').val(customer_data.LastName);
							$('#billing_address_1').val(customer_data.AddressList.Address.Street);
							$('#billing_address_2').val(customer_data.AddressList.Address.StreetNumber);
							$('#billing_postcode').val(customer_data.AddressList.Address.PostalCode);
							$('#billing_city').val(customer_data.AddressList.Address.PostalPlace);

							$('#shipping_first_name').val(customer_data.FirstName);
							$('#shipping_last_name').val(customer_data.LastName);
							$('#shipping_address_1').val(customer_data.AddressList.Address.Street);
							$('#shipping_address_2').val(customer_data.AddressList.Address.StreetNumber);
							$('#shipping_postcode').val(customer_data.AddressList.Address.PostalCode);
							$('#shipping_city').val(customer_data.AddressList.Address.PostalPlace);

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