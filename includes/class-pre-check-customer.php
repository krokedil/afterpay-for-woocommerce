<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Runs PreCheckCustomer for AfterPay payment methods
 *
 * @class    WC_AfterPay_Pre_Check_Customer
 * @version  1.0.0
 * @package  WC_Gateway_AfterPay/Classes
 * @category Class
 * @author   Krokedil
 */
class WC_AfterPay_Pre_Check_Customer {

	/**
	 * WC_AfterPay_Pre_Check_Customer constructor.
	 */
	public function __construct() {
		// Enqueue JS file
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Register AJAX callback
		add_action( 'wp_ajax_afterpay_pre_check_customer', array( $this, 'pre_check_customer' ) );
		add_action( 'wp_ajax_nopriv_afterpay_pre_check_customer', array( $this, 'pre_check_customer' ) );

		add_action( 'woocommerce_before_checkout_billing_form', array( $this, 'display_pre_check_form' ) );
		add_action( 'woocommerce_checkout_init', array( $this, 'maybe_pre_check_customer' ) );
	}

	/**
	 * Run PreCheckCustomer on checkout load if we have a personal number and an Arvato method is selected
	 */
	public function maybe_pre_check_customer() {
		$chosen_payment_method = WC()->session->chosen_payment_method;
		if ( strpos( $chosen_payment_method, 'afterpay' ) !== false ) {
			$personal_no = '';

			if ( WC()->session->get( 'afterpay_personal_no' ) ) {
				$personal_no = WC()->session->get( 'afterpay_personal_no' );
			} else if ( ! is_user_logged_in() ) {
				$user = wp_get_current_user();
				if ( get_user_meta( $user->ID, '_arvato_personal_number', true ) ) {
					$personal_no = get_user_meta( $user->ID, '_arvato_personal_number', true );
				}
			}

			if ( '' != $personal_no ) {
				$pre_check_customer_response = $this->pre_check_customer_request( $personal_no, $chosen_payment_method );

				if ( $pre_check_customer_response->IsSuccess ) {
					// Set session data
					WC()->session->set( 'afterpay_checkout_id', $pre_check_customer_response->CheckoutID );
					WC()->session->set( 'afterpay_customer_no', $pre_check_customer_response->Customer->CustomerNo );
					WC()->session->set( 'afterpay_personal_no', $personal_no );
					WC()->session->set( 'afterpay_allowed_payment_methods', $pre_check_customer_response->AllowedPaymentMethods->AllowedPaymentMethod );
				}
			}
		}
	}

	public static function display_pre_check_form() {
		$personal_number = WC()->session->get( 'afterpay_personal_no' ) ? WC()->session->get( 'afterpay_personal_no' ) : '';
		?>
		<div id="afterpay-pre-check-customer" style="display:none">
			<p>
				<input type="radio" class="input-radio" value="Person" name="afterpay_customer_category" id="afterpay-customer-category-person" checked />
				<label for="afterpay-customer-category-person"><?php _e( 'Person', 'woocommerce-gateway-afterpay' ); ?></label>
				<br />
				<input type="radio" class="input-radio" value="Company" name="afterpay_customer_category" id="afterpay-customer-category-company" />
				<label for="afterpay-customer-category-company"><?php _e( 'Company', 'woocommerce-gateway-afterpay' ); ?></label>
			</p>
			<p class="form-row form-row-wide validate-required">
				<?php /*
	            <label for="afterpay-pre-check-customer-pn">
					<?php _e( 'Personal/organization number', 'woocommerce-gateway-afterpay' ); ?>
					<abbr class="required" title="required">*</abbr>
				</label>
	            */ ?>
				<input type="text" name="afterpay-pre-check-customer-pn" id="afterpay-pre-check-customer-number" class="afterpay-pre-check-customer-number"
				       placeholder="<?php _e( 'Personal/organization number', 'woocommerce-gateway-afterpay' ); ?>" value="<?php echo $personal_number; ?>" />
				<button type="button" style="margin-top:0.5em" class="afterpay-get-address-button button"><?php _e( 'Get address', 'woocommerce-gateway-klarna' ); ?></button>
			</p>
		</div>
		<?php
	}

	/**
	 * Load the JS file(s).
	 */
	public function enqueue_scripts() {
		wp_register_script( 'afterpay_pre_check_customer', plugins_url( 'assets/js/pre-check-customer.js', __DIR__ ),
			array( 'jquery' ), false, true );
		wp_localize_script( 'afterpay_pre_check_customer', 'WC_AfterPay', array(
			'ajaxurl'                         => admin_url( 'admin-ajax.php' ),
			'afterpay_pre_check_customer_nonce' => wp_create_nonce( 'afterpay_pre_check_customer_nonce' ),
		) );
		wp_enqueue_script( 'afterpay_pre_check_customer' );
	}

	/**
	 * Pre check customer for AfterPay payment methods.
	 */
	public function pre_check_customer() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'afterpay_pre_check_customer_nonce' ) ) {
			exit( 'Nonce can not be verified.' );
		}

		$data = array();

		$personal_number = $_REQUEST['personal_number'];
		$payment_method = $_REQUEST['payment_method'];
		$customer_category = $_REQUEST['customer_category'];

		if ( $customer_category != 'Company' ) {
			$customer_category = 'Person';
		}

		$pre_check_customer_response = $this->pre_check_customer_request( $personal_number, $payment_method, $customer_category );
		$data['response'] = $pre_check_customer_response;
		$data['message'] = __( 'Address found and added to checkout form.', 'woocommerce-gateway-afterpay' );

		if ( $pre_check_customer_response->IsSuccess ) {
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( $data );
		}

		wp_die();
	}

	// To be used when PreCheckCustomer is not an AJAX call (when customer is logged in and
	// we have their personal number)
	public function pre_check_customer_request( $personal_number, $payment_method, $customer_category = 'Person' ) {
		// Prepare order lines for AfterPay
		$order_lines_processor = new WC_AfterPay_Process_Order_Lines();
		$order_lines = $order_lines_processor->get_order_lines();

		$payment_method_settings = get_option( 'woocommerce_' . $payment_method . '_settings' );

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
				'CustomerCategory' => $customer_category,
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
		$response = $soap_client->PreCheckCustomer( $args );

		error_log( var_export( $response, true ) );

		if ( $response->IsSuccess ) {
			// Set session data
			WC()->session->set( 'afterpay_checkout_id', $response->CheckoutID );
			WC()->session->set( 'afterpay_customer_no', $response->Customer->CustomerNo );
			WC()->session->set( 'afterpay_personal_no', $personal_number );
			WC()->session->set( 'afterpay_allowed_payment_methods', $response->AllowedPaymentMethods->AllowedPaymentMethod );

			// Capture user's personal number as meta field, if logged in
			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();
				add_user_meta( $user->ID, '_arvato_personal_number', $personal_number );
			}

			// Send success
			return $response;
		} else {
			return false;
		}
	}

}
$wc_afterpay_pre_check_customer = new WC_AfterPay_Pre_Check_Customer();