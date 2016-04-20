<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Complete Arvato checkout
 *
 * @class    WC_Arvato_Complete_Checkout
 * @version  1.0.0
 * @package  WC_Gateway_Arvato/Classes
 * @category Class
 * @author   Krokedil
 */
class WC_Arvato_Complete_Checkout {

	/**
	 * Mandatory fields
	 *
	 * Member name
	 * - CheckoutID
	 * - ContractID
	 * - CustomerNo (custom field _arvato_customer_no)
	 * - OrderNo (available in woocommerce_order_status_cancelled hook)
	 * - CurrencyCode
	 * - Amount (excluding VAT)
	 * - TotalOrderValue (including VAT)
	 * - OrderDate (yyyy-mm-dd)
	 *
	 * User (pulled using get_option)
	 * - ClientID
	 * - Password
	 * - Username
	 *
	 * PaymentInfo
	 * - PaymentMethod
	 *
	 * PaymentInfo.AccountInfo
	 * - AccountProfileNo (mandatory by account)
	 *
	 * PaymentInfo.InstallmentInfo
	 * AccountProfileNo (mandatory by installment)
	 * Amount (mandatory by installment)
	 * InstallmentProfileNo (mandatory by installment)
	 * NumberOfInstallments (mandatory by installment)
	 */

	/** @var int */
	private $order_id = '';

	/** @var string */
	private $payment_method_id = '';

	/** @var array */
	private $settings = array();

	/**
	 * WC_Arvato_Complete_Checkout constructor.
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
	 * Execute Arvato CompleteCheckout when WooCommerce checkout is processed.
	 */
	public function complete_checkout() {
		$order = wc_get_order( $this->order_id );

		$customer_no = WC()->session->get( 'arvato_customer_no' );
		$checkout_id = WC()->session->get( 'arvato_checkout_id' );

		$payment_method_settings = $this->settings;

		// Live or test checkout endpoint, based on payment gateway settings
		$checkout_endpoint = 'yes' == $payment_method_settings['testmode'] ? ARVATO_CHECKOUT_TEST : ARVATO_CHECKOUT_LIVE;

		switch ( $this->payment_method_id ) {
			case 'arvato_invoice':
				$payment_method = 'Invoice';
				break;
			case 'arvato_account':
				$payment_method = 'Account';
				break;
			case 'arvato_part_payment':
				$payment_method = 'Installment';
				break;
		}

		$args_complete_checkout = array(
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
			'OrderDate'       => date( 'Y-m-d', $order->order_date )
		);

		$soap_client                = new SoapClient( $checkout_endpoint );
		$complete_checkout_response = $soap_client->CompleteCheckout( $args_complete_checkout );

		if ( $complete_checkout_response->IsSuccess ) {
			update_post_meta( $order->id, '_arvato_reservation_id', $complete_checkout_response->ReservationID );

			return true;
		} else {
			return new WP_Error( 'failure', __( 'CompleteCheckout request failed.', 'woocommerce-gateway-arvato' ) );
		}
	}

}