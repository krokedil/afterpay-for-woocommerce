<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Process order lines for sending them to AfterPay
 *
 * @class    WC_AfterPay_Process_Order_Lines
 * @version  1.0.0
 * @package  WC_Gateway_AfterPay/Classes
 * @category Class
 * @author   Krokedil
 */
class WC_AfterPay_Process_Order_Lines {

	/**
	 * Get order lines from order or cart
	 *
	 * @param  bool $order_id
	 *
	 * @return array $order_lines
	 */
	public function get_order_lines( $order_id = false ) {
		if ( $order_id ) {
			return $this->get_order_lines_from_order( $order_id );
		} else {
			return $this->get_order_lines_from_cart();
		}
	}

	/**
	 * Process WooCommerce order into AfterPay order lines
	 *
	 * @param $order_id
	 *
	 * @return array
	 */
	private function get_order_lines_from_order( $order_id ) {
		$order       = new WC_Order( $order_id );
		$order_lines = array();
		// Process order lines
		if ( sizeof( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item_key => $item ) {
				$_product      = $order->get_product_from_item( $item );
				$order_lines[] = array(
					'grossUnitPrice'  	=> $order->get_item_total( $item, true ),
					'description' 		=> $item['name'],
					'productId'         => $this->get_item_reference( $_product ),
					'groupId'     		=> $_product->get_id(),
					'lineNumber'      	=> $item_key,
					'netUnitPrice'    	=> $order->get_item_total( $item, false ),
					'quantity'        	=> $item['qty'],
					'vatPercent'      	=> round( $order->get_item_tax( $item ) / $order->get_item_total( $item, false ), 4 ) * 100,
				);
			}
		}

		// Process shipping
		if ( $order->get_total_shipping() > 0 ) {
			$shipping_methods = $order->get_shipping_methods();
			foreach ( $shipping_methods as $shipping_method_key => $shipping_method_value ) {
				$shipping_method_taxes = $shipping_method_value['taxes']['total'];
				$shipping_method_tax = 0;
				foreach ( $shipping_method_taxes as $key => $value ) {
					$shipping_method_tax = $shipping_method_tax + floatval( $value );
				}
				$order_lines[] = array(
					'grossUnitPrice'  	=> $shipping_method_tax + $shipping_method_value['cost'],
					'description' 		=> $shipping_method_value['name'],
					'productId'			=> $shipping_method_value['type'],
					'groupId'     		=> $shipping_method_value['type'],
					'lineNumber'      	=> $shipping_method_key,
					'netUnitPrice'    	=> floatval( $shipping_method_value['cost'] ),
					'quantity'        	=> 1,
					'vatPercent'      	=> round( $shipping_method_tax / $shipping_method_value['cost'], 4 ) * 100,
				);
			}
		}

		// Process fees
		$order_fees = $order->get_fees();
		if ( ! empty( $order_fees ) ) {
			foreach ( $order->get_fees() as $order_fee_key => $order_fee_value ) {
				$order_lines[] = array(
					'grossUnitPrice'  	=> round( ( $order_fee_value['line_tax'] + $order_fee_value['line_total'] ), 2 ),
					'description' 		=> $order_fee_value['name'],
					'productId'			=> $order_fee_value['type'],
					'groupId'     		=> $order_fee_value['type'],
					'lineNumber'      	=> $order_fee_key,
					'netUnitPrice'    	=> $order_fee_value['line_total'],
					'quantity'        	=> 1,
					'vatPercent'      	=> round( $order_fee_value['line_tax'] / $order_fee_value['line_total'], 4 ) * 100
				);
			}
		}
		return $order_lines;
	}

	/**
	 * Process WooCommerce cart into AfterPay order lines
	 *
	 * @return array
	 */
	public function get_order_lines_from_cart() {
		$order_lines = array();

		// Process order lines
		if ( sizeof( WC()->cart->cart_contents ) > 0 ) {
			foreach ( WC()->cart->cart_contents as $item_key => $item ) {
				$_product      = wc_get_product( $item['product_id'] );;
				$order_lines[] = array(
					'grossUnitPrice'  => ( $item['line_tax'] + $item['line_total'] ) / $item['quantity'],
					'description' => get_the_title( $item['product_id'] ),
					'productId'          => $this->get_item_reference( $_product ),
					'lineNumber'      => $item_key,
					'netUnitPrice'    => $item['line_total'] / $item['quantity'],
					'quantity'        => $item['quantity'],
					'vatPercent'      => round( $item['line_tax'] / $item['line_total'], 4 ) * 100
				);
			}
		}

		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

		// Process shipping
		if ( WC()->shipping->get_packages() ) {
			foreach ( WC()->shipping->get_packages() as $shipping_package ) {
				foreach ( $shipping_package['rates'] as $shipping_rate_key => $shipping_rate_value ) {
					if ( in_array( $shipping_rate_value->id, $chosen_methods ) ) {
						$shipping_tax = array_sum( $shipping_rate_value->taxes );

						if ( $shipping_rate_value->cost > 0 ) {
							$vat_percent = round( $shipping_tax / $shipping_rate_value->cost, 4 ) * 100;
						} else {
							$vat_percent = 0;
						}

						$order_lines[] = array(
							'grossUnitPrice'  	=> $shipping_tax + $shipping_rate_value->cost,
							'description' 		=> $shipping_rate_value->label,
							'productId'			=> $shipping_rate_value->id,
							'lineNumber'		=> $shipping_rate_key,
							'netUnitPrice'		=> $shipping_rate_value->cost,
							'quantity'			=> 1,
							'vatPercent'		=> $vat_percent
						);
					}
				}
			}

		}

		// Process fees
		if ( WC()->cart->fee_total > 0 ) {
			foreach ( WC()->cart->get_fees() as $cart_fee ) {
				$cart_fee_tax = array_sum( $cart_fee->tax_data );

				$order_lines[] = array(
					'grossUnitPrice'  => round( ( $cart_fee->amount + $cart_fee_tax ), 2 ),
					'description' => $cart_fee->name,
					'productId'          => $cart_fee->id,
					'lineNumber'      => $cart_fee->id,
					'netUnitPrice'    => $cart_fee->amount,
					'quantity'        => 1,
					'vatPercent'      => round( $cart_fee_tax / $cart_fee->amount, 4 ) * 100
				);
			}
		}
		return $order_lines;
	}

	/**
	 * Gets product SKU, variation ID or ID
	 *
	 * @param $_product object
	 *
	 * @return string
	 */
	public function get_item_reference( $_product ) {
		if ( $_product->get_sku() ) {
			$item_reference = $_product->get_sku();
		} elseif ( $_product->get_id() ) {
			$item_reference = $_product->get_id();
		} else {
			$item_reference = $_product->get_id();
		}

		return strval( $item_reference );
	}

}