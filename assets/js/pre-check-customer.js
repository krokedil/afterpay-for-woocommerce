jQuery( function( $ ) {

	// Prevent the form from actually submitting
	$(document).on('submit', '#arvato-pre-check-customer', function (event) {
		event.preventDefault();

		$.ajax(
			WC_Arvato.ajaxurl,
			{
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'arvato_pre_check_customer',
					nonce: WC_Arvato.arvato_pre_check_customer_nonce
				},
				success: function (response) {
					console.log('success');
					console.log(response.data);

				},
				error: function (response) {
					console.log('error');
					console.log(response);
				}
			}
		);
	});

});