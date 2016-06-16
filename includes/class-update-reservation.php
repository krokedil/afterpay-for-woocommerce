<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Update AfterPay reservation
 *
 * Check if order was created using AfterPay and if yes, update AfterPay reservation when WooCommerce order is updated.
 *
 * @class WC_AfterPay_Update_Reservation
 * @version 1.0.0
 * @package WC_Gateway_AfterPay/Classes
 * @category Class
 * @author Krokedil
 */
class WC_AfterPay_Update_Reservation {

	/**
	 * WC_AfterPay_Cancel_Reservation constructor.
	 */
	public function __construct() {
		// Add order item
		add_action( 'woocommerce_ajax_add_order_item_meta', array( $this, 'add_item' ), 10, 3 );

		// Edit an order item and save
		add_action( 'woocommerce_saved_order_items', array( $this, 'edit_item' ), 10, 2 );
	}

	function add_item( $itemid, $item ) {
		global $wpdb;
		$item_row = $wpdb->get_row( $wpdb->prepare( "
			SELECT      order_id
			FROM        {$wpdb->prefix}woocommerce_order_items
			WHERE       order_item_id = %d
		", $itemid ) );

		$orderid = $item_row->order_id;
		error_log( 'add_item' );
		$order = wc_get_order( $orderid );
	}

	function edit_item( $orderid, $items ) {
		// if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			$order = wc_get_order( $orderid );
		// }
	}

}
$wc_afterpay_update_reservation = new WC_AfterPay_Update_Reservation;