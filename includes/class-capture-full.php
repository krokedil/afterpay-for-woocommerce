<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Cancel Arvato reservation
 *
 * Check if order was created using Arvato and if yes, cancel Arvato order when WooCommerce order is marked cancelled.
 *
 * @class WC_Arvato_Capture_Full
 * @version 1.0.0
 * @package WC_Gateway_Arvato/Classes
 * @category Class
 * @author Krokedil
 */
class WC_Arvato_Capture_Full {

	/**
	 * Mandatory fields
	 * Member name
	 * - CustomerNo (custom field _arvato_customer_no)
	 * - OrderNo (available in woocommerce_order_status_cancelled hook)
	 *
	 * User (pulled using get_option)
	 * - ClientID
	 * - Password
	 * - Username
	 */

}
$wc_arvato_capture_full = new WC_Arvato_Capture_Full;