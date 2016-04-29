<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Create AfterPay contract ofr account and installment
 *
 * @class    WC_AfterPay_Create_Contract
 * @version  1.0.0
 * @package  WC_Gateway_AfterPay/Classes
 * @category Class
 * @author   Krokedil
 */
class WC_AfterPay_Create_Contract {

	/*
	 * Required parameters:
	 * CheckoutID
	 * User - ClientID
	 * User - Password
	 * User - Username
	 * PaymentInfo - PaymentMethod
	 */

	/** @var int */
	private $order_id = '';

	/** @var string */
	private $payment_method_id = '';

	/** @var array */
	private $settings = array();

	/**
	 * WC_AfterPay_Create_Contract constructor.
	 *
	 * @param $order_id          int    WooCommerce order ID
	 * @param $payment_method_id string WooCommerce payment method id
	 */
	public function __construct( $order_id, $payment_method_id ) {
		$this->order_id          = $order_id;
		$this->payment_method_id = $payment_method_id;
		$this->settings          = get_option( 'woocommerce_' . $this->payment_method_id . '_settings' );
	}

	public function create_contract() {
		$order = wc_get_order( $this->order_id );

		$customer_no = WC()->session->get( 'afterpay_customer_no' );
		$checkout_id = WC()->session->get( 'afterpay_checkout_id' );

		$payment_method_settings = $this->settings;

		// Live or test checkout endpoint, based on payment gateway settings
		$checkout_endpoint = 'yes' == $payment_method_settings['testmode'] ? ARVATO_CHECKOUT_TEST : ARVATO_CHECKOUT_LIVE;

		if ( 'afterpay_account' == $this->payment_method_id || 'afterpay_part_payment' == $this->payment_method_id ) {
			$payment_method = 'Account';
		}

		$args = array(
			'User'        => array(
				'ClientID' => $payment_method_settings['client_id'],
				'Username' => $payment_method_settings['username'],
				'Password' => $payment_method_settings['password']
			),
			'CheckoutID'  => $checkout_id,
			'PaymentInfo' => array(
				'PaymentMethod'   => $payment_method,
				'AccountInfo' => array(
					'AccountProfileNo' => 1
				)
			)
		);

		$soap_client              = new SoapClient( $checkout_endpoint );
		$create_contract_response = $soap_client->CreateContract( $args );
		error_log( 'CREATE CONTRACT ARGS: ' . var_export( $args, true ) );
		error_log( 'CREATE CONTRACT RESPONSE: ' . var_export( $create_contract_response, true ) );

		if ( $create_contract_response->IsSuccess ) {
			update_post_meta( $order->id, '_afterpay_contract_id', $create_contract_response->ContractID );
			// Store reservation ID as order note
			$order->add_order_note(
				sprintf( __( 'AfterPay contract created, contract ID: %s.', 'woocommerce-gateway-afterpay' ), $create_contract_response->ContractID )
			);

			return true;
		} else {
			return new WP_Error( 'failure', __( 'CompleteCheckout request failed.', 'woocommerce-gateway-afterpay' ) );
		}

	}
}