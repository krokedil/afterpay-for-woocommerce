<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action( 'plugins_loaded', 'init_wc_gateway_arvato_account_class' );
add_filter( 'woocommerce_payment_gateways', 'add_arvato_account_method' );

/**
 * Initialize Arvato Account payment gateway
 *
 * @wp_hook plugins_loaded
 */
function init_wc_gateway_arvato_account_class() {
	/**
	 * Arvato Account Payment Gateway.
	 *
	 * Provides Arvato Account Payment Gateway for WooCommerce.
	 *
	 * @class       WC_Gateway_Arvato_Account
	 * @extends     WC_Payment_Gateway
	 * @version     0.1
	 * @author      Krokedil
	 */
	class WC_Gateway_Arvato_Account extends WC_Payment_Gateway {
		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
			$this->id                 = 'arvato_account';
			$this->icon               = apply_filters( 'woocommerce_arvato_account_icon', '' );
			$this->has_fields         = false;
			$this->method_title       = __( 'Arvato Account', 'woocommerce-gateway-arvato' );
			$this->method_description = __( 'Allows payments through Arvato Account.', 'woocommerce-gateway-arvato' );

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );

			// Actions

			// Filters
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
function add_arvato_account_method( $methods ) {
	$methods[] = 'WC_Gateway_Arvato_Account';
	return $methods;
}