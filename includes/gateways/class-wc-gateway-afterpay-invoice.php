<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action( 'plugins_loaded', 'init_wc_gateway_afterpay_invoice_class' );
add_filter( 'woocommerce_payment_gateways', 'add_afterpay_invoice_method' );

/**
 * Initialize AfterPay Invoice payment gateway
 *
 * @wp_hook plugins_loaded
 */
function init_wc_gateway_afterpay_invoice_class() {
	/**
	 * AfterPay Invoice Payment Gateway.
	 *
	 * Provides AfterPay Invoice Payment Gateway for WooCommerce.
	 *
	 * @class       WC_Gateway_AfterPay_Invoice
	 * @extends     WC_Gateway_AfterPay_Factory
	 * @version     0.1
	 * @author      Krokedil
	 */
	class WC_Gateway_AfterPay_Invoice extends WC_Gateway_AfterPay_Factory {

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
			$this->id                 = 'afterpay_invoice';
			$this->method_title       = __( 'AfterPay Invoice', 'woocommerce-gateway-afterpay' );

			$this->icon               = apply_filters( 'woocommerce_afterpay_invoice_icon', AFTERPAY_URL . '/assets/images/logo.png' );
			$this->has_fields         = true;
			$this->method_description = __( 'Allows payments through ' . $this->method_title . '.', 'woocommerce-gateway-afterpay' );

			// Define user set variables
			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->client_id   = $this->get_option( 'client_id' );
			$this->username    = $this->get_option( 'username' );
			$this->password    = $this->get_option( 'password' );
			$this->debug       = $this->get_option( 'debug' );
			
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			$this->supports = array(
				'products',
				'refunds'
			);

			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
				$this,
				'process_admin_options'
			) );
		}
	}

}

/**
 * Add AfterPay payment gateway
 *
 * @wp_hook woocommerce_payment_gateways
 *
 * @param  $methods Array All registered payment methods
 *
 * @return $methods Array All registered payment methods
 */
function add_afterpay_invoice_method( $methods ) {
	$methods[] = 'WC_Gateway_AfterPay_Invoice';

	return $methods;
}