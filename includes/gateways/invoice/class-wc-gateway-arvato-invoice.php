<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action( 'plugins_loaded', 'init_wc_gateway_arvato_invoice_class' );
add_filter( 'woocommerce_payment_gateways', 'add_arvato_invoice_method' );

/**
 * Initialize Arvato Invoice payment gateway
 *
 * @wp_hook plugins_loaded
 */
function init_wc_gateway_arvato_invoice_class() {
	/**
	 * Arvato Invoice Payment Gateway.
	 *
	 * Provides Arvato Invoice Payment Gateway for WooCommerce.
	 *
	 * @class       WC_Gateway_Arvato_Invoice
	 * @extends     WC_Payment_Gateway
	 * @version     0.1
	 * @author      Krokedil
	 */
	class WC_Gateway_Arvato_Invoice extends WC_Payment_Gateway {
		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
			$this->id                 = 'arvato_invoice';
			$this->icon               = apply_filters( 'woocommerce_arvato_invoice_icon', '' );
			$this->has_fields         = true;
			$this->method_title       = __( 'Arvato Invoice', 'woocommerce-gateway-arvato' );
			$this->method_description = __( 'Allows payments through Arvato Invoice.', 'woocommerce-gateway-arvato' );

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );

			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'wp_footer', array( $this, 'footer_debug' ) );

			// Filters
		}

		/**
		 * Logging method.
		 * @param string $message
		 */
		public static function log( $message ) {
			if ( self::$log_enabled ) {
				if ( empty( self::$log ) ) {
					self::$log = new WC_Logger();
				}
				self::$log->add( 'arvato-invoice', $message );
			}
		}

		/**
		 * Initialise Gateway Settings Form Fields.
		 */
		public function init_form_fields() {
			$this->form_fields = include( 'includes/settings-invoice.php' );
		}

		/**
		 * Outputs Gateway Form Fields.
		 */
		public function payment_fields() {
			// Fields displayed in checkout page
		}

		function footer_debug() {
			$endpoint = 'https://sandboxapi.horizonafs.com/eCommerceServices/eCommerce/Checkout/v2/CheckoutServices.svc?wsdl';
			$soap_client = new SoapClient( $endpoint );

			$args = array(
				'User' => array(
					'ClientID' => 7852,
					'Password' => 'm8K1Dfuj',
					'Username' => 'WooComTestSE'
				),
				'Customer' => array(
					'Address' => array(
						'CountryCode' => 'SE',
						'PostalCode' => 75649,
						'PostalPlace' => 'UPPSALA',
						'Street' => 'Ågatan'
					),
					'CustomerCategory' => 'Person',
					'Email' => 'lars.arvidsson@arvato.com',
					'FirstName' => 'Nils Gunnar',
					'LastName' => 'Nyman',
					'MobilePhone' => '0708581465',
					'Organization_PersonalNo' => '4502251111'
				),
				'OrderDetails' => array(
					'Amount' => 4000,
					'CurrencyCode' => 'SEK',
					'OrderChannelType' => 'Internet',
					'OrderDeliveryType' => 'Normal',
					'OrderLines' => array(
						array(
							'GrossUnitPrice' => 4000,
							'ItemDescription' => 'Blah',
							'ItemID' => 99,
							'LineNumber' => 1,
							'NetUnitPrice' => 4000,
							'Quantity' => 1,
							'VatPercent' => 0
						)
					)
				)
			);

			$response = $soap_client->PreCheckCustomer( $args );
			/*
			$response = wp_remote_post( $endpoint, array(
					'method' => 'POST',
					'timeout' => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(
						'Content-Type' => 'text/xml; charset=utf-8',
					),
					'body' => $args,
					'cookies' => array()
				)
			);
			*/

			echo '<pre>';
			print_r( $response );
			echo '</pre>';
		}
	}
}

/**
 * Add Arvato payment gateway
 *
 * @wp_hook woocommerce_payment_gateways
 *
 * @param  $methods Array All registered payment methods
 * @return $methods Array All registered payment methods
 */
function add_arvato_invoice_method( $methods ) {
	$methods[] = 'WC_Gateway_Arvato_Invoice';
	return $methods;
}