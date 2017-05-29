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
 * Class WC_AfterPay_Request_CreateContract
 */
class WC_AfterPay_Request_CreateContract extends WC_AfterPay_Request {

	/** @var string AfterPay API request path. */
	private $request_path   = '/api/v3/checkout';
	/** @var string AfterPay API request method. */
	private $request_method = 'POST';
	/**
	 * Returns Create Cart request response.
	 *
	 * @return array|WP_Error
	 */
	public function response( $checkout_id, $payment_method_name ) {
		$request_url = 'https://sandbox.afterpay.io' . $this->request_path . '/' . $checkout_id . '/contract';
		$request     = wp_remote_request( $request_url, $this->get_request_args( $checkout_id, $payment_method_name ) );
		if ( ! is_wp_error( $request ) && 200 == $request['response']['code'] ) {
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
	private function get_request_args( $checkout_id, $payment_method_name ) {
		$request_args = array(
			'headers' => $this->request_header(),
			'body'    => $this->get_request_body( $checkout_id, $payment_method_name ),
			'method'  => $this->request_method
		);
		return $request_args;
	}
	/**
	 * Gets Create Cart request body.
	 *
	 * @return false|string
	 */
	private function get_request_body( $checkout_id, $payment_method_name ) {
		$formatted_request_body = array(
			'paymentInfo'       => array('type' => $payment_method_name,
			),
		);
		return wp_json_encode( $formatted_request_body );
	}
}