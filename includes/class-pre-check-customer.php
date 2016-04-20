<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Runs PreCheckCustomer for Arvato payment methods
 *
 * @class    WC_Arvato_Pre_Check_Customer
 * @version  1.0.0
 * @package  WC_Gateway_Arvato/Classes
 * @category Class
 * @author   Krokedil
 */
class WC_Arvato_Pre_Check_Customer {

	/**
	 * WC_Arvato_Pre_Check_Customer constructor.
	 */
	public function __construct() {
		// Enqueue JS file
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Register AJAX callback
		add_action( 'wp_ajax_arvato_pre_check_customer', array( $this, 'pre_check_customer' ) );
		add_action( 'wp_ajax_nopriv_arvato_pre_check_customer', array( $this, 'pre_check_customer' ) );

		add_action( 'woocommerce_before_checkout_form', array( $this, 'display_pre_check_form' ) );

		add_action( 'woocommerce_checkout_init', array( $this, 'test' ) );
	}

	public function test() {
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			echo '<p>Logged in ' . $user->exists() . '</p>';
			echo '<pre>';
			print_r( $user );
			echo '</pre>';
		} else {

			echo '<p>Not logged in</p>';
		}
	}

	public function display_pre_check_form() {
		?>
		<form id="arvato-pre-check-customer" class="arvato-pre-check-customer">
			<p class="form-row form-row-wide validate-required">
				<input type="text" name="arvato-pre-check-customer-pn" id="arvato-pre-check-customer-pn"
				       placeholder="Personal number" />
			</p>
			<p class="form-row form-row-wide">
				<input type="submit" for="arvato-pre-check-customer" value="Fetch customer information" />
			</p>
		</form>
		<p>Not you? <a href="#">Click here</a> to enter your personal number.</p>
		<?php
	}

	public function enqueue_scripts() {
		wp_register_script( 'arvato_pre_check_customer', plugins_url( 'assets/js/pre-check-customer.js', __DIR__ ),
			array( 'jquery' ), false, true );
		wp_localize_script( 'arvato_pre_check_customer', 'WC_Arvato', array(
			'ajaxurl'                         => admin_url( 'admin-ajax.php' ),
			'arvato_pre_check_customer_nonce' => wp_create_nonce( 'arvato_pre_check_customer_nonce' ),
		) );
		wp_enqueue_script( 'arvato_pre_check_customer' );
	}

	/**
	 * Pre check customer for Arvato payment methods.
	 */
	public function pre_check_customer() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'arvato_pre_check_customer_nonce' ) ) {
			exit( 'Nonce can not be verified.' );
		}

		$data = array();

		$personal_number = $_REQUEST['personal_number'];

		// Needed to get Arvato account information
		$payment_method = $_REQUEST['payment_method'];
		$payment_method_settings = get_option( 'woocommerce_' . $payment_method . '_settings' );

		// Prepare order lines for Arvato
		$order_lines_processor = new WC_Arvato_Process_Order_Lines();
		$order_lines = $order_lines_processor->get_order_lines();

		// Live or test checkout endpoint, based on payment gateway settings
		$checkout_endpoint = 'yes' == $payment_method_settings['testmode'] ? ARVATO_CHECKOUT_TEST :
			ARVATO_CHECKOUT_LIVE;

		// PreCheckCustomer
		$soap_client = new SoapClient( $checkout_endpoint );
		$args = array(
			'User' => array(
				'ClientID' => $payment_method_settings['client_id'],
				'Username' => $payment_method_settings['username'],
				'Password' => $payment_method_settings['password']
			),
			'Customer' => array(
				'Address' => array(
					'CountryCode' => 'SE',
				),
				'CustomerCategory' => 'Person',
				'Organization_PersonalNo' => $personal_number,
			),
			'OrderDetails' => array(
				'Amount' => WC()->cart->total,
				'CurrencyCode' => 'SEK',
				'OrderChannelType' => 'Internet',
				'OrderDeliveryType' => 'Normal',
				'OrderLines' => $order_lines
			)
		);
		$pre_check_customer_response = $soap_client->PreCheckCustomer( $args );
		$data['response'] = $pre_check_customer_response;

		if ( $pre_check_customer_response->IsSuccess ) {
			// Set session data
			WC()->session->set( 'arvato_checkout_id', $pre_check_customer_response->CheckoutID );
			WC()->session->set( 'arvato_customer_no', $pre_check_customer_response->Customer->CustomerNo );

			// Send success
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( $data );
		}

		wp_die();
	}

	public function pre_check_customer_request( $personal_number, $payment_method ) {
		// Prepare order lines for Arvato
		$order_lines_processor = new WC_Arvato_Process_Order_Lines();
		$order_lines = $order_lines_processor->get_order_lines();

		$payment_method_settings = get_option( 'woocommerce_' . $payment_method . '_settings' );

		// PreCheckCustomer
		$soap_client = new SoapClient( $checkout_endpoint );
		$args = array(
			'User' => array(
				'ClientID' => $payment_method_settings['client_id'],
				'Username' => $payment_method_settings['username'],
				'Password' => $payment_method_settings['password']
			),
			'Customer' => array(
				'Address' => array(
					'CountryCode' => 'SE',
				),
				'CustomerCategory' => 'Person',
				'Organization_PersonalNo' => $personal_number,
			),
			'OrderDetails' => array(
				'Amount' => WC()->cart->total,
				'CurrencyCode' => 'SEK',
				'OrderChannelType' => 'Internet',
				'OrderDeliveryType' => 'Normal',
				'OrderLines' => $order_lines
			)
		);
		$pre_check_customer_response = $soap_client->PreCheckCustomer( $args );
	}

}
$wc_arvato_pre_check_customer = new WC_Arvato_Pre_Check_Customer();