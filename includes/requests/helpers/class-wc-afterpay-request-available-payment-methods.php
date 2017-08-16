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
class WC_AfterPay_Request_Available_Payment_Methods extends WC_AfterPay_Request {
	/** @var string AfterPay API request path. */
	public $request_path   = '/api/v3/checkout/payment-methods';
	/** @var string AfterPay API request method. */
	public $request_method = 'POST';
	/**
	 * Returns Create Cart request response.
	 *
	 * @return array|WP_Error
	 */
	public function response( $personal_number, $email, $customer_category ) {
		$request_url = $this->base_url . $this->request_path;
		$request     = wp_remote_retrieve_body( wp_remote_request( $request_url, $this->get_request_args( $personal_number, $email, $customer_category ) ) );
		return $request;
	}
	/**
	 * Gets Create Cart request arguments.
	 *
	 * @return array
	 */
	private function get_request_args( $personal_number, $email, $customer_category ) {
		$request_args = array(
			'headers' => $this->request_header(),
			'body'    => $this->get_request_body( $personal_number, $email, $customer_category ),
			'method'  => $this->request_method,
		);
		return $request_args;
	}
	/**
	 * Gets Create Cart request body.
	 *
	 * @return false|string
	 */
	private function get_request_body( $personal_number, $email, $customer_category ) {
		$formatted_request_body = array(
			'customer'       	=> array('customerCategory' => $customer_category, 'identificationNumber' => $personal_number, 'email' => $email ),
			'order'  			=> array('totalGrossAmount' => WC()->cart->total, 'currency' => get_woocommerce_currency() ),
		);
		return wp_json_encode( $formatted_request_body );
	}
}