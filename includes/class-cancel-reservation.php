<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Cancel AfterPay reservation
 *
 * Check if order was created using AfterPay and if yes, cancel AfterPay reservation when WooCommerce order is marked
 * cancelled.
 *
 * @class WC_AfterPay_Cancel_Reservation
 * @version 1.0.0
 * @package WC_Gateway_AfterPay/Classes
 * @category Class
 * @author Krokedil
 */
class WC_AfterPay_Cancel_Reservation {

	public $x_auth_key = '';

	public $testmode = '';
	/**
	 * WC_AfterPay_Cancel_Reservation constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'cancel_reservation' ) );
	}

	/**
	 * Process reservation cancellation.
	 *
	 * @param $order_id
	 */
	public function cancel_reservation( $order_id ) {
		$order = wc_get_order( $order_id );

		$payment_method = $order->get_payment_method();
		$payment_method_settings 	= get_option( 'woocommerce_' . $payment_method . '_settings' );
		$this->x_auth_key = $payment_method_settings['x_auth_key'];
		$this->testmode = $payment_method_settings['testmode'];

		$order_number = $order->get_order_number();
		$request  = new WC_AfterPay_Request_Cancel_Payment( $this->x_auth_key, $this->testmode );
		$request->response( $order_number );
	}
}
$wc_afterpay_cancel_reservation = new WC_AfterPay_Cancel_Reservation;
