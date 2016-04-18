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
	 * @param  bool  $order_id
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
		$order = new WC_Order( $order_id );
		$order_lines = array();

		if ( sizeof( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item_key => $item ) {
				$_product = $order->get_product_from_item( $item );
				$order_lines[] = array(
					'GrossUnitPrice' => $order->get_item_total( $item, true ),
					'ItemDescription' => $item['name'],
					'ItemID' => $_product->id,
					'ItemGroupId' => '9999',
					'LineNumber' => $item_key,
					'NetUnitPrice' => $order->get_item_total( $item, false ),
					'Quantity' => $item['qty'],
					'VatPercent' => $order->get_item_tax( $item ) / $order->get_item_total( $item, false )
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

		if ( sizeof( WC()->cart->cart_contents ) > 0 ) {
			foreach ( WC()->cart->cart_contents as $item_key => $item ) {
				$order_lines[] = array(
					'GrossUnitPrice' => $item['line_total'],
					'ItemDescription' => $item['name'],
					'ItemID' => $item['product_id'],
					'ItemGroupId' => '9999',
					'LineNumber' => $item_key,
					'NetUnitPrice' => $item['line_total'],
					'Quantity' => $item['quantity'],
					'VatPercent' => $item['line_tax'] / $item['line_subtotal']
				);
			}
		}

		return $order_lines;
	}

}