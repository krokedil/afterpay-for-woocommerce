<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action( 'plugins_loaded', 'init_wc_gateway_afterpay_part_payment_class' );
add_filter( 'woocommerce_payment_gateways', 'add_afterpay_part_payment_method' );

/**
 * Initialize AfterPay Part_Payment payment gateway
 *
 * @wp_hook plugins_loaded
 */
function init_wc_gateway_afterpay_part_payment_class() {
	/**
	 * AfterPay Part_Payment Payment Gateway.
	 *
	 * Provides AfterPay Part_Payment Payment Gateway for WooCommerce.
	 *
	 * @class       WC_Gateway_AfterPay_Part_Payment
	 * @extends     WC_Gateway_AfterPay_Factory
	 * @version     0.1
	 * @author      Krokedil
	 */
	class WC_Gateway_AfterPay_Part_Payment extends WC_Gateway_AfterPay_Factory {

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
			$this->id                 = 'afterpay_part_payment';
			$this->method_title       = __( 'AfterPay Part Payment', 'woocommerce-gateway-afterpay' );

			$this->icon               = apply_filters( 'woocommerce_afterpay_part_payment_icon', AFTERPAY_URL . '/assets/images/logo.png' );
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

		/**
		 * Display payment fields for Part Payment
		 */
		public function payment_fields() {
			parent::payment_fields();
			if ( WC()->session->get( 'afterpay_allowed_payment_methods' ) ) {
				foreach( WC()->session->get( 'afterpay_allowed_payment_methods' ) as $payment_option ) {
					if ( $payment_option->PaymentMethod == 'Installment' ) {
						if ( sizeof( $payment_option->AllowedInstallmentPlans->AllowedInstallmentPlan ) >= 1 ) {
							echo '<select id="afterpay-installment-plans" name="afterpay_installment_plan">';
							foreach( $payment_option->AllowedInstallmentPlans->AllowedInstallmentPlan as $installment_plan ) {
								echo '<option value="' . $installment_plan->AccountProfileNumber . '">';
								echo $installment_plan->NumberOfInstallments . ' * ' . $installment_plan->InstallmentAmount;
								echo '</option>';
							}
							echo '</select>';
						}
					}
				}
			}
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
function add_afterpay_part_payment_method( $methods ) {
	$methods[] = 'WC_Gateway_AfterPay_Part_Payment';

	return $methods;
}