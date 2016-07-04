<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action( 'plugins_loaded', 'init_wc_gateway_afterpay_factory_class' );

/**
 * Initialize AfterPay Invoice payment gateway
 *
 * @wp_hook plugins_loaded
 */
function init_wc_gateway_afterpay_factory_class() {
	/**
	 * AfterPay Payment Gateway Factory.
	 *
	 * Parent class for all AfterPay payment methods.
	 *
	 * @class       WC_Gateway_AfterPay_Factory
	 * @extends     WC_Payment_Gateway
	 * @version     0.1
	 * @author      Krokedil
	 */
	class WC_Gateway_AfterPay_Factory extends WC_Payment_Gateway {

		/** @var WC_Logger Logger instance */
		public static $log = false;

		/**
		 * Logging method.
		 *
		 * @param string $message
		 */
		public static function log( $message ) {
			$afterpay_settings = get_option( 'woocommerce_afterpay_invoice_settings' );
			if ( $afterpay_settings['debug'] == 'yes' ) {
				if ( empty( self::$log ) ) {
					self::$log = new WC_Logger();
				}
				self::$log->add( 'afterpay', $message );
			}
		}

		/**
		 * Check if payment method is available for current customer.
		 */
		public function is_available() {
			
			// Only activate the payment gateway if the customers country is the same as the shop country ($this->afterpay_country)
			if ( WC()->customer->get_country() == true && WC()->customer->get_country() != $this->afterpay_country ) {
				return false;
			}
			
			

			// Check if payment method is configured
			$payment_method = $this->id;
			$payment_method_settings = get_option( 'woocommerce_' . $payment_method . '_settings' );
			if ( '' == $payment_method_settings['username'] || '' == $payment_method_settings['password'] || '' == $payment_method_settings['client_id'] ) {
				return false;
			}
			
			// Don't display part payment and Account for Norwegian customers
			if ( WC()->customer->get_country() == true && 'NO' == WC()->customer->get_country() && ( 'afterpay_part_payment' == $this->id || 'afterpay_account' == $this->id ) ) {
				return false;
			}

			// Check if PreCheckCustomer allows this payment method
			if ( WC()->session->get( 'afterpay_allowed_payment_methods' ) ) {
				switch ( $payment_method ) {
					case 'afterpay_invoice':
						$payment_method_name = 'Invoice';
						break;
					case 'afterpay_account':
						$payment_method_name = 'Account';
						break;
					case 'afterpay_part_payment':
						$payment_method_name = 'Installment';
						break;
				}

				$success = false;

				// Check PreCheckCustomer response for available payment methods
				foreach( WC()->session->get( 'afterpay_allowed_payment_methods' ) as $payment_option ) {
					if ( $payment_option->PaymentMethod == $payment_method_name ) {
						$success = true;
					}
				}
				
				if ( $success ) {
					return true;
				} else {
					return false;
				}
			}

			return true;
		}

		/**
		 * Initialise Gateway Settings Form Fields.
		 */
		public function init_form_fields() {
			$form_fields = array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'woocommerce-gateway-afterpay' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable ' . $this->method_title, 'woocommerce-gateway-afterpay' ),
					'default' => 'yes'
				),
				'title' => array(
					'title'       => __( 'Title', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-afterpay' ),
					'default'     => __( $this->method_title, 'woocommerce-gateway-afterpay' ),
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => __( 'Description', 'woocommerce-gateway-afterpay' ),
					'type'        => 'textarea',
					'desc_tip'    => true,
					'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-afterpay' ),
				),
				'username_se' => array(
					'title'       => __( 'AfterPay Username - Sweden', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'Please enter your AfterPay username for Sweden; this is needed in order to take payment.',
						'woocommerce-gateway-afterpay' ),
				),
				'password_se' => array(
					'title'       => __( 'AfterPay Password - Sweden', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'Please enter your AfterPay password for Sweden; this is needed in order to take payment.',
						'woocommerce-gateway-afterpay' ),
				),
				'client_id_se' => array(
					'title'       => __( 'AfterPay Client ID - Sweden', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'Please enter your AfterPay client ID for Sweden; this is needed in order to take payment.',
						'woocommerce-gateway-afterpay' ),
				),
				'username_no' => array(
					'title'       => __( 'AfterPay Username - Norway', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'Please enter your AfterPay username for Norway; this is needed in order to take payment.',
						'woocommerce-gateway-afterpay' ),
				),
				'password_no' => array(
					'title'       => __( 'AfterPay Password - Norway', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'Please enter your AfterPay password for Norway; this is needed in order to take payment.',
						'woocommerce-gateway-afterpay' ),
				),
				'client_id_no' => array(
					'title'       => __( 'AfterPay Client ID - Norway', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'Please enter your AfterPay client ID for Norway; this is needed in order to take payment.',
						'woocommerce-gateway-afterpay' ),
				),
			);
			
			// Invoice fee for AfterPay Invoice
			if ( 'afterpay_invoice' == $this->id ) {
				$form_fields['invoice_fee_id'] = array(
					'title'   => __( 'Invoice Fee', 'woocommerce-gateway-afterpay' ),
					'type'    => 'text',
					'description'   => __( 'Create a hidden (simple) product that acts as the invoice fee. Enter the ID number in this textfield. Leave blank to disable.', 'woocommerce-gateway-afterpay' ),
				);
			}

			// Logging, test mode and order management toggles for all payment methods
			// are in AfterPay Invoice settings
			if ( 'afterpay_invoice' == $this->id ) {
				$form_fields['order_management'] = array(
					'title'   => __( 'Enable Order Management', 'woocommerce-gateway-afterpay' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable AfterPay order capture on WooCommerce order completion and AfterPay order cancellation on WooCommerce order cancellation', 'woocommerce-gateway-afterpay' ),
					'default' => 'yes'
				);
				$form_fields['testmode'] = array(
					'title'       => __( 'AfterPay testmode', 'woocommerce-gateway-afterpay' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable AfterPay testmode', 'woocommerce-gateway-afterpay' ),
					'default'     => 'no',
				);
				$form_fields['debug'] = array(
					'title'       => __( 'Debug Log', 'woocommerce-gateway-afterpay' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable logging', 'woocommerce-gateway-afterpay' ),
					'default'     => 'no',
					'description' => sprintf( __( 'Log ' . $this->method_title . ' events in <code>%s</code>', 'woocommerce-gateway-afterpay' ), wc_get_log_file_path( 'afterpay-invoice' ) )
				);
			}

			$this->form_fields = $form_fields;
		}

		/**
		 * Process the payment and return the result.
		 *
		 * @param  int $order_id
		 *
		 * @return array
		 * @throws Exception
		 */
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );
			
			// If needed, run PreCheckCustomer
			if( ! WC()->session->get( 'afterpay_checkout_id' ) ) {
				$wc_afterpay_pre_check_customer = new WC_AfterPay_Pre_Check_Customer();
				$response = $wc_afterpay_pre_check_customer->pre_check_customer_request( $_POST['afterpay-pre-check-customer-number'], $this->id, $_POST['afterpay_customer_category'], $order->billing_country, $order );
				
				if ( is_wp_error( $response ) ) {
					//throw new Exception( $response->get_error_message() );
					wc_add_notice( __( $response->get_error_message(), 'woocommerce-gateway-afterpay' ), 'error' );
					return false;
				}
			}
			
			
			// If needed, run CreateContract
			if ( 'afterpay_account' == $this->id || 'afterpay_part_payment' == $this->id ) {
				$wc_afterpay_create_contract = new WC_AfterPay_Create_Contract( $order_id, $this->id );
				if ( is_wp_error( $wc_afterpay_create_contract->create_contract() ) ) {
					return false;
				}
			}


			// Use WC_AfterPay_Complete_Checkout class to process the payment
			// Must previously perform PreCheckCustomer
			// CheckoutID and CustomerNo are required and returned from PreCheckCustomer
			$wc_afterpay_complete_checkout = new WC_AfterPay_Complete_Checkout( $order_id, $this->id, $this->client_id, $this->username, $this->password );

			if ( ! is_wp_error( $wc_afterpay_complete_checkout->complete_checkout() ) ) {
				// Mark payment complete on success
				$order->payment_complete();

				// Store reservation ID as order note
				$order->add_order_note(
					sprintf( __( 'AfterPay reservation created, reservation ID: %s.', 'woocommerce-gateway-afterpay' ), get_post_meta( $order_id, '_afterpay_reservation_id', true ) )
				);

				// Remove cart
				WC()->cart->empty_cart();

				// Return thank you redirect
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order )
				);
			} else {
				$error_message = $wc_afterpay_complete_checkout->complete_checkout();
				throw new Exception( $error_message->get_error_message() );
			}
		}
		
		public function clear_afterpay_sessions() {
			WC()->session->__unset( 'afterpay_checkout_id' );
			WC()->session->__unset( 'afterpay_customer_no' );
			WC()->session->__unset( 'afterpay_personal_no' );
			WC()->session->__unset( 'afterpay_allowed_payment_methods' );
			WC()->session->__unset( 'afterpay_customer_details' );
			WC()->session->__unset( 'afterpay_cart_total' );
		}

		/**
		 * Process a refund if supported.
		 *
		 * @param  int    $order_id
		 * @param  float  $amount
		 * @param  string $reason
		 * @return bool True or false based on success, or a WP_Error object
		 */
		public function process_refund( $order_id, $amount = null, $reason = '' ) {
			$order = wc_get_order( $order_id );

			if ( is_wp_error( $this->can_refund_order( $order, $amount ) ) ) {
				return $this->can_refund_order( $order, $amount );
			}

			include_once( plugin_dir_path( __DIR__ ) . 'class-refund.php' );

			// Use WC_AfterPay_Complete_Checkout class to process the payment
			// Must previously perform PreCheckCustomer
			// CheckoutID and CustomerNo are required and returned from PreCheckCustomer
			$wc_afterpay_refund = new WC_AfterPay_Refund( $order_id, $this->id );

			$result = $wc_afterpay_refund->refund_invoice( $order_id, $amount, $reason );

			if ( is_wp_error( $result ) ) {
				$this->log( 'Refund Failed: ' . $result->get_error_message() );
				return new WP_Error( 'error', $result->get_error_message() );
			}
			
			return true;
		}

		/**
		 * Can the order be refunded via AfterPay AfterPay?
		 * @param  WC_Order $order
		 * @return bool
		 */
		public function can_refund_order( $order, $amount ) {
			// Check if there's a transaction ID (invoice number)
			if ( ! $order->get_transaction_id() ) {
				$this->log( 'Refund failed: No AfterPay invoice number ID.' );
				return new WP_Error( 'error', __( 'Refund failed: No AfterPay invoice number ID.', 'woocommerce' ) );
			}

			// At the moment, only full refund is possible, because we can't send refunded order lines to AfterPay
			if ( $amount != $order->get_total() ) {
				$this->log( 'Refund failed: Only full order amount can be refunded via AfterPay.' );
				return new WP_Error( 'error', __( 'Refund failed: Only full order amount can be refunded via AfterPay.',
					'woocommerce' ) );
			}

			return true;
		}

	}
}