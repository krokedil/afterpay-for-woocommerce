<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Refund AfterPay invoice
 *
 * Check if refund is possible, then process it. Currently only supports RefundFull.
 *
 * @class WC_AfterPay_Refund
 * @version 1.0.0
 * @package WC_Gateway_AfterPay/Classes
 * @category Class
 * @author Krokedil
 */
class WC_AfterPay_Refund {

	/** @var int */
	private $order_id = '';

	/** @var bool */
	private $testmode = false;

	/** @var string */
	private $x_auth_key = '';

	/**
	 * WC_AfterPay_Refund constructor.
	 */
	public function __construct() {
		$afterpay_settings = get_option( 'woocommerce_afterpay_invoice_settings' );
		$this->testmode = 'yes' == $afterpay_settings['testmode'] ? true : false;
	}

	/**
	 * Process refund.
	 *
	 * @param $order_id
	 * @return boolean
	 */
	public function refund_invoice( $order_id, $amount = null, $reason = '' ) {
		$order = wc_get_order( $order_id );
		$payment_method = $order->payment_method;
		$payment_method_settings 	= get_option( 'woocommerce_' . $payment_method . '_settings' );
		$this->x_auth_key = $payment_method_settings['x_auth_key'];
		$this->testmode = $payment_method_settings['testmode'];

		error_log( 'refunding full order' );
		$order_number = $order->get_order_number();
		$request      = new WC_AfterPay_Request_Refund_Payment( $this->x_auth_key, $this->testmode );
		$request->response( $order_number );
	}

}
$wc_afterpay_refund = new WC_AfterPay_Refund;
