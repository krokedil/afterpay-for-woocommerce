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

		/** @var WC_Logger Logger instance */
		public static $log = false;

		/**
		 * Logging method.
		 *
		 * @param string $message
		 */
		public static function log( $message ) {
			$afterpay_settings = get_option( 'woocommerce_afterpay_invoice_settings' );
			if ( $afterpay_settings['debug'] == 'yes' ) {
				if ( empty( self::$log ) ) {
					self::$log = new WC_Logger();
				}
				self::$log->add( 'afterpay', $message );
			}
		}

		/**
		 * Check if payment method is available for current customer.
		 */
		public function is_available() {
			
			if( WC()->customer ) {
				// Only activate the payment gateway if the customers country is the same as the shop country ($this->afterpay_country)
				if ( WC()->customer->get_country() == true && WC()->customer->get_country() != $this->afterpay_country ) {
					return false;
				}
				
				// Check if payment method is configured
				$payment_method 			= $this->id;
				$country 					= strtolower(WC()->customer->get_country());
				$payment_method_settings 	= get_option( 'woocommerce_' . $payment_method . '_settings' );
				
				if ( 'yes' !== $payment_method_settings['enabled'] ) {
					return false;
				}
				
				if ( '' == $payment_method_settings['username_' . $country] || '' == $payment_method_settings['password_' . $country] || '' == $payment_method_settings['client_id_' . $country] ) {
					return false;
				}
				
				// Don't display part payment and Account for Norwegian customers
				if ( WC()->customer->get_country() == true && 'NO' == WC()->customer->get_country() && ( 'afterpay_part_payment' == $this->id || 'afterpay_account' == $this->id ) ) {
					return false;
				}
			}
			
			if( WC()->session ) {
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
			}
			return true;
		}

		/**
		 * Initialise Gateway Settings Form Fields.
		 */
		public function init_form_fields() {
			$form_fields = array(
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
					'type'        => 'textarea',
					'desc_tip'    => true,
					'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-afterpay' ),
				),
				'username_se' => array(
					'title'       => __( 'Arvato Username - Sweden', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'Please enter your Arvato username for Sweden; this is needed in order to take payment.',
						'woocommerce-gateway-afterpay' ),
				),
				'password_se' => array(
					'title'       => __( 'Arvato Password - Sweden', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'Please enter your Arvato password for Sweden; this is needed in order to take payment.',
						'woocommerce-gateway-afterpay' ),
				),
				'client_id_se' => array(
					'title'       => __( 'Arvato Client ID - Sweden', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'Please enter your Arvato client ID for Sweden; this is needed in order to take payment.',
						'woocommerce-gateway-afterpay' ),
				),
				'username_no' => array(
					'title'       => __( 'Arvato Username - Norway', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'Please enter your Arvato username for Norway; this is needed in order to take payment.',
						'woocommerce-gateway-afterpay' ),
				),
				'password_no' => array(
					'title'       => __( 'Arvato Password - Norway', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'Please enter your Arvato password for Norway; this is needed in order to take payment.',
						'woocommerce-gateway-afterpay' ),
				),
				'client_id_no' => array(
					'title'       => __( 'Arvato Client ID - Norway', 'woocommerce-gateway-afterpay' ),
					'type'        => 'text',
					'description' => __( 'Please enter your Arvato client ID for Norway; this is needed in order to take payment.',
						'woocommerce-gateway-afterpay' ),
				),
			);
			
			// Invoice fee for AfterPay Invoice
			if ( 'afterpay_invoice' == $this->id ) {
				$form_fields['invoice_fee_id'] = array(
					'title'   => __( 'Invoice Fee', 'woocommerce-gateway-afterpay' ),
					'type'    => 'text',
					'description'   => __( 'Create a hidden (simple) product that acts as the invoice fee. Enter the ID number in this textfield. Leave blank to disable.', 'woocommerce-gateway-afterpay' ),
				);
			}

			// Logging, test mode and order management toggles for all payment methods
			// are in AfterPay Invoice settings
			if ( 'afterpay_invoice' == $this->id ) {
				$form_fields['order_management'] = array(
					'title'   => __( 'Enable Order Management', 'woocommerce-gateway-afterpay' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Arvato order capture on WooCommerce order completion and Arvato order cancellation on WooCommerce order cancellation', 'woocommerce-gateway-afterpay' ),
					'default' => 'yes'
				);
				$form_fields['testmode'] = array(
					'title'       => __( 'Arvato testmode', 'woocommerce-gateway-afterpay' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable Arvato testmode', 'woocommerce-gateway-afterpay' ),
					'default'     => 'no',
				);
				$form_fields['debug'] = array(
					'title'       => __( 'Debug Log', 'woocommerce-gateway-afterpay' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable logging', 'woocommerce-gateway-afterpay' ),
					'default'     => 'no',
					'description' => sprintf( __( 'Log ' . $this->method_title . ' events in <code>%s</code>', 'woocommerce-gateway-afterpay' ), wc_get_log_file_path( 'afterpay-invoice' ) )
				);
				$form_fields['customer_type'] = array(
					'title'       => __( 'Customer type', 'woocommerce-gateway-afterpay' ),
					'type'        => 'select',
					'description'       => __( 'Select the type of customer that can make purchases through AfterPay', 'woocommerce-gateway-afterpay' ),
					'options' => array(
						'both'      => __( 'Both person and company', 'woocommerce-gateway-afterpay' ),
						'private'   => __( 'Person', 'woocommerce-gateway-afterpay' ),
						'company'   => __( 'Company', 'woocommerce-gateway-afterpay' ),
					),
					'default'     => 'both',
				);
				$form_fields['separate_shipping_companies'] = array(
					'title'       => __( 'Separate shipping address', 'woocommerce-gateway-afterpay' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable separate shipping address for companies', 'woocommerce-gateway-afterpay' ),
					'default'     => 'no',
				);
			}

			$this->form_fields = $form_fields;
		}

		/**
		 * Process the payment and return the result.
		 *
		 * @param  int $order_id
		 *
		 * @return array
		 * @throws Exception
		 */
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );
			// If needed, run PreCheckCustomer
			$wc_afterpay_pre_check_customer = new WC_AfterPay_Pre_Check_Customer();
			if( ! WC()->session->get( 'afterpay_checkout_id' ) || $wc_afterpay_pre_check_customer->check_against_fields($order) ) {
				$response = $wc_afterpay_pre_check_customer->pre_check_customer_request( $_POST['afterpay-pre-check-customer-number'], $this->id, $_POST['afterpay_customer_category'], $order->billing_country, $order );
				
				if ( is_wp_error( $response ) ) {
					//throw new Exception( $response->get_error_message() );
					wc_add_notice( __( $response->get_error_message(), 'woocommerce-gateway-afterpay' ), 'error' );
					return false;
				}
				
				// Update the address if needed
				$afterpay_settings = get_option( 'woocommerce_afterpay_invoice_settings' );
				$customer_type = $afterpay_settings['customer_type'];
				if($customer_type === 'private') {
					$this->check_used_address( WC()->session->get( 'afterpay_customer_details' ), $order );
				}
			}
			
			
			// If needed, run CreateContract
			if ( 'arvato_account' == $this->id || 'arvato_part_payment' == $this->id ) {
				$wc_afterpay_create_contract = new WC_AfterPay_Create_Contract( $order_id, $this->id );
				if ( is_wp_error( $wc_afterpay_create_contract->create_contract() ) ) {
					return false;
				}
			}


			// Use WC_AfterPay_Complete_Checkout class to process the payment
			// Must previously perform PreCheckCustomer
			// CheckoutID and CustomerNo are required and returned from PreCheckCustomer
			$response = '';
			$wc_afterpay_complete_checkout = new WC_AfterPay_Complete_Checkout( $order_id, $this->id, $this->client_id, $this->username, $this->password );
			$response = $wc_afterpay_complete_checkout->complete_checkout();
			if ( ! is_wp_error( $response ) ) {
				// Mark payment complete on success
				$order->payment_complete();

				// Store reservation ID as order note
				$order->add_order_note(
					sprintf( __( 'Arvato reservation created, reservation ID: %s.', 'woocommerce-gateway-afterpay' ), get_post_meta( $order_id, '_afterpay_reservation_id', true ) )
				);

				// Remove cart
				WC()->cart->empty_cart();

				// Return thank you redirect
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order )
				);
			} else {
				wc_add_notice( __( $response->get_error_message(), 'woocommerce-gateway-afterpay' ), 'error' );
				return false;
			}
		}
		
		/**
		 * Display payment fields for all three payment methods
		 */
		function payment_fields() {
			if ( $this->description ) {
				echo wpautop( wptexturize( $this->description ) );
			}
			echo $this->get_afterpay_info();
		}
		
		/**
		 * Clear sessions on finalized purchase
		 */
		public function clear_afterpay_sessions() {
			
			WC()->session->__unset( 'afterpay_checkout_id' );
			WC()->session->__unset( 'afterpay_customer_no' );
			WC()->session->__unset( 'afterpay_personal_no' );
			WC()->session->__unset( 'afterpay_allowed_payment_methods' );
			WC()->session->__unset( 'afterpay_customer_details' );
			WC()->session->__unset( 'afterpay_cart_total' );
			
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
				$this->log( 'Refund failed: No Arvato invoice number ID.' );
				return new WP_Error( 'error', __( 'Refund failed: No Arvato invoice number ID.', 'woocommerce' ) );
			}

			// At the moment, only full refund is possible, because we can't send refunded order lines to AfterPay
			if ( $amount != $order->get_total() ) {
				$this->log( 'Refund failed: Only full order amount can be refunded via Arvato.' );
				return new WP_Error( 'error', __( 'Refund failed: Only full order amount can be refunded via Arvato.',
					'woocommerce' ) );
			}

			return true;
		}
		
		/**
		 * Check entered personal/organisation number
		 * 
		 **/
		public function process_checkout_fields() {	
			if ( $_POST['payment_method'] == 'afterpay_invoice' || $_POST['payment_method'] == 'afterpay_account' || $_POST['payment_method'] == 'afterpay_part_payment' ) {
				
				if( !is_numeric( $_POST['afterpay-pre-check-customer-number'] ) ) {
					$format = __( 'YYMMDDNNNN', 'woocommerce-gateway-afterpay' );
					wc_add_notice( sprintf( __( '<strong>Personal/organization number</strong> needs to be numeric and in the following format: %s.', 'woocommerce-gateway-afterpay' ), $format), 'error' );
				}
			}
		}
		
		/**
		 * Check used address
		 * Compare the address entered in the checkout with the registered address (returned from AfterPay)
		 **/
		public function check_used_address( $posted, $order ) {
			$changed_fields = array();
	
			// Shipping address
			if ( $posted['address_1'] != $order->shipping_address_1 ) {
				$changed_fields['shipping_&_billing_address_1'] = $posted['address_1'];
				update_post_meta( $order->id, '_shipping_address_1', $posted['address_1'] );
				update_post_meta( $order->id, '_billing_address_1', $posted['address_1'] );
			}
			
			if ( $posted['address_2'] != $order->shipping_address_2 ) {
				$changed_fields['shipping_&_billing_address_2'] = $posted['address_2'];
				update_post_meta( $order->id, '_shipping_address_2', $posted['address_2'] );
				update_post_meta( $order->id, '_billing_address_2', $posted['address_2'] );
			}
	
	
			// Post number
			if ( $posted['postcode'] != $order->shipping_postcode ) {
				$changed_fields['shipping_&_billing_postcode'] = $posted['postcode'];
				update_post_meta( $order->id, '_shipping_postcode', $posted['postcode'] );
				update_post_meta( $order->id, '_billing_postcode', $posted['postcode'] );
			}
	
			// City
			if ( $posted['city'] != $order->shipping_city ) {
				$changed_fields['shipping_&_billing_city'] = $posted['city'];
				update_post_meta( $order->id, '_shipping_city', $posted['city'] );
				update_post_meta( $order->id, '_billing_city', $posted['city'] );
			}
	
			// First name
			if ( $posted['first_name'] != $order->shipping_first_name ) {
				$changed_fields['shipping_&_billing_first_name'] = $posted['first_name'];
				update_post_meta( $order->id, '_shipping_first_name', $posted['first_name'] );
				update_post_meta( $order->id, '_billing_first_name', $posted['first_name'] );
			}
	
			// Last name
			if ( $posted['last_name'] != $order->shipping_last_name ) {
				$changed_fields['shipping_&_billing_last_name'] = $posted['last_name'];
				update_post_meta( $order->id, '_shipping_last_name', $posted['last_name'] );
				update_post_meta( $order->id, '_billing_last_name', $posted['last_name'] );
			}
	
			if ( ! empty( $changed_fields ) ) {
				$changed_fields_in_string = '';
				foreach ( $changed_fields as $key => $value ) {
					$changed_fields_in_string .= $key . ': ' . $value . ', ';
				}
	
				// Add order note about the changes
				$order->add_order_note( sprintf( __( 'The registered address did not match the one specified in WooCommerce. The order has been updated. The following fields was changed: %s.', 'woocommerce' ), $changed_fields_in_string ) );
			}
		}
		
		/**
		 * Helper function for displaying the AfterPay Invoice terms
		 */
		public function get_afterpay_info() {

			switch ( get_woocommerce_currency() ) {

				case 'NOK':
					//$terms_url   			= 'https://www.arvato.com/content/dam/arvato/documents/norway-ecomm-terms-and-conditions/Vilk%C3%A5r%20for%20AfterPay%20Faktura.pdf';
					//$terms_readmore 		= 'Les mer om AfterPay <a href="' . $terms_url . '" target="_blank">her</a>.';
					//$terms_content 			= '<h3>AfterPay Faktura</h3>';
					//if( 0 == $this->get_invoice_fee_price() ) {
					//	$terms_content 		.= '<p>Vi tilbyr AfterPay Faktura i samarbeid med arvato Finance AS. Betalingsfristen er 14 dager. Hvis du velger å betale med AfterPay faktura vil det ikke påløpe gebyr.</p>';
					//} else {
					// 	$terms_content 		.= '<p>Vi tilbyr AfterPay Faktura i samarbeid med arvato Finance AS. Betalingsfristen er 14 dager. Hvis du velger å betale med AfterPay faktura vil det påløpe et gebyr på NOK ' . $this->get_invoice_fee_price() . '.</p>';
					//}
					//$terms_content 			.= '<p>For å betale med faktura må du ha fylt 18 år, være folkeregistrert i Norge samt bli godkjent i kredittvurderingen som gjennomføres ved kjøpet. På bakgrunn av kredittsjekken vil det genereres gjenpartsbrev. Faktura sendes på e-post. Ved forsinket betaling vil det bli sendt inkassovarsel og lovbestemte gebyrer kan påløpe. Dersom betaling fortsatt uteblir vil fakturaen bli sendt til inkasso og ytterligere omkostninger vil påløpe.</p>';
					//$terms_content			.= '<h3>AfterPay Konto/Delbetalning</h3>';
					//$terms_content			.= '<p>Med AfterPay får du tilbud om å dele opp betalingen når du mottar fakturaen. Det er to alternative måter å dele opp betalingen på; konto eller delbetaling.</p>';
					//$terms_content			.= '<p><strong>AfterPay Konto</strong> er en fleksibel måte å betale din faktura på og du velger selv hvor mye du ønsker å betale hver måned. Minste beløp å betale vil til enhver tid være basert på utestående balanse og er presisert på den månedlige fakturaen. Priseksempel: 5000 kr o/ 9 mnd., effektiv rente 45,09 %. Samlet kredittkostnad: 820 kr.</p>';
					//$terms_content			.= '<p>Med <strong>AfterPay Delbetaling</strong> velger du hvor mange måneder du ønsker å dele opp betalingen i. Du kan velge mellom 3, 6, 12, 24 eller 36 måneder. På den måten vil du alltid ha kontroll på hva du skal betale per måned. Priseksempel: 5000 kr o/ 12 mnd, effektiv rente 43,58 %. Samlet kredittkostnad: 1009 kr.</p>';
					//$terms_content 			.= '<p>' . $terms_readmore . '</p>';
					$short_readmore 		= 'Les mer her';
					$afterpay_info ='<a target="_blank" href="https://www.afterpay.no/nb/vilkar">' . $short_readmore . '</a>';
					break;
				case 'SEK':
					$terms_url   			= 'https://www.arvato.com/content/dam/arvato/documents/norway-ecomm-terms-and-conditions/Vilk%C3%A5r%20for%20AfterPay%20Faktura.pdf';
					$terms_content			= wp_remote_retrieve_body( wp_remote_get( plugins_url() . '/afterpay-for-woocommerce/templates/afterpay-terms-' . $this->afterpay_country . '.html' ) );
					$terms_readmore 		= 'Läs mer om AfterPay <a href="' . $terms_url . '" target="_blank">här</a>.';
					$short_readmore 		= 'Läs mer här';
					$afterpay_info = '<div id="afterpay-terms-content" style="display:none;">';
					$afterpay_info .= $terms_content;
					$afterpay_info .='</div>';
					$afterpay_info .='<a href="#TB_inline?width=600&height=550&inlineId=afterpay-terms-content" class="thickbox">' . $short_readmore . '</a>';
					break;
				default:
					$terms_url   			= 'https://www.arvato.com/content/dam/arvato/documents/norway-ecomm-terms-and-conditions/Vilk%C3%A5r%20for%20AfterPay%20Faktura.pdf';
					$terms_content			= wp_remote_retrieve_body( wp_remote_get( plugins_url() . '/afterpay-for-woocommerce/templates/afterpay-terms-' . $this->afterpay_country . '.html' ) );
					$terms_readmore 		= 'Läs mer om AfterPay <a href="' . $terms_url . '" target="_blank">här</a>.';
					$short_readmore 		= 'Läs mer här';
					$afterpay_info = '<div id="afterpay-terms-content" style="display:none;">';
					$afterpay_info .= $terms_content;
					$afterpay_info .='</div>';
					$afterpay_info .='<a href="#TB_inline?width=600&height=550&inlineId=afterpay-terms-content" class="thickbox">' . $short_readmore . '</a>';
					break;
			}
			
			add_thickbox();
			
			return $afterpay_info;
		}
		
		
		/**
		 * Helper function - get Invoice fee price
		 */
		public function get_invoice_fee_price() {
			$invoice_settings = get_option( 'woocommerce_afterpay_invoice_settings' );
			$this->invoice_fee_id = $invoice_settings['invoice_fee_id'];
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
