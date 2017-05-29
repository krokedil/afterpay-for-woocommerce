<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Capture AfterPay reservation
 *
 * Check if order was created using AfterPay and if yes, capture AfterPay reservation when WooCommerce order is marked
 * completed.
 *
 * @class WC_AfterPay_Capture
 * @version 1.0.0
 * @package WC_Gateway_AfterPay/Classes
 * @category Class
 * @author Krokedil
 */
class WC_AfterPay_Capture {

	/**
	 * WC_AfterPay_Cancel_Reservation constructor.
	 */
	public function __construct() {
		$afterpay_settings = get_option( 'woocommerce_afterpay_invoice_settings' );

		$this->x_auth_key = $afterpay_settings['x_auth_key'];
		$this->testmode = $afterpay_settings['testmode'];

		add_action( 'woocommerce_order_status_completed', array( $this, 'capture_full' ) );
	}

	public function capture_full( $order_id ) {
		$wc_order = wc_get_order( $order_id );
		$request  = new WC_AfterPay_Request_Capture_Payment( $this->x_auth_key, $this->testmode );
		$response = $request->response( $order_id );
		$response = json_decode( $response );
		if ( $response->captureNumber ) {
			$wc_order->add_order_note( sprintf( __( 'Payment captured with AfterPay with capture number %s', 'woocommerce-gateway-afterpay' ), $response->captureNumber ) );
		} else {
			$wc_order->add_order_note( sprintf( __( 'Payment failed to be captured by AfterPay', 'woocommerce-gateway-afterpay' ) ) );
		}
	}
}
$wc_afterpay_capture = new WC_AfterPay_Capture;
