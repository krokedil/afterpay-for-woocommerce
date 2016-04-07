<?php

class WC_Arvato_Cancel_Reservation {

	public function __construct() {
		$this->client_id = 7852;
		$this->password = 'm8K1Dfuj';
		$this->username = 'WooComTestSE';
		$this->endpoint_checkout = 'https://sandboxapi.horizonafs.com/eCommerceServices/eCommerce/Checkout/v2/CheckoutServices.svc?wsdl';

		add_action( 'woocommerce_order_status_cancelled', array( $this, 'cancel_reservation' ) );
	}

	public function cancel_reservation( $order_id ) {
		$cancel_reservation_args = array(
			'User' => array(
				'ClientID' => $this->client_id,
				'Password' => $this->password,
				'Username' => $this->username
			),
			'CustomerNo' => get_post_meta( $order_id, '_arvato_customer_no', true ),
			'OrderNo' => $order_id
		);
		$soap_client = new SoapClient( $this->endpoint_checkout );
		$response = $soap_client->CancelReservation( $cancel_reservation_args );
	}

}
$wc_arvato_cancel_reservation = new WC_Arvato_Cancel_Reservation;