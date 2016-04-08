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

		add_action( 'woocommerce_before_checkout_form', array( $this, 'slbd' ) );
	}

	function slbd() {
		?>
		<?php
		echo '<pre>';
		print_r( WC()->cart->cart_contents );
		echo '</pre>';
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

		$data = array(
			'something' => 'else'
		);

		if ( sizeof( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item_key => $item ) {
				$_product = $order->get_product_from_item( $item );
				$order_lines[] = array(
					'GrossUnitPrice' => $order->get_item_total( $item, true ),
					'ItemDescription' => $item['name'],
					'ItemID' => $_product->id,
					'ItemGroupId' => '9999',
					'LineNumber' => $item_key,
					'NetUnitPrice' => $order->get_item_total( $item, false ),
					'Quantity' => $item['qty'],
					'VatPercent' => $order->get_item_tax( $item ) / $order->get_item_total( $item, false )
				);
			}
		}

		return $order_lines;
		// PreCheckCustomer
		$soap_client = new SoapClient( 'https://sandboxapi.horizonafs.com/eCommerceServices/eCommerce/Checkout/v2/CheckoutServices.svc?wsdl' );
		$args_pre_check_customer = array(
			'User' => array(
				'ClientID' => 7852,
				'Password' => 'm8K1Dfuj',
				'Username' => 'WooComTestSE'
			),
			'Customer' => array(
				'Address' => array(
					'CountryCode' => 'SE',
				),
				'CustomerCategory' => 'Person',
				'Email' => 'lars.arvidsson@arvato.com',
				'MobilePhone' => '0708581465',
				'Organization_PersonalNo' => '4502251111'
			),
			'OrderDetails' => array(
				'Amount' => WC()->cart->get_total(),
				'CurrencyCode' => 'SEK',
				'OrderChannelType' => 'Internet',
				'OrderDeliveryType' => 'Normal',
				'OrderLines' => $this->format_order_lines( $order_id )
			)
		);
		$pre_check_customer_response = $soap_client->PreCheckCustomer( $args_pre_check_customer );

		wp_send_json_success( $data );

		wp_die();
	}

}

$wc_arvato_pre_check_customer = new WC_Arvato_Pre_Check_Customer();