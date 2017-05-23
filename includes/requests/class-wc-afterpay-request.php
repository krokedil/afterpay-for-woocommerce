<?php
/**
 * API request parent class.
 *
 * @package AfterPay for WooCommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class WC_AfterPay_Request
 */
class WC_AfterPay_Request {
	/**
	 * AfterPay API key, used in all API requests.
	 *
	 * @var string
	 */
	private $x_auth_key;
	/**
	 * AfterPay API request base url, different for test and live mode.
	 *
	 * @var string
	 */
	private $base_url;
	/**
	 * AfterPay API request resource url.
	 *
	 * @var string
	 */
	protected $resource_url;
	/**
	 * WC_AfterPay_Request constructor.
	 *
	 * @param string  $x_auth_key   API key.
	 * @param boolean $test_mode Test mode.
	 */
	public function __construct( $x_auth_key = '', $test_mode = false ) {
		$this->x_auth_key  = $x_auth_key;
		$this->base_url = $test_mode ? 'https://sandboxapi.horizonafs.com/eCommerceServicesWebApi' : 'https://api.afterpay.io';
	}
	/**
	 * Returns formatted request header.
	 *
	 * @return array
	 */
	protected function request_header() {
		return array(
			'X-Auth-Key'   => $this->x_auth_key,
			'Content-Type' => 'application/json',
		);
	}
	/**
	 * Returns formatted request body.
	 * Must be overridden in child classes.
	 */
	protected function request_body() {
		die( 'function WC_AfterPay_Request::request_body() must be over-ridden in a sub-class.' );
	}
	/**
	 * Perform API request.
	 */
	public function request() {
		$response = wp_remote_request(
			$this->base_url . $this->resource_url,
			array(
				'headers' => $this->request_header(),
			)
		);
		if ( 200 === $response['response']['code'] ) {
			$response_body = json_decode( $response['body'] );
			echo '<pre>';
			echo esc_html( $response_body->version );
			echo '</pre>';
		}
	}
}
$wc_afterpay_request = new WC_AfterPay_Request();