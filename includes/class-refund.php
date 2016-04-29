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

	/**
	 * Grab AfterPay reservation ID.
	 *
	 * @return string
	 */
	public function get_reservation_id() {
		return get_post_meta( $this->order_id, '_afterpay_reservation_id', true );
	}

	/**
	 * Get payment method settings.
	 *
	 * @return array
	 */
	public function get_payment_method_settings() {
		$order                = wc_get_order( $this->order_id );
		$order_payment_method = $order->payment_method;

		$payment_method_settings = get_option( 'woocommerce_' . $order_payment_method . '_settings' );
		return $payment_method_settings;
	}

	/**
	 * Check if order was created using one of AfterPay's payment options.
	 *
	 * @return boolean
	 */
	public function check_if_afterpay_order() {
		$order                = wc_get_order( $this->order_id );
		$order_payment_method = $order->payment_method;

		if ( strpos( $order_payment_method, 'afterpay' ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 * Process refund.
	 *
	 * @param $order_id
	 * @return boolean
	 */
	public function refund_invoice( $order_id, $amount = null, $reason = '' ) {
		$this->order_id = $order_id;
		$order = wc_get_order( $this->order_id );

		// If this order wasn't created using an AfterPay payment method, bail.
		if ( ! $this->check_if_afterpay_order() ) {
			return;
		}

		// Get settings for payment method used to create this order.
		$payment_method_settings = $this->get_payment_method_settings();

		$order_maintenance_endpoint = 'yes' == $payment_method_settings['testmode'] ? ARVATO_ORDER_MAINTENANCE_TEST :
			ARVATO_ORDER_MAINTENANCE_LIVE;

		// Check if logging is enabled
		$this->log_enabled = $payment_method_settings['debug'];

		$refund_args = array(
			'User'       => array(
				'ClientID' => $payment_method_settings['client_id'],
				'Username' => $payment_method_settings['username'],
				'Password' => $payment_method_settings['password']
			),
			'ReservationID' => $this->get_reservation_id(),
			'InvoiceNumber' => $order->get_transaction_id(),
		);

		$soap_client = new SoapClient( $order_maintenance_endpoint );

		if ( $amount != $order->get_total() ) {
			$refund_args['OrderDetails']['Amount'] = $amount;
			$refund_args['OrderDetails']['OrderNo'] = $order_id;
			$refund_args['OrderDetails']['CurrencyCode'] = $order->get_order_currency();
			$refund_args['OrderDetails']['OrderChannelType'] = 'Internet';
			$refund_args['OrderDetails']['OrderDeliveryType'] = 'Normal';

			$response = $soap_client->RefundPartial( $refund_args );
		} else {
			$refund_args['OrderNo'] = $order_id;
			$response = $soap_client->RefundFull( $refund_args );
		}
		
		if ( $response->IsSuccess ) {
			// Add time stamp, used to prevent duplicate cancellations for the same order.
			update_post_meta( $this->order_id, '_afterpay_invoice_refunded', current_time( 'mysql' ) );
			$order->add_order_note(	__( 'AfterPay refund was successfully processed.', 'woocommerce-gateway-afterpay' ) );

			return $response;
		} else {
			$order->add_order_note( __(
				'AfterPay refund could not be processed.',
				'woocommerce-gateway-afterpay'
			) );

			return new WP_Error( 'afterpay-refund', __( 'Refund failed.', 'woocommerce-gateway-afterpay' ) );
		}
	}

}
$wc_afterpay_refund = new WC_AfterPay_Refund;