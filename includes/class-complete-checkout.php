<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Complete AfterPay checkout
 *
 * @class    WC_AfterPay_Complete_Checkout
 * @version  1.0.0
 * @package  WC_Gateway_AfterPay/Classes
 * @category Class
 * @author   Krokedil
 */
class WC_AfterPay_Complete_Checkout {

	/** @var int */
	private $order_id = '';

	/** @var string */
	private $payment_method_id = '';

	/** @var array */
	private $settings = array();

	/**
	 * WC_AfterPay_Complete_Checkout constructor.
	 *
	 * @param $order_id          int    WooCommerce order ID
	 * @param $payment_method_id string WooCommerce payment method id
	 */
	public function __construct( $order_id, $payment_method_id ) {
		$this->order_id          = $order_id;
		$this->payment_method_id = $payment_method_id;
		$this->settings          = get_option( 'woocommerce_' . $this->payment_method_id . '_settings' );
	}

	/**
	 * Execute AfterPay CompleteCheckout when WooCommerce checkout is processed.
	 */
	public function complete_checkout() {
		$order = wc_get_order( $this->order_id );

		$customer_no = WC()->session->get( 'afterpay_customer_no' );
		$checkout_id = WC()->session->get( 'afterpay_checkout_id' );

		$payment_method_settings = $this->settings;

		// Live or test checkout endpoint, based on payment gateway settings
		$checkout_endpoint = 'yes' == $payment_method_settings['testmode'] ? ARVATO_CHECKOUT_TEST : ARVATO_CHECKOUT_LIVE;

		switch ( $this->payment_method_id ) {
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

		$args = array(
			'User'            => array(
				'ClientID' => $payment_method_settings['client_id'],
				'Username' => $payment_method_settings['username'],
				'Password' => $payment_method_settings['password']
			),
			'CheckoutID'      => $checkout_id,
			'OrderNo'         => $this->order_id,
			'CustomerNo'      => $customer_no,
			'Amount'          => $order->get_total(),
			'TotalOrderValue' => $order->get_total(),
			'PaymentInfo'     => array(
				'PaymentMethod' => $payment_method
			),
			'OrderDate'       => date( 'Y-m-d', strtotime( $order->order_date ) )
		);

		if ( 'afterpay_account' == $this->payment_method_id ) {
			$args['PaymentInfo']['AccountInfo'] = array(
				'AccountProfileNo' => 1
			);
		} elseif ( 'afterpay_part_payment' == $this->payment_method_id ) { // part_payment
			if ( isset( $_POST['afterpay_installment_plan'] ) ) {
				$args['PaymentInfo']['AccountInfo'] = array(
					'AccountProfileNo' => wc_clean( $_POST['afterpay_installment_plan'] )
				);
			}
		}

		error_log( 'COMPLETE CHECKOUT ARGS: ' . var_export( $args, true ) );

		$soap_client                = new SoapClient( $checkout_endpoint );
		$complete_checkout_response = $soap_client->CompleteCheckout( $args );

		if ( $complete_checkout_response->IsSuccess ) {
			error_log( 'COMPLETE CHECKOUT RESPONSE: ' . var_export( $complete_checkout_response, true ) );

			update_post_meta( $order->id, '_afterpay_reservation_id', $complete_checkout_response->ReservationID );

			// Unset AfterPay session values
			WC()->session->__unset( 'afterpay_checkout_id' );
			WC()->session->__unset( 'afterpay_customer_no' );
			WC()->session->__unset( 'afterpay_allowed_payment_methods' );

			return true;
		} else {
			return new WP_Error( 'failure', __( 'CompleteCheckout request failed.', 'woocommerce-gateway-afterpay' ) );
		}
	}

}