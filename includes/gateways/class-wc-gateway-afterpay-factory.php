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
		
		/**
		 * Check if payment method is available for current customer
		 */
		public function is_available() {
			// Check if Sweden is selected
			if ( WC()->customer->get_country() == true && 'SE' != WC()->customer->get_country() ) {
				return false;
			}

			// Check if payment method is configured
			$payment_method = $this->id;
			$payment_method_settings = get_option( 'woocommerce_' . $payment_method . '_settings' );
			if ( '' == $payment_method_settings['username'] || '' == $payment_method_settings['password'] || '' == $payment_method_settings['client_id'] ) {
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
			$this->form_fields = array(
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
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-afterpay' ),
				),
				'username' => array(
					'title'       => __( 'AfterPay Username', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'Please enter your AfterPay username; this is needed in order to take payment.',
						'woocommerce-gateway-afterpay' ),
				),
				'password' => array(
					'title'       => __( 'AfterPay Password', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'Please enter your AfterPay password; this is needed in order to take payment.',
						'woocommerce-gateway-afterpay' ),
				),
				'client_id' => array(
					'title'       => __( 'AfterPay Client ID', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'Please enter your AfterPay client ID; this is needed in order to take payment.',
						'woocommerce-gateway-afterpay' ),
				),
				'order_management' => array(
					'title'   => __( 'Enable Order Management', 'woocommerce-gateway-afterpay' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable AfterPay order capture on WooCommerce order completion and AfterPay order cancellation on WooCommerce order cancellation', 'woocommerce-gateway-afterpay' ),
					'default' => 'yes'
				),
				'testmode' => array(
					'title'       => __( 'AfterPay testmode', 'woocommerce-gateway-afterpay' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable AfterPay testmode', 'woocommerce-gateway-afterpay' ),
					'default'     => 'no',
				),
				'debug' => array(
					'title'       => __( 'Debug Log', 'woocommerce-gateway-afterpay' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable logging', 'woocommerce-gateway-afterpay' ),
					'default'     => 'no',
					'description' => sprintf( __( 'Log ' . $this->method_title . ' events in <code>%s</code>', 'woocommerce-gateway-afterpay' ), wc_get_log_file_path( 'afterpay-invoice' ) )
				),
			);
		}

		/**
		 * Process the payment and return the result.
		 */
		public function payment_fields() {
			WC_AfterPay_Pre_Check_Customer::display_pre_check_form();
		}

		/**
		 * Process the payment and return the result.
		 *
		 * @param  int $order_id
		 *
		 * @return array
		 */
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

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
			$wc_afterpay_complete_checkout = new WC_AfterPay_Complete_Checkout( $order_id, $this->id );

			if ( $wc_afterpay_complete_checkout->complete_checkout() ) {
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
			}
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
				return new WP_Error( 'error', __( 'Refund failed: No AfterPay invoice number ID.', 'woocommerce' ) );
			}

			// At the moment, only full refund is possible, because we can't send refunded order lines to AfterPay
			if ( $amount != $order->get_total() ) {
				return new WP_Error( 'error', __( 'Refund failed: Only full order amount can be refunded.',
					'woocommerce' ) );
			}

			return true;
		}

	}
}