<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action( 'plugins_loaded', 'init_wc_gateway_arvato_factory_class' );

/**
 * Initialize Arvato Invoice payment gateway
 *
 * @wp_hook plugins_loaded
 */
function init_wc_gateway_arvato_factory_class() {
	/**
	 * Arvato Payment Gateway Factory.
	 *
	 * Parent class for all Arvato payment methods.
	 *
	 * @class       WC_Gateway_Arvato_Factory
	 * @extends     WC_Payment_Gateway
	 * @version     0.1
	 * @author      Krokedil
	 */
	class WC_Gateway_Arvato_Factory extends WC_Payment_Gateway {

		/**
		 * Initialise Gateway Settings Form Fields.
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'woocommerce-gateway-arvato' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable ' . $this->method_title, 'woocommerce-gateway-arvato' ),
					'default' => 'yes'
				),
				'title' => array(
					'title'       => __( 'Title', 'woocommerce-gateway-arvato' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-arvato' ),
					'default'     => __( $this->method_title, 'woocommerce-gateway-arvato' ),
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => __( 'Description', 'woocommerce-gateway-arvato' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-arvato' ),
				),
				'username' => array(
					'title'       => __( 'Arvato Username', 'woocommerce-gateway-arvato' ),
					'type'        => 'text',
					'description' => __( 'Please enter your Arvato username; this is needed in order to take payment.',
						'woocommerce-gateway-arvato' ),
				),
				'password' => array(
					'title'       => __( 'Arvato Password', 'woocommerce-gateway-arvato' ),
					'type'        => 'text',
					'description' => __( 'Please enter your Arvato password; this is needed in order to take payment.',
						'woocommerce-gateway-arvato' ),
				),
				'client_id' => array(
					'title'       => __( 'Arvato Client ID', 'woocommerce-gateway-arvato' ),
					'type'        => 'text',
					'description' => __( 'Please enter your Arvato client ID; this is needed in order to take payment.',
						'woocommerce-gateway-arvato' ),
				),
				'order_management' => array(
					'title'   => __( 'Enable Order Management', 'woocommerce-gateway-arvato' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Arvato order capture on WooCommerce order completion and Arvato order refund on 
		WooCommerce order refund',
						'woocommerce-gateway-arvato' ),
					'default' => 'yes'
				),
				'testmode' => array(
					'title'       => __( 'Arvato testmode', 'woocommerce-gateway-arvato' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable Arvato testmode', 'woocommerce-gateway-arvato' ),
					'default'     => 'no',
				),
				'debug' => array(
					'title'       => __( 'Debug Log', 'woocommerce-gateway-arvato' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable logging', 'woocommerce-gateway-arvato' ),
					'default'     => 'no',
					'description' => sprintf( __( 'Log ' . $this->method_title . ' events in <code>%s</code>', 'woocommerce-gateway-arvato' ), wc_get_log_file_path( 'arvato-invoice' ) )
				),
			);
		}

		/**
		 * Process the payment and return the result.
		 *
		 * @param  int $order_id
		 *
		 * @return array
		 */
		public function process_payment( $order_id ) {
			// Use WC_Arvato_Complete_Checkout class to process the payment
			// Must previously perform PreCheckCustomer
			// CheckoutID and CustomerNo are required and returned from PreCheckCustomer
			$wc_arvato_complete_checkout = new WC_Arvato_Complete_Checkout( $order_id, $this->id );

			if ( $wc_arvato_complete_checkout->complete_checkout() ) {
				$order = wc_get_order( $order_id );

				// Mark payment complete on success
				$order->payment_complete();

				// Store reservation ID as order note
				$order->add_order_note(
					sprintf( __( 'Arvato reservation created, reservation ID: %s.', 'woocommerce-gateway-arvato' ), get_post_meta( $order_id, '_arvato_reservation_id', true ) )
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

	}
}