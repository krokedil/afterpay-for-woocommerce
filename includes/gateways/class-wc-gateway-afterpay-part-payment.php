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
			$this->title       		= $this->get_option( 'title' );
			$this->description 		= $this->get_option( 'description' );
			$this->client_id_se   	= $this->get_option( 'client_id_se' );
			$this->username_se    	= $this->get_option( 'username_se' );
			$this->password_se    	= $this->get_option( 'password_se' );
			$this->client_id_no   	= $this->get_option( 'client_id_no' );
			$this->username_no    	= $this->get_option( 'username_no' );
			$this->password_no    	= $this->get_option( 'password_no' );
			$this->debug       		= $this->get_option( 'debug' );
			$this->api_key       	= $this->get_option( 'api_key' );
			$this->testmode       	= $this->get_option( 'testmode' );
			
			// Set country and merchant credentials based on currency.
			switch ( get_woocommerce_currency() ) {
				case 'NOK' :
					$this->afterpay_country 	= 'NO';
					$this->client_id  			= $this->client_id_no;
					$this->username     		= $this->username_no;
					$this->password     		= $this->password_no;
					break;
				case 'SEK' :
					$this->afterpay_country		= 'SE';
					$this->client_id  			= $this->client_id_se;
					$this->username     		= $this->username_se;
					$this->password     		= $this->password_se;
					break;
				default:
					$this->afterpay_country 	= '';
					$this->client_id  			= '';
					$this->username     		= '';
					$this->password     		= '';
			}
			
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
			add_action( 'woocommerce_thankyou', array( 
				$this, 
				'clear_afterpay_sessions' 
			) );
			add_action( 'woocommerce_checkout_process', array( 
				$this, 
				'process_checkout_fields' 
			) );
		}

		/**
		 * Display payment fields for Part Payment
		 */
		public function payment_fields() {
			parent::payment_fields();

			$payment_options = WC()->session->get( 'afterpay_allowed_payment_methods' );
			$installment_plans = 0;
			foreach($payment_options as $payment_option) {

				//@TODO - Check with AfterPay why Installment seem to be returned as Account
				if('Account' == $payment_option->type && 1 !== $payment_option->account->profileNo ) {
					$installment_plans++;
				}
			}

			if ( $installment_plans >= 1 ) {
				echo '<p>' . __( 'Please select a payment plan:', 'woocommerce-gateway-afterpay' ) . '</p>';
				foreach( $payment_options as $key => $installment_plan ) {


					if( 'Account' == $installment_plan->type && 1 !== $installment_plan->account->profileNo ) {

						// Sort payment plans before displaying them
						/*$payment_plans = $payment_option->AllowedInstallmentPlans->AllowedInstallmentPlan;
						usort(
							$payment_plans,
							array( $this, 'sort_payment_plans' )
						);
						*/
						//foreach( $payment_plans as $key => $installment_plan ) {
						$label = sprintf(
							'%1$s x %2$s %3$s per month',
							$installment_plan->account->numberOfPayments,
							$installment_plan->account->monthlyPaymentAmount,
							get_woocommerce_currency()
						);

						echo '<input type="radio" name="afterpay_installment_plan" id="afterpay-installment-plan-' . $installment_plan->account->profileNo . '" value="' . $installment_plan->account->profileNo . '" ' . checked( $key, 0, false ) . ' />';
						echo '<label for="afterpay-installment-plan-' . $installment_plan->account->profileNo . '"> ' . $label . '</label>';
						echo '<br>';
						//}

					}

				}

				$example = __( 'Example: 10000 kr over 12 months, effective interest rate 16.82%. Total credit amount 1682SEK, total repayment amount 11682 SEK.', 'woocommerce-gateway-afterpay'	);
				echo '<p style="margin: 1.5em 0 0; font-size: 0.8em;">' . $example . '</p>';
			}
		}

		/**
		 * Sort payment plans before displaying them, shortest to longest
		 *
		 * @param $plana
		 * @param $planb
		 *
		 * @return int
		 */
		public function sort_payment_plans( $plana, $planb ) {
			return $plana->NumberOfInstallments > $planb->NumberOfInstallments;
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