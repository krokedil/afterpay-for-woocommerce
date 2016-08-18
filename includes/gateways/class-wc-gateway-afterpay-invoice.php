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
			$this->id           = 'afterpay_invoice';
			$this->method_title = __( 'AfterPay Invoice', 'woocommerce-gateway-afterpay' );

			$this->icon               = apply_filters( 'woocommerce_afterpay_invoice_icon', AFTERPAY_URL . '/assets/images/logo.png' );
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
			$this->invoice_fee_id	= $this->get_option( 'invoice_fee_id' );
			$this->debug       		= $this->get_option( 'debug' );
			
			// Invoice fee
			if ( '' == $this->invoice_fee_id ) {
				$this->invoice_fee_id = 0;
			}
			
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
		function payment_fields() {
			if ( $this->description ) {
				echo wpautop( wptexturize( $this->description ) );
				
				echo $this->get_afterpay_info();
			}
		}
		
		/**
		 * Helper function for displaying the AfterPay Invoice terms
		 */
		public function get_afterpay_info() {

			switch ( get_woocommerce_currency() ) {
				case 'NOK':
					$terms_url   			= 'https://www.arvato.com/content/dam/arvato/documents/norway-ecomm-terms-and-conditions/Vilk%C3%A5r%20for%20AfterPay%20Faktura.pdf';
					$terms_title 			= 'AfterPay Faktura';
					if( 0 == $this->get_invoice_fee_price() ) {
						$terms_content 		= '<p>Vi tilbyr AfterPay Faktura i samarbeid med arvato Finance AS. Betalingsfristen er 14 dager. Hvis du velger å betale med AfterPay faktura vil det ikke påløpe gebyr.</p>';
					} else {
					 	$terms_content 		.= '<p>Vi tilbyr AfterPay Faktura i samarbeid med arvato Finance AS. Betalingsfristen er 14 dager. Hvis du velger å betale med AfterPay faktura vil det påløpe et gebyr på NOK ' . $this->get_invoice_fee_price() . '.</p>';
					}
					$terms_content 			.= '<p>For å betale med faktura må du ha fylt 18 år, være folkeregistrert i Norge samt bli godkjent i kredittvurderingen som gjennomføres ved kjøpet. På bakgrunn av kredittsjekken vil det genereres gjenpartsbrev. Faktura sendes på e-post. Ved forsinket betaling vil det bli sendt inkassovarsel og lovbestemte gebyrer kan påløpe. Dersom betaling fortsatt uteblir vil fakturaen bli sendt til inkasso og ytterligere omkostninger vil påløpe.</p>';
					$terms_readmore 		= 'Les mer om AfterPay <a href="' . $terms_url . '" target="_blank">her</a>.';
					$short_readmore 		= 'Les mer her';
					break;
				case 'SEK' :
					$terms_url   			= 'http://www.afterpay.se/sv/terms-and-conditions';
					$terms_title 			= 'AfterPay Faktura';
					if( 0 == $this->get_invoice_fee_price() ) {
						$terms_content 		= '<p>Vi erbjuder betalning med faktura i samarbete med AfterPay. Betalningsvillkor är 14 dagar. Ingen fakturaavgift tillkommer.</p>';
					} else {
					 	$terms_content 		.= '<p>Vi erbjuder betalning med faktura i samarbete med AfterPay. Betalningsvillkor är 14 dagar och en fakturaavgift om ' . $this->get_invoice_fee_price() . ' kr tillkommer.</p>';
					}
					$terms_content 			.= '<p>För att kunna beställa mot faktura måste Du ha fyllt 18 år och vara folkbokförd i Sverige samt godkännas i den kreditprövning som genomförs vid köpet. Fakturan skickas via e-post. Vid försenad betalning skickar AfterPay en betalningspåminnelse varvid lagstadgad påminnelseavgift. Ni debiteras även dröjsmålsränta med 2% per månad från fakturans förfallodag. Vid utebliven betalning lämnas ärendet till inkasso varvid lagstadgad inkassoavgift tillkommer.</p>';
					$terms_readmore 		= 'Läs mer om AfterPay <a href="' . $terms_url . '" target="_blank">här</a>.';
					$short_readmore 		= 'Läs mer här';
					break;
				default:
					$terms_url   			= 'https://www.arvato.com/content/dam/arvato/documents/norway-ecomm-terms-and-conditions/Vilk%C3%A5r%20for%20AfterPay%20Faktura.pdf';
					$terms_title 			= 'AfterPay Faktura';
					$terms_content 			= '<p>Vi tilbyr AfterPay Faktura i samarbeid med arvato Finance AS. Betalingsfristen er 14 dager. Hvis du velger å betale med AfterPay faktura vil det påløpe et gebyr på ' . get_woocommerce_currency() . ' 0.</p>';
					$terms_content 			.= '<p>For å betale med faktura må du ha fylt 18 år, være folkeregistrert i Norge samt bli godkjent i kredittvurderingen som gjennomføres ved kjøpet. På bakgrunn av kredittsjekken vil det genereres gjenpartsbrev. Faktura sendes på e-post. Ved forsinket betaling vil det bli sendt inkassovarsel og lovbestemte gebyrer kan påløpe. Dersom betaling fortsatt uteblir vil fakturaen bli sendt til inkasso og ytterligere omkostninger vil påløpe.</p>';
					$terms_readmore 		= 'Läs mer om AfterPay <a href="' . $terms_url . '" target="_blank">här</a>.';
					$short_readmore 		= 'Läs mer här';
			}
		
			add_thickbox();
			$afterpay_info = '<div id="afterpay-terms-content" style="display:none;">';
			$afterpay_info .= '<h3>' . $terms_title . '</h3>';
			$afterpay_info .= $terms_content;
			$afterpay_info .= '<p>' . $terms_readmore . '</p>';
			$afterpay_info .='</div>';
			$afterpay_info .='<a href="#TB_inline?width=600&height=550&inlineId=afterpay-terms-content" class="thickbox">' . $short_readmore . '</a>';
			return $afterpay_info;
		}
		
		// Helper function - get Invoice fee price
		public function get_invoice_fee_price() {
			if ( $this->invoice_fee_id > 0 ) {
				$product = wc_get_product( $this->invoice_fee_id );
				if ( $product ) {
					return $product->get_price();
				} else {
					return 0;
				}
			} else {
				return 0;
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
function add_afterpay_invoice_method( $methods ) {
	$methods[] = 'WC_Gateway_AfterPay_Invoice';

	return $methods;
}