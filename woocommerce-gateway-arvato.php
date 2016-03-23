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
 * Description:     Extends WooCommerce. Provides a <a href="http://www.klarna.se" target="_blank">Klarna</a> gateway for WooCommerce.
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


include_once( 'includes/gateways/invoice/class-wc-gateway-arvato-invoice.php' );
include_once( 'includes/gateways/part-payment/class-wc-gateway-arvato-part-payment.php' );
include_once( 'includes/gateways/account/class-wc-gateway-arvato-account.php' );