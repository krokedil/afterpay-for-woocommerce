<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Process order lines for sending them to Arvato
 *
 * @class    WC_Arvato_Process_Order_Lines
 * @version  1.0.0
 * @package  WC_Gateway_Arvato/Classes
 * @category Class
 * @author   Krokedil
 */
class WC_Arvato_Process_Order_Lines {

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
	 * Process WooCommerce order into Arvato order lines
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
					'GrossUnitPrice'  => $order->get_item_total( $item, true ),
					'ItemDescription' => $item['name'],
					'ItemID'          => $_product->id,
					'ItemGroupId'     => $_product->id,
					'LineNumber'      => $item_key,
					'NetUnitPrice'    => $order->get_item_total( $item, false ),
					'Quantity'        => $item['qty'],
					'VatPercent'      => round( $order->get_item_tax( $item ) / $order->get_item_total( $item, false ), 4 ) * 100
				);
			}
		}

		// Process shipping
		if ( $order->get_total_shipping() > 0 ) {
			foreach ( $order->get_shipping_methods() as $shipping_method_key => $shipping_method_value ) {
				$shipping_method_tax = array_sum( maybe_unserialize( $shipping_method_value['taxes'] ) );

				$order_lines[] = array(
					'GrossUnitPrice'  => $shipping_method_tax + $shipping_method_value['cost'],
					'ItemDescription' => $shipping_method_value['name'],
					'ItemID'          => $shipping_method_key,
					'ItemGroupId'     => $shipping_method_key,
					'LineNumber'      => $shipping_method_key,
					'NetUnitPrice'    => $shipping_method_value['cost'],
					'Quantity'        => 1,
					'VatPercent'      => round( $shipping_method_tax / $shipping_method_value['cost'], 4 ) * 100
				);
			}
		}

		// Process fees
		if ( ! empty( $order->get_fees() ) ) {
			foreach ( $order->get_fees() as $order_fee_key => $order_fee_value ) {
				$order_lines[] = array(
					'GrossUnitPrice'  => round( ( $order_fee_value['line_tax'] + $order_fee_value['line_total'] ), 2 ),
					'ItemDescription' => $order_fee_value['name'],
					'ItemID'          => $order_fee_key,
					'ItemGroupId'     => $order_fee_key,
					'LineNumber'      => $order_fee_key,
					'NetUnitPrice'    => $order_fee_value['line_total'],
					'Quantity'        => 1,
					'VatPercent'      => round( $order_fee_value['line_tax'] / $order_fee_value['line_total'], 4 ) * 100
				);
			}
		}

		return $order_lines;
	}

	/**
	 * Process WooCommerce cart into Arvato order lines
	 *
	 * @return array
	 */
	public function get_order_lines_from_cart() {
		$order_lines = array();

		// Process order lines
		if ( sizeof( WC()->cart->cart_contents ) > 0 ) {
			foreach ( WC()->cart->cart_contents as $item_key => $item ) {
				$order_lines[] = array(
					'GrossUnitPrice'  => ( $item['line_tax'] + $item['line_total'] ) / $item['quantity'],
					'ItemDescription' => get_the_title( $item['product_id'] ),
					'ItemID'          => $item['product_id'],
					'LineNumber'      => $item_key,
					'NetUnitPrice'    => $item['line_total'] / $item['quantity'],
					'Quantity'        => $item['quantity'],
					'VatPercent'      => round( $item['line_tax'] / $item['line_total'], 4 ) * 100
				);
			}
		}

		// Process shipping
		if ( WC()->shipping->get_packages() ) {
			foreach ( WC()->shipping->get_packages() as $shipping_package ) {
				foreach ( $shipping_package['rates'] as $shipping_rate_key => $shipping_rate_value ) {
					$shipping_tax = array_sum( $shipping_rate_value->taxes );

					$order_lines[] = array(
						'GrossUnitPrice'  => $shipping_tax + $shipping_rate_value->cost,
						'ItemDescription' => $shipping_rate_value->label,
						'ItemID'          => $shipping_rate_value->id,
						'LineNumber'      => $shipping_rate_key,
						'NetUnitPrice'    => $shipping_rate_value->cost,
						'Quantity'        => 1,
						'VatPercent'      => round( $shipping_tax / $shipping_rate_value->cost, 4 ) * 100
					);
				}
			}

		}

		// Process fees
		if ( WC()->cart->fee_total > 0 ) {
			foreach ( WC()->cart->get_fees() as $cart_fee ) {
				$cart_fee_tax = array_sum( $cart_fee->tax_data );

				$order_lines[] = array(
					'GrossUnitPrice'  => round( ( $cart_fee->amount + $cart_fee_tax ), 2 ),
					'ItemDescription' => $cart_fee->label,
					'ItemID'          => $cart_fee->id,
					'LineNumber'      => $cart_fee->id,
					'NetUnitPrice'    => $cart_fee->amount,
					'Quantity'        => 1,
					'VatPercent'      => round( $cart_fee_tax / $cart_fee->amount, 4 ) * 100
				);
			}
		}

		error_log( 'CART ORDER LINES: ' . var_export( $order_lines, true ) );

		return $order_lines;
	}

}