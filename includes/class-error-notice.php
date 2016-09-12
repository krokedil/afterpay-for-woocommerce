<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Returns error messages depending on 
 *
 * @class    WC_AfterPay_Invoice_Fee
 * @version  1.0.3
 * @package  WC_Gateway_AfterPay/Classes
 * @category Class
 * @author   Krokedil
 */
class WC_AfterPay_Error_Notice {
	
	
	public static function get_error_message( $error_code, $request_type = false ) {
		$error_message = '';
		
		if( 'complete_checkout' == $request_type ) {
			
			// CompleteCheckout request
			switch ( $error_code ) {
				case '0' :
					$error_message = __( 'Please try again', 'woocommerce-gateway-afterpay' );
					break;
				case '1' :
					$error_message = __( 'Please try again', 'woocommerce-gateway-afterpay' );
					break;
				case '2' :
					$error_message = __( 'Please verify ', 'woocommerce-gateway-afterpay' );
					break;
				case '3' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '4' :
					$error_message = __( 'Please verify your information and try again ', 'woocommerce-gateway-afterpay' );
					break;
				case '5' :
					$error_message = __( 'Please verify your information and try again ', 'woocommerce-gateway-afterpay' );
					break;
				case '6' :
					$error_message = __( 'Please verify your information and try again ', 'woocommerce-gateway-afterpay' );
					break;
				case '7' :
					$error_message = __( 'Please verify your information and try again ', 'woocommerce-gateway-afterpay' );
					break;
				case '8' :
					$error_message = __( 'Please verify your information and try again ', 'woocommerce-gateway-afterpay' );
					break;
				case '9' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '10' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '11' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '12' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '13' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '14' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '15' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '16' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '17' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				default:
					// No action
					$error_message = __( 'Please try again', 'woocommerce-gateway-afterpay' );
					break;
			}
			
		} else {
			
			// PreCheckCustomer request
			switch ( $error_code ) {
				case '0' :
					$error_message = __( 'Please try again', 'woocommerce-gateway-afterpay' );
					break;
				case '1' :
					$error_message = __( 'Please try again', 'woocommerce-gateway-afterpay' );
					break;
				case '2' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '3' :
					$error_message = __( 'Please verify your information and try again ', 'woocommerce-gateway-afterpay' );
					break;
				case '4' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '5' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '6' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '7' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '8' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '9' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '10' :
					$error_message = __( 'Please select a different payment method', 'woocommerce-gateway-afterpay' );
					break;
				case '11' :
					$error_message = __( 'Please verify your information and try again ', 'woocommerce-gateway-afterpay' );
					break;
				case '12' :
					$error_message = __( 'Please verify your information and try again ', 'woocommerce-gateway-afterpay' );
					break;
				case '13' :
					$error_message = __( 'Please verify your information and try again ', 'woocommerce-gateway-afterpay' );
					break;
				default:
					// No action
					$error_message = __( 'Please verify your information and try again ', 'woocommerce-gateway-afterpay' );
					break;
			}
		}
		
		return $error_message;
		
	}


}