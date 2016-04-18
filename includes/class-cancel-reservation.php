<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Cancel Arvato reservation
 *
 * Check if order was created using Arvato and if yes, cancel Arvato reservation when WooCommerce order is marked
 * cancelled.
 *
 * @class WC_Arvato_Cancel_Reservation
 * @version 1.0.0
 * @package WC_Gateway_Arvato/Classes
 * @category Class
 * @author Krokedil
 */
class WC_Arvato_Cancel_Reservation {

	/** @var int */
	private $order_id = '';

	/**
	 * WC_Arvato_Cancel_Reservation constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'cancel_reservation' ) );
	}

	/**
	 * Check if order was created using one of Arvato's payment options.
	 *
	 * @return boolean
	 */
	public function get_customer_no() {
		return get_post_meta( $this->order_id, '_arvato_customer_no', true );
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
	 * Check if order was created using one of Arvato's payment options.
	 *
	 * @return boolean
	 */
	public function check_if_arvato_order() {
		$order                = wc_get_order( $this->order_id );
		$order_payment_method = $order->payment_method;

		if ( strpos( $order_payment_method, 'arvato' ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 * Process reservation cancellation.
	 *
	 * @param $order_id
	 */
	public function cancel_reservation( $order_id ) {
		$this->order_id = $order_id;
		$order = wc_get_order( $this->order_id );

		// If this order wasn't created using an Arvato payment method, bail.
		if ( ! $this->check_if_arvato_order() ) {
			return;
		}

		// If this reservation was already cancelled, do nothing.
		if ( get_post_meta( $this->order_id, '_arvato_reservation_cancelled', true ) ) {
			return;
		}

		// Get settings for payment method used to create this order.
		$payment_method_settings = $this->get_payment_method_settings();
		$checkout_endpoint = 'yes' == $payment_method_settings['testmode'] ? ARVATO_CHECKOUT_TEST :
			ARVATO_CHECKOUT_LIVE;

		// Check if logging is enabled
		$this->log_enabled = $payment_method_settings['debug'];

		$cancel_reservation_args = array(
			'User'       => array(
				'ClientID' => $payment_method_settings['client_id'],
				'Username' => $payment_method_settings['username'],
				'Password' => $payment_method_settings['password']
			),
			'CustomerNo' => $this->get_customer_no(),
			'OrderNo'    => $this->order_id
		);

		$soap_client = new SoapClient( $checkout_endpoint );
		$response    = $soap_client->CancelReservation( $cancel_reservation_args );

		if ( $response->IsSuccess ) {
			// Add time stamp, used to prevent duplicate cancellations for the same order.
			add_post_meta( $this->order_id, '_arvato_reservation_cancelled', current_time( 'mysql' ) );

			$order->add_order_note( __(
				'Arvato reservation was successfully cancelled.',
				'woocommerce-gateway-arvato'
			) );
		} else {
			$order->add_order_note( __(
				'Arvato reservation could not be cancelled.',
				'woocommerce-gateway-arvato'
			) );
		}
	}

}
$wc_arvato_cancel_reservation = new WC_Arvato_Cancel_Reservation;