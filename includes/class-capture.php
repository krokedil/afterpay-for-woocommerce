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
	 * Mandatory fields
	 * Member name
	 * - CustomerNo (custom field _afterpay_customer_no)
	 * - OrderNo (available in woocommerce_order_status_cancelled hook)
	 *
	 * User (pulled using get_option)
	 * - ClientID
	 * - Password
	 * - Username
	 */

	/** @var int */
	private $order_id = '';

	/**
	 * WC_AfterPay_Cancel_Reservation constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_completed', array( $this, 'capture_full' ) );
	}

	/**
	 * Grab AfterPay customer number.
	 *
	 * @return string
	 */
	public function get_customer_no() {
		return get_post_meta( $this->order_id, '_afterpay_customer_no', true );
	}

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
	 * Process reservation cancellation.
	 *
	 * @param $order_id
	 */
	public function capture_full( $order_id ) {
		$this->order_id = $order_id;
		$order = wc_get_order( $this->order_id );

		// If this order wasn't created using an AfterPay payment method, bail.
		if ( ! $this->check_if_afterpay_order() ) {
			return;
		}

		// If this reservation was already cancelled, do nothing.
		if ( get_post_meta( $this->order_id, '_afterpay_reservation_captured', true ) ) {
			return;
		}

		// Get settings for payment method used to create this order.
		$payment_method_settings = $this->get_payment_method_settings();

		// If payment method is set to not capture orders automatically, bail.
		if ( ! $payment_method_settings['order_management'] ) {
			return;
		}

		$order_maintenance_endpoint = 'yes' == $payment_method_settings['testmode'] ? ARVATO_ORDER_MAINTENANCE_TEST :
			ARVATO_ORDER_MAINTENANCE_LIVE;

		$payment_method_id = $order->payment_method;
		switch ( $payment_method_id ) {
			case 'afterpay_invoice':
				$payment_method = 'Invoice';
				break;
			case 'afterpay_account':
				$payment_method = 'Account';
				break;
			case 'afterpay_part_payment':
				$payment_method = 'Installment';
				break;
		}

		// Prepare order lines for AfterPay
		$order_lines_processor = new WC_AfterPay_Process_Order_Lines();
		$order_lines = $order_lines_processor->get_order_lines( $order_id );

		// Check if logging is enabled
		$this->log_enabled = $payment_method_settings['debug'];

		$args = array(
			'User'       => array(
				'ClientID' => $payment_method_settings['client_id'],
				'Username' => $payment_method_settings['username'],
				'Password' => $payment_method_settings['password']
			),
			'ReservationID'    => $this->get_reservation_id(),
			'PaymentInfo'      => array(
				'PaymentMethod' => $payment_method
			),
			'ContractDate'     => date( 'Y-m-d', strtotime( $order->order_date ) ),
			'OrderDetails'     => array(
				'Amount'            => $order->get_total(),
				'TotalOrderValue'   => $order->get_total(),
				'CurrencyCode'      => $order->get_order_currency(),
				'OrderChannelType'  => 'Internet',
				'OrderDeliveryType' => 'Normal',
				'OrderLines'        => $order_lines,
				'OrderNo'           => $this->order_id,
			),
		);

		$soap_client = new SoapClient( $order_maintenance_endpoint );
		$response    = $soap_client->CaptureFull( $args );

		if ( $response->IsSuccess ) {
			error_log( 'CAPTURE RESPONSE: ' . var_export( $response, true ) );

			// Add time stamp, used to prevent duplicate cancellations for the same order.
			update_post_meta( $this->order_id, '_afterpay_reservation_captured', current_time( 'mysql' ) );
			update_post_meta( $this->order_id, '_transaction_id', $response->InvoiceNumber );

			$order->add_order_note(
				sprintf( __( 'AfterPay reservation was successfully captured, invoice number: %s.', 'woocommerce-gateway-afterpay' ), $response->InvoiceNumber )
			);

		} else {
			$order->add_order_note( __(
				'AfterPay reservation could not be captured.',
				'woocommerce-gateway-afterpay'
			) );
		}
	}

}
$wc_afterpay_capture = new WC_AfterPay_Capture;