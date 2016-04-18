jQuery( function( $ ) {

	// Prevent the form from actually submitting
	$(document).on('submit', '#arvato-pre-check-customer', function (event) {
		event.preventDefault();

		var entered_personal_number = $('#arvato-pre-check-customer-pn').val();
		var selected_payment_method = $('input[name="payment_method"]:checked').val();

		if ('' != entered_personal_number) { // Check if the field is empty
			$.ajax(
				WC_Arvato.ajaxurl,
				{
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'arvato_pre_check_customer',
						personal_number: entered_personal_number,
						payment_method: selected_payment_method,
						nonce: WC_Arvato.arvato_pre_check_customer_nonce
					},
					success: function (response) {
						if (response.success) { // wp_send_json_success
							console.log('SUCCESS');
							console.log(response.data);

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