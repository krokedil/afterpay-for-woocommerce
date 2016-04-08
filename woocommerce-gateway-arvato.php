<?php
/**
 * WooCommerce Arvato Gateway
 *
 * @since 0.1
 *
 * @package WC_Gateway_Arvato
 *
 * @wordpress-plugin
 * Plugin Name:     WooCommerce Arvato Gateway
 * Plugin URI:      http://woothemes.com/woocommerce
 * Description:     Provides Arvato AfterPay payment gateway for WooCommerce.
 * Version:         0.1
 * Author:          Krokedil
 * Author URI:      http://krokedil.com/
 * Developer:       Krokedil
 * Developer URI:   http://krokedil.com/
 * Text Domain:     woocommerce-gateway-arvato
 * Domain Path:     /languages
 * Copyright:       © 2016 Krokedil.
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Check if SOAP extension is loaded, prevent plugin activation if it isn't.
 */
register_activation_hook( __FILE__, 'woocommerce_gateway_arvato_activation_check' );
function woocommerce_gateway_arvato_activation_check() {
	if ( ! extension_loaded( 'soap' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( __( 'WooCommerce Arvato Gateway requires PHP SOAP extension. Please get in touch with 
	your hosting provider to see how you can enable it.', 'woocommerce-gateway-arvato' ) );
	}
}

/**
 * If the plugin was activated in some other way, deactivate it if SOAP extension is not loaded.
 */
add_action( 'admin_init', 'woocommerce_gateway_arvato_soap_check' );
function woocommerce_gateway_arvato_soap_check() {
	if ( ! extension_loaded( 'soap' ) ) {
		if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			add_action( 'admin_notices', 'woocommerce_gateway_arvato_disabled_notice' );

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}
}

/**
 * Print deactivation notice.
 */
function woocommerce_gateway_arvato_disabled_notices() {
	echo '<div class="notice notice-error">';
	echo '<p><strong>' . esc_html__( 'WooCommerce Arvato Gateway requires PHP SOAP extension. Please get in touch with 
	your hosting provider to see how you can enable it.', 'woocommerce-gateway-arvato' ) . '</strong></p>';
	echo '</div>';
}

include_once( 'includes/gateways/invoice/class-wc-gateway-arvato-invoice.php' );
include_once( 'includes/gateways/part-payment/class-wc-gateway-arvato-part-payment.php' );
include_once( 'includes/gateways/account/class-wc-gateway-arvato-account.php' );

include_once( 'includes/class-pre-check-customer.php' );
include_once( 'includes/class-cancel-reservation.php' );