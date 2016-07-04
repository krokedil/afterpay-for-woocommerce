<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds Invoice Fee to cart if customer is paying with AfterPay Invoice and merchant have added an invoice fee.
 *
 * @class    WC_AfterPay_Invoice_Fee
 * @version  1.0.3
 * @package  WC_Gateway_AfterPay/Classes
 * @category Class
 * @author   Krokedil
 */
class WC_AfterPay_Invoice_Fee {

	/** @var bool */
	private $testmode = false;

	/**
	 * WC_AfterPay_Invoice_Fee constructor.
	 */
	public function __construct() {
		$afterpay_settings = get_option( 'woocommerce_afterpay_invoice_settings' );
		$this->testmode    = 'yes' == $afterpay_settings['testmode'] ? true : false;
		$this->invoice_fee_id = ( isset( $afterpay_settings['invoice_fee_id'] ) ) ? $afterpay_settings['invoice_fee_id'] : '';
		
		// Invoice fee
		if ( '' == $this->invoice_fee_id ) {
			$this->invoice_fee_id = 0;
		}
		
		// Add Invoice fee
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'calculate_totals' ), 10, 1 );
	}

	
	/**
	 * Calculate totals on checkout form.
	 **/
	public function calculate_totals( $cart ) {
		
		if ( (is_checkout() || defined( 'WOOCOMMERCE_CHECKOUT' ) ) && $this->invoice_fee_id > 0 ) {

			$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
			$current_gateway    = WC()->session->chosen_payment_method;

			if ( ! empty( $available_gateways ) ) {
				// Chosen Method
				if ( isset( $current_gateway ) && isset( $available_gateways[ $current_gateway ] ) ) {
					$current_gateway = $available_gateways[ $current_gateway ];
				} elseif ( isset( $available_gateways[ get_option( 'woocommerce_default_gateway' ) ] ) ) {
					$current_gateway = $available_gateways[ get_option( 'woocommerce_default_gateway' ) ];
				} else {
					$current_gateway = current( $available_gateways );

				}

			}
			
			if ( 'afterpay_invoice' == $current_gateway->id ) {
				
				$current_gateway_id = $current_gateway->id;
				$product = get_product( $this->invoice_fee_id );

				if ( $product ) {
	
					// Is this a taxable product?
					if ( $product->is_taxable() ) {
						$product_tax = true;
					} else {
						$product_tax = false;
					}
					$cart->add_fee( $product->get_title(), $product->get_price_excluding_tax(), $product_tax, $product->get_tax_class() );
	
				}

			}
		} // End if is checkout
		
	}


}

$wc_afterpay_invoice_fee = new WC_AfterPay_Invoice_Fee();