<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Complete Arvato checkout
 *
 * @class    WC_Arvato_Complete_Checkout
 * @version  1.0.0
 * @package  WC_Gateway_Arvato/Classes
 * @category Class
 * @author   Krokedil
 */
class WC_Arvato_Complete_Checkout {

	/**
	 * Mandatory fields
	 *
	 * Member name
	 * - CheckoutID
	 * - ContractID
	 * - CustomerNo (custom field _arvato_customer_no)
	 * - OrderNo (available in woocommerce_order_status_cancelled hook)
	 * - CurrencyCode
	 * - Amount (excluding VAT)
	 * - TotalOrderValue (including VAT)
	 * - OrderDate (yyyy-mm-dd)
	 *
	 * User (pulled using get_option)
	 * - ClientID
	 * - Password
	 * - Username
	 *
	 * PaymentInfo
	 * - PaymentMethod
	 *
	 * PaymentInfo.AccountInfo
	 * - AccountProfileNo (mandatory by account)
	 *
	 * PaymentInfo.InstallmentInfo
	 * AccountProfileNo (mandatory by installment)
	 * Amount (mandatory by installment)
	 * InstallmentProfileNo (mandatory by installment)
	 * NumberOfInstallments (mandatory by installment)
	 */

	/** @var int */
	private $order_id = '';

	/**
	 * WC_Arvato_Cancel_Reservation constructor.
	 */
	public function __construct() {
		$this->endpoint_checkout = 'https://sandboxapi.horizonafs.com/eCommerceServices/eCommerce/Checkout/v2/CheckoutServices.svc?wsdl';

		add_action( 'woocommerce_order_status_cancelled', array( $this, 'cancel_reservation' ) );
	}

}
$wc_arvato_complete_checkout = new WC_Arvato_Complete_Checkout;