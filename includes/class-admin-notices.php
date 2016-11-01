<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Returns error messages depending on 
 *
 * @class    WC_AfterPay_Admin_Notices
 * @version  1.1
 * @package  WC_AfterPay/Classes
 * @category Class
 * @author   Krokedil
 */
class WC_AfterPay_Admin_Notices {
	
	/**
	 * WC_AfterPay_Admin_Notices constructor.
	 */
	public function __construct() {
		$afterpay_invoice_settings = get_option( 'woocommerce_afterpay_invoice_settings' );
		$this->enabled 		= $afterpay_invoice_settings['enabled'];
		$this->username 	= $afterpay_invoice_settings['username'];
		$this->username_se 	= $afterpay_invoice_settings['username_se'];
		
		add_action( 'admin_init', array( $this, 'check_settings' ) );
	}
	
	public function check_settings() {
		if ( ! empty( $_POST ) ) {
			add_action( 'woocommerce_settings_saved', array( $this, 'check_terms' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'check_terms' ) );
		}
	}
	/**
	 * Check if terms page is set
	 */
	public function check_terms() {
		if( 'yes' != $this->enabled ) {
			return;
		}
		
		// Terms page
		if( $this->username && !$this->username_se ) {
			echo '<div class="notice notice-error">';
			echo '<p>' . __( 'We have updated the settings for AfterPay. You need to enter your AfterPay merchant credentials again and resave the settings top be able to take payments via AfterPay.', 'woocommerce-gateway-afterpay' ) . '</p>';
			echo '</div>';
		}
	}
}
$wc_afterpay_admin_notices = new WC_AfterPay_Admin_Notices;