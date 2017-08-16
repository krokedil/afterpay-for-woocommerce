<?php
/**
 * Available payment methods request.
 *
 * @package AfterPay for WooCommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class WC_AfterPay_Request_Available_Payment_Methods
 */
class WC_AfterPay_Request_Authorize_Payment extends WC_AfterPay_Request {
	/** @var string AfterPay API request path. */
	private $request_path   = '/api/v3/checkout/authorize';
	/** @var string AfterPay API request method. */
	private $request_method = 'POST';
	/**
	 * Returns Create Cart request response.
	 *
	 * @return array|WP_Error
	 */
	public function response( $order_id, $payment_method_name, $profile_no = false ) {
		$request_url = $this->base_url . $this->request_path;
		$request     = wp_remote_request( $request_url, $this->get_request_args( $order_id, $payment_method_name, $profile_no ) );
		if( ! is_wp_error( $request ) && 200 == $request['response']['code'] ) {
			return wp_remote_retrieve_body( $request );
		} else {
			return new WP_Error( 'error', wp_remote_retrieve_body( $request ) );
		}
	}
	/**
	 * Gets Create Cart request arguments.
	 *
	 * @return array
	 */
	private function get_request_args( $order_id, $payment_method_name, $profile_no = false ) {
		$request_args = array(
			'headers' => $this->request_header(),
			'body'    => $this->get_request_body( $order_id, $payment_method_name, $profile_no ),
			'method'  => $this->request_method
		);
		return $request_args;
	}
	/**
	 * Gets Create Cart request body.
	 *
	 * @return false|string
	 */
	private function get_request_body( $order_id, $payment_method_name, $profile_no = false ) {
		$order = wc_get_order( $order_id );
		// Prepare order lines for AfterPay
		$order_lines_processor = new WC_AfterPay_Process_Order_Lines();
		$order_lines = $order_lines_processor->get_order_lines( $order_id );
		$net_total_amount = 0;
		foreach ( $order_lines as $key => $value )
		{
			$net_total_amount = $net_total_amount + ( floatval( $value['netUnitPrice'] * $value['quantity'] ) );
		}
		if ( 'Installment' === $payment_method_name	) {
			$payment_method_name = 'Account';
		}
		$formatted_request_body = array(
			'payment'       	=> array( 'type' => $payment_method_name ),
			'customer'       	=> array(
				'customerCategory'	=> 'Person',
				'firstName' 		=> $order->get_billing_first_name(),
				'lastName' 			=> $order->get_billing_last_name(),
				'email' 			=> $order->get_billing_email(),
				'identificationNumber' => WC()->session->get( 'afterpay_personal_no' ),
				'address' 			=> array(
					'street' => $order->get_billing_address_1(),
					'postalCode' => $order->get_billing_postcode(),
					'postalPlace' => $order->get_billing_city(),
					'countryCode' => $order->get_billing_country(),
				),
			),
			'order'  			=> array(
				'number' 			=> $order->get_order_number(),
				'totalGrossAmount' 	=> $order->get_total(),
				'TotalNetAmount'    => $net_total_amount,
				'currency' 			=> $order->get_currency(),
				'items' 			=> $order_lines,
			),
		);
		// Add profileNo for Account and Partpayment
		if ( isset( $profile_no ) && 'Account' === $payment_method_name ) {
			if ( $profile_no < 1 ) {
				$profile_no = 1;
			}
			$formatted_request_body['payment']['account'] = array(
				'profileNo' => $profile_no,
			);
		}
		return wp_json_encode( $formatted_request_body );
	}
}