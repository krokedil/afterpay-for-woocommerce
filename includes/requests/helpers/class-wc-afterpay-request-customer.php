<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * AfterPay API Request Customer.
 *
 * @since 1.0
 */
class WC_AfterPay_Request_Customer extends WC_AfterPay_Request {
	/** @var string AfterPay API request path. */
	private $request_path   = '/api/v3/lookup/customer';
	/** @var string AfterPay API request method. */
	private $request_method = 'POST';
	/**
	 * Returns Create Cart request response.
	 *
	 * @return array|WP_Error
	 */
	public function response( $personal_number, $billing_country ) {
		$request_url = 'https://sandbox.afterpay.io' . $this->request_path;
		$request     = wp_remote_request( $request_url, $this->get_request_args($personal_number, $billing_country) );
		if( ! is_wp_error( $request ) && 200 == $request['response']['code'] ) {
			return wp_remote_retrieve_body( $request );
		} else {
			return new WP_Error( 'error', wp_remote_retrieve_body( $request ) );
		}
		return $request;
	}
	/**
	 * Gets Create Cart request arguments.
	 *
	 * @return array
	 */
	private function get_request_args($personal_number, $billing_country) {
		$request_args = array(
			'headers' => $this->request_header(),
			'body'    => $this->get_request_body($personal_number, $billing_country),
			'method'  => $this->request_method
		);
		return $request_args;
	}
	/**
	 * Gets Create Cart request body.
	 *
	 * @return false|string
	 */
	private function get_request_body($personal_number, $billing_country) {
		$formatted_request_body 	= array(
			'countryCode'       	=> $billing_country,
			'identificationNumber'  => $personal_number
		);
		return wp_json_encode( $formatted_request_body );
	}
}