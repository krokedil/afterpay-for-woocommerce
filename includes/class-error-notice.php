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
					$error_message = __( 'Not set.', 'woocommerce-gateway-afterpay' );
					break;
				case '1' :
					$error_message = __( 'Ok.', 'woocommerce-gateway-afterpay' );
					break;
				case '2' :
					$error_message = __( 'Illegal values.', 'woocommerce-gateway-afterpay' );
					break;
				case '3' :
					$error_message = __( 'Limit exceeded.', 'woocommerce-gateway-afterpay' );
					break;
				case '4' :
					$error_message = __( 'Order already exists.', 'woocommerce-gateway-afterpay' );
					break;
				case '5' :
					$error_message = __( 'Amount does not match PreCheck.', 'woocommerce-gateway-afterpay' );
					break;
				case '6' :
					$error_message = __( 'Invalid Checkout ID.', 'woocommerce-gateway-afterpay' );
					break;
				case '7' :
					$error_message = __( 'System error.', 'woocommerce-gateway-afterpay' );
					break;
				case '8' :
					$error_message = __( 'Invalid Contract ID.', 'woocommerce-gateway-afterpay' );
					break;
				case '9' :
					$error_message = __( 'Invalid Client ID.', 'woocommerce-gateway-afterpay' );
					break;
				case '10' :
					$error_message = __( 'Customer related risk.', 'woocommerce-gateway-afterpay' );
					break;
				case '11' :
					$error_message = __( 'Other risk. Please select another payment method.', 'woocommerce-gateway-afterpay' );
					break;
				case '12' :
					$error_message = __( 'Invalid payment method.', 'woocommerce-gateway-afterpay' );
					break;
				case '13' :
					$error_message = __( 'Invalid Order No.', 'woocommerce-gateway-afterpay' );
					break;
				case '14' :
					$error_message = __( 'Invalid Customer No.', 'woocommerce-gateway-afterpay' );
					break;
				case '15' :
					$error_message = __( 'Invalid currency code.', 'woocommerce-gateway-afterpay' );
					break;
				case '16' :
					$error_message = __( 'Contract duration not allowed.', 'woocommerce-gateway-afterpay' );
					break;
				case '17' :
					$error_message = __( 'Monthly installment below minimum amount.', 'woocommerce-gateway-afterpay' );
					break;
				default:
					// No action
					$error_message = __( 'Unknown response code.', 'woocommerce-gateway-afterpay' );
					break;
			}
			
		} else {
			
			// PreCheckCustomer request
			switch ( $error_code ) {
				case '0' :
					$error_message = __( 'Not set.', 'woocommerce-gateway-afterpay' );
					break;
				case '1' :
					$error_message = __( 'Ok.', 'woocommerce-gateway-afterpay' );
					break;
				case '2' :
					$error_message = __( 'Order already exists.', 'woocommerce-gateway-afterpay' );
					break;
				case '3' :
					$error_message = __( 'Illegal values.', 'woocommerce-gateway-afterpay' );
					break;
				case '4' :
					$error_message = __( 'Customer related risk.', 'woocommerce-gateway-afterpay' );
					break;
				case '5' :
					$error_message = __( 'Delivery related risk.', 'woocommerce-gateway-afterpay' );
					break;
				case '6' :
					$error_message = __( 'Address related risk.', 'woocommerce-gateway-afterpay' );
					break;
				case '7' :
					$error_message = __( 'Order related risk.', 'woocommerce-gateway-afterpay' );
					break;
				case '8' :
					$error_message = __( 'Profile related risk.', 'woocommerce-gateway-afterpay' );
					break;
				case '9' :
					$error_message = __( 'Limit exceed risk.', 'woocommerce-gateway-afterpay' );
					break;
				case '10' :
					$error_message = __( 'Other risk. Please select another payment method.', 'woocommerce-gateway-afterpay' );
					break;
				case '11' :
					$error_message = __( 'System error.', 'woocommerce-gateway-afterpay' );
					break;
				case '12' :
					$error_message = __( 'Invalid Client ID.', 'woocommerce-gateway-afterpay' );
					break;
				case '13' :
					$error_message = __( 'Validation error – wrong/missing input data.', 'woocommerce-gateway-afterpay' );
					break;
				default:
					// No action
					$error_message = __( 'Unknown response code', 'woocommerce-gateway-afterpay' );
					break;
			}
		}
		
		return $error_message;
		
	}


}