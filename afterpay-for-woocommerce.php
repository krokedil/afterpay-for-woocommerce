<?php
/**
 * WooCommerce AfterPay Gateway
 *
 * @since 0.1
 *
 * @package WC_Gateway_AfterPay
 *
 * @wordpress-plugin
 * Plugin Name:     AfterPay v3 for WooCommerce
 * Plugin URI:      https://krokedil.se/produkt/afterpay/
 * Description:     Provides AfterPay v3 payment gateway for WooCommerce.
 * Version:         1.2
 * Author:          Krokedil
 * Author URI:      http://krokedil.com/
 * Developer:       Krokedil
 * Developer URI:   http://krokedil.com/
 * Text Domain:     woocommerce-gateway-afterpay
 * Domain Path:     /languages
 * Copyright:       © 2016-2017 Krokedil.
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Localisation.
 */
load_plugin_textdomain( 'woocommerce-gateway-afterpay', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/**
 * Check if SOAP extension is loaded, prevent plugin activation if it isn't.
 */
function woocommerce_gateway_afterpay_activation_check() {
	if ( ! extension_loaded( 'soap' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( __( 'WooCommerce AfterPay Gateway requires PHP SOAP extension. Please get in touch with 
	your hosting provider to see how you can enable it.', 'woocommerce-gateway-afterpay' ) );
	}
}
register_activation_hook( __FILE__, 'woocommerce_gateway_afterpay_activation_check' );
/**
 * If the plugin was activated in some other way, deactivate it if SOAP extension is not loaded.
 */

function woocommerce_gateway_afterpay_soap_check() {
	if ( ! extension_loaded( 'soap' ) ) {
		if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			add_action( 'admin_notices', 'woocommerce_gateway_afterpay_disabled_notice' );

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}
}
add_action( 'admin_init', 'woocommerce_gateway_afterpay_soap_check' );

/**
 * Print deactivation notice.
 */
function woocommerce_gateway_afterpay_disabled_notices() {
	echo '<div class="notice notice-error">';
	echo '<p><strong>' . esc_html__( 'WooCommerce AfterPay Gateway requires PHP SOAP extension. Please get in touch with 
	your hosting provider to see how you can enable it.', 'woocommerce-gateway-afterpay' ) . '</strong></p>';
	echo '</div>';
}

// Define plugin paths
define( 'AFTERPAY_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
define( 'AFTERPAY_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

include_once( AFTERPAY_PATH . '/includes/gateways/class-wc-gateway-afterpay-factory.php' );
include_once( AFTERPAY_PATH . '/includes/gateways/class-wc-gateway-afterpay-invoice.php' );
include_once( AFTERPAY_PATH . '/includes/gateways/class-wc-gateway-afterpay-part-payment.php' );
include_once( AFTERPAY_PATH . '/includes/gateways/class-wc-gateway-afterpay-account.php' );

include_once( AFTERPAY_PATH . '/includes/class-pre-check-customer.php' );
include_once( AFTERPAY_PATH . '/includes/class-cancel-reservation.php' );
include_once( AFTERPAY_PATH . '/includes/class-create-contract.php' );
include_once( AFTERPAY_PATH . '/includes/class-complete-checkout.php' );
include_once( AFTERPAY_PATH . '/includes/class-update-reservation.php' );

include_once( AFTERPAY_PATH . '/includes/class-process-order-lines.php' );
include_once( AFTERPAY_PATH . '/includes/class-invoice-fee.php' );
include_once( AFTERPAY_PATH . '/includes/class-error-notice.php' );
include_once( AFTERPAY_PATH . '/includes/class-admin-notices.php' );

// V3
include_once( AFTERPAY_PATH . '/includes/requests/class-wc-afterpay-request.php' );
include_once( AFTERPAY_PATH . '/includes/class-capture.php' );
include_once( AFTERPAY_PATH . '/includes/requests/helpers/class-wc-afterpay-request-customer.php' );
include_once( AFTERPAY_PATH . '/includes/requests/helpers/class-wc-afterpay-request-available-payment-methods.php' );
include_once( AFTERPAY_PATH . '/includes/requests/helpers/class-wc-afterpay-request-authorize-payment.php' );
include_once( AFTERPAY_PATH . '/includes/requests/helpers/class-wc-afterpay-request-create-contract.php' );
include_once( AFTERPAY_PATH . '/includes/requests/helpers/class-wc-afterpay-request-capture-payment.php' );
include_once( AFTERPAY_PATH . '/includes/requests/helpers/class-wc-afterpay-request-cancel-payment.php' );
include_once( AFTERPAY_PATH . '/includes/requests/helpers/class-wc-afterpay-request-refund-payment.php' );

// Define server endpoints
define(
	'ARVATO_CHECKOUT_LIVE',
	'https://api.afterpay.io'
);
define(
	'ARVATO_CHECKOUT_TEST',
	'https://sandboxapi.horizonafs.com/eCommerceServicesWebApi'
);
