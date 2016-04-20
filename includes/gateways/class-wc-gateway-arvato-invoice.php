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
	 * @extends     WC_Gateway_Arvato_Factory
	 * @version     0.1
	 * @author      Krokedil
	 */
	class WC_Gateway_Arvato_Invoice extends WC_Gateway_Arvato_Factory {

		/** @var bool Whether or not logging is enabled */
		public static $log_enabled = false;

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
			$this->id                 = 'arvato_invoice';
			$this->icon               = apply_filters( 'woocommerce_arvato_invoice_icon', '' );
			$this->has_fields         = true;
			$this->method_title       = __( 'Arvato Invoice', 'woocommerce-gateway-arvato' );
			$this->method_description = __( 'Allows payments through Arvato Invoice.', 'woocommerce-gateway-arvato' );

			$this->supports = array(
				'products',
				'refunds'
			);

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables
			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->client_id   = $this->get_option( 'client_id' );
			$this->username    = $this->get_option( 'username' );
			$this->password    = $this->get_option( 'password' );
			$this->debug       = $this->get_option( 'debug' );

			self::$log_enabled = $this->debug;

			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
				$this,
				'process_admin_options'
			) );
			add_action( 'wp_footer', array( $this, 'footer_debug' ) );

			// Filters
		}

		/**
		 * Logging method.
		 *
		 * @param string $message
		 */
		public static function log( $message ) {
			/*
			if ( self::$log_enabled ) {
				if ( empty( self::$log ) ) {
					self::$log = new WC_Logger();
				}
				self::$log->add( 'arvato', $message );
			}
			*/
		}

		/**
		 * Check if payment method is available for current customer
		 */
		public function is_available() {
			return true;
		}

		function footer_debug() {
			$order = wc_get_order( 193 );
				echo '<pre style="color:#fff">';
				print_r( $order->get_fees() );
				echo '</pre>';

			$processor = new WC_Arvato_Process_Order_Lines;
			echo '<pre style="color:#fff">';
			print_r( $processor->get_order_lines( 193 ) );
			echo '</pre>';

			/*
			$the_order = wc_get_order(169);
			echo '<pre style="color:#fff">';
			foreach( $the_order->get_items() as $item_id => $item ) {
				echo $the_order->get_total_refunded_for_item($item_id);
				echo '<br />';
			}
			echo '</pre>';
			*/

			/*
			$soap_client_1 = new SoapClient( 'https://sandboxapi.horizonafs.com/eCommerceServices/eCommerce/Checkout/v2/CheckoutServices.svc?wsdl' );

			// PreCheckCustomer

			$args_1 = array(
				'User'         => array(
					'ClientID' => 7852,
					'Password' => 'm8K1Dfuj',
					'Username' => 'WooComTestSE'
				),
				'Customer'     => array(
					'Address'                 => array(
						'CountryCode' => 'SE',
						'PostalCode'  => 75649,
						'PostalPlace' => 'UPPSALA',
						'Street'      => 'Ågatan'
					),
					'CustomerCategory'        => 'Person',
					'Email'                   => 'lars.arvidsson@arvato.com',
					'FirstName'               => 'Nils GunnarThird Name',
					'LastName'                => 'Nyman',
					'MobilePhone'             => '0708581465',
					'Organization_PersonalNo' => '4502251111'
				),
				'OrderDetails' => array(
					'Amount'            => 24000,
					'CurrencyCode'      => 'SEK',
					'OrderChannelType'  => 'Internet',
					'OrderDeliveryType' => 'Normal',
					'OrderLines'        => array(
						array(
							'GrossUnitPrice'  => 24000,
							'ItemDescription' => 'Blah',
							'ItemID'          => 99,
							'ItemGroupId'     => '9999',
							'LineNumber'      => 1,
							'NetUnitPrice'    => 24000,
							'Quantity'        => 1,
							'VatPercent'      => 0
						)
					)
				)
			);

			$response_1 = $soap_client_1->PreCheckCustomer( $args_1 );

			echo '<pre style="color: #fff">';
			echo '<h1>Response: PreCheckCustomer</h1>';
			print_r( $response_1 );
			echo '</pre>';

			// CreateContract

			$args_2 = array(
				'User'        => array(
					'ClientID' => 7852,
					'Password' => 'm8K1Dfuj',
					'Username' => 'WooComTestSE'
				),
				'CheckoutID'  => $response_1->CheckoutID,
				'PaymentInfo' => array(
					'PaymentMethod'   => 'Account',
					'InstallmentInfo' => array(
						'AccountProfileNo' => 1,
						'Amount'           => 24000
					)
				)
			);

			$soap_client_2 = new SoapClient( 'https://sandboxapi.horizonafs.com/eCommerceServices/eCommerce/Checkout/v2/CheckoutServices.svc?wsdl' );
			$response_2    = $soap_client_2->CreateContract( $args_2 );

			echo '<pre style="color: #fff">';
			echo '<h1>Response: CreateContract</h1>';
			print_r( $response_2 );
			echo '</pre>';

			// CompleteCheckout
			$order_no = strval( rand( 1000, 1000000 ) );

			$args_3 = array(
				'User'            => array(
					'ClientID' => 7852,
					'Password' => 'm8K1Dfuj',
					'Username' => 'WooComTestSE'
				),
				'CheckoutID'      => $response_1->CheckoutID,
				'OrderNo'         => $order_no,
				'CustomerNo'      => $response_1->Customer->CustomerNo,
				'Amount'          => 24000,
				'TotalOrderValue' => '24000',
				'PaymentInfo'     => array(
					'PaymentMethod' => 'Account'
				),
				'OrderDate'       => '2016-03-30'
			);

			$soap_client_3 = new SoapClient( 'https://sandboxapi.horizonafs.com/eCommerceServices/eCommerce/Checkout/v2/CheckoutServices.svc?wsdl' );
			$response_3    = $soap_client_3->CompleteCheckout( $args_3 );

			echo '<pre style="color: #fff">';
			echo '<h1>Response: CompleteCheckout</h1>';
			print_r( $response_3 );
			echo '</pre>';

			// CaptureFull

			$args_4 = array(
				'User'             => array(
					'ClientID' => 7852,
					'Password' => 'm8K1Dfuj',
					'Username' => 'WooComTestSE'
				),
				'TransactionID'    => $response_3->TransactionID,
				'Amount'           => 1394,
				'PaymentInfo'      => array(
					'PaymentMethod' => 'Account'
				),
				'ContractDate'     => '2016-04-05',
				'OrderDetails'     => array(
					'Amount'            => 24000,
					'TotalOrderValue'   => 24000,
					'CurrencyCode'      => 'SEK',
					'OrderChannelType'  => 'Internet',
					'OrderDeliveryType' => 'Normal',
					'OrderLines'        => array(
						array(
							'GrossUnitPrice'  => 24000,
							'ItemDescription' => 'Blah',
							'ItemID'          => 99,
							'ItemGroupId'     => '9999',
							'LineNumber'      => 1,
							'NetUnitPrice'    => 24000,
							'Quantity'        => 1,
							'VatPercent'      => 0
						)
					),
					'OrderNo'           => $order_no,
				),
				'YourRef'          => 'Britta Skoog',
				'OurRef'           => 'Ann Holm',
				'StatcodeNum'      => 12,
				'StatcodeAlphaNum' => 'Blått'
			);

			$soap_client_4 = new SoapClient( $this->endpoint_order_management );
			$response_4    = $soap_client_4->CaptureFull( $args_4 );

			echo '<pre style="color: #fff">';
			echo '<h1>Response: CaptureFull</h1>';
			print_r( $response_4 );
			echo '</pre>';
			*/
		}

		/**
		 * Process the payment and return the result.
		 *
		 * @param  int $order_id
		 *
		 * @return array
		 */
		public function process_paymenttt( $order_id ) {
			// Use WC_Arvato_Complete_Checkout class to process the payment
			// Must previously perform PreCheckCustomer
			// CheckoutID and CustomerNo are required and returned from PreCheckCustomer
			$wc_arvato_complete_checkout = new WC_Arvato_Complete_Checkout();

			// If error
			wc_add_notice( __( 'Payment error:', 'woothemes' ) . $error_message, 'error' );

			return;


			$order       = new WC_Order( $order_id );
			$soap_client = new SoapClient( $this->endpoint_checkout );

			// PreCheckCustomer
			$args_pre_check_customer     = array(
				'User'         => array(
					'ClientID' => 7852,
					'Password' => 'm8K1Dfuj',
					'Username' => 'WooComTestSE'
				),
				'Customer'     => array(
					'Address'                 => array(
						'CountryCode' => 'SE',
					),
					'CustomerCategory'        => 'Person',
					'Email'                   => 'lars.arvidsson@arvato.com',
					'MobilePhone'             => '0708581465',
					'Organization_PersonalNo' => '4502251111'
				),
				'OrderDetails' => array(
					'Amount'            => $order->get_total(),
					'CurrencyCode'      => 'SEK',
					'OrderChannelType'  => 'Internet',
					'OrderDeliveryType' => 'Normal',
					'OrderLines'        => $this->format_order_lines( $order_id )
				)
			);
			$pre_check_customer_response = $soap_client->PreCheckCustomer( $args_pre_check_customer );

			// error_log( 'RESPONSE PRE CHECK CUSTOMER: ' . var_export( $pre_check_customer_response, true ) );

			update_post_meta( $order->id, '_arvato_customer_no', $pre_check_customer_response->Customer->CustomerNo );

			// CompleteCheckout

			$args_complete_checkout = array(
				'User'        => array(
					'ClientID' => 7852,
					'Password' => 'm8K1Dfuj',
					'Username' => 'WooComTestSE'
				),
				'CheckoutID'  => $pre_check_customer_response->CheckoutID,
				'OrderNo'     => $order->id,
				'CustomerNo'  => $pre_check_customer_response->Customer->CustomerNo,
				'Amount'      => $order->get_total(),
				'PaymentInfo' => array(
					'PaymentMethod' => 'Invoice'
				),
				'OrderDate'   => date( 'Y-m-d', $order->order_date )
			);

			$response_complete_checkout = $soap_client->CompleteCheckout( $args_complete_checkout );

			// error_log( 'RESPONSE COMPLETE CHECKOUT: ' . var_export( $response_complete_checkout, true ) );

			if ( $response_complete_checkout->IsSuccess ) {
				/*
				$args_2 = array(
					'User' => array(
						'ClientID' => 7852,
						'Password' => 'm8K1Dfuj',
						'Username' => 'WooComTestSE'
					),
					'ReservationID' => $response_1->ReservationID,
					'Amount' => $order->get_total(),
					'PaymentInfo' => array(
						'PaymentMethod' => 'Invoice'
					),
					'ContractDate' => '2016-04-05',
					'OrderDetails' => array(
						'Amount' => $order->get_total(),
						'TotalOrderValue' => $order->get_total(),
						'CurrencyCode' => 'SEK',
						'OrderChannelType' => 'Internet',
						'OrderDeliveryType' => 'Normal',
						'OrderLines' => $order_lines,
						'OrderNo' => $order->id,
					),
					'YourRef' => 'Britta Skoog',
					'OurRef' => 'Ann Holm',
					'StatcodeNum' => 12,
					'StatcodeAlphaNum' => 'Blått'
				);

				$endpoint_capture = $this->endpoint_order_management;
				$soap_client_2 = new SoapClient( $endpoint_capture );
				$response_2 = $soap_client_2->CaptureFull( $args_2 );

				error_log( 'RESPONSE2: ' . var_export( $response_2, true ) );
				*/

				// Mark payment complete on success
				$order->payment_complete();

				// Remove cart
				WC()->cart->empty_cart();

				// Return thank you redirect
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order )
				);
			}

			// If error
			// wc_add_notice( __('Payment error:', 'woothemes') . $error_message, 'error' );
			// return;

			/*
			$args = array(
				'User' => array(
					'ClientID' => $this->client_id,
					'Password' => $this->password,
					'Username' => $this->username
				),
				'CheckoutID' => $response->CheckoutID,
				'OrderNo' => '122',
				'CustomerNo' => '100003',
				'Amount' => 4000,
				'TotalOrderValue' => '4000',
				'PaymentInfo' => array(
					'PaymentMethod' => 'Invoice'
				),
				'OrderDate' => '2016-03-30'
			);

			$soap_client1 = new SoapClient( $endpoint );
			$response1 = $soap_client1->CompleteCheckout( $args );

			echo '<pre>';
			print_r( $response1 );
			echo '</pre>';
			*/
		}

		/**
		 * Format WooCommerce order lines for Arvato
		 */
		public function format_order_lines( $order_id ) {
			$order       = new WC_Order( $order_id );
			$order_lines = array();

			if ( sizeof( $order->get_items() ) > 0 ) {
				foreach ( $order->get_items() as $item_key => $item ) {
					$_product      = $order->get_product_from_item( $item );
					$order_lines[] = array(
						'GrossUnitPrice'  => $order->get_item_total( $item, true ),
						'ItemDescription' => $item['name'],
						'ItemID'          => $_product->id,
						'ItemGroupId'     => '9999',
						'LineNumber'      => $item_key,
						'NetUnitPrice'    => $order->get_item_total( $item, false ),
						'Quantity'        => $item['qty'],
						'VatPercent'      => $order->get_item_tax( $item ) / $order->get_item_total( $item, false )
					);
				}
			}

			return $order_lines;
		}
	}

}

/**
 * Add Arvato payment gateway
 *
 * @wp_hook woocommerce_payment_gateways
 *
 * @param  $methods Array All registered payment methods
 *
 * @return $methods Array All registered payment methods
 */
function add_arvato_invoice_method( $methods ) {
	$methods[] = 'WC_Gateway_Arvato_Invoice';

	return $methods;
}