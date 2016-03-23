<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Arvato Invoice Gateway.
 */
return array(
	'enabled' => array(
		'title'   => __( 'Enable/Disable', 'woocommerce-gateway-arvato' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Arvato Invoice', 'woocommerce-gateway-arvato' ),
		'default' => 'yes'
	),
	'title' => array(
		'title'       => __( 'Title', 'woocommerce-gateway-arvato' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-arvato' ),
		'default'     => __( 'Arvato Invoice', 'woocommerce-gateway-arvato' ),
		'desc_tip'    => true,
	),
	'description' => array(
		'title'       => __( 'Description', 'woocommerce-gateway-arvato' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-arvato' ),
	),
	'username' => array(
		'title'       => __( 'Arvato Username', 'woocommerce-gateway-arvato' ),
		'type'        => 'text',
		'description' => __( 'Please enter your Arvato username; this is needed in order to take payment.',
			'woocommerce-gateway-arvato' ),
	),
	'password' => array(
		'title'       => __( 'Arvato Password', 'woocommerce-gateway-arvato' ),
		'type'        => 'text',
		'description' => __( 'Please enter your Arvato password; this is needed in order to take payment.',
			'woocommerce-gateway-arvato' ),
	),
	'client_id' => array(
		'title'       => __( 'Arvato Client ID', 'woocommerce-gateway-arvato' ),
		'type'        => 'text',
		'description' => __( 'Please enter your Arvato client ID; this is needed in order to take payment.',
			'woocommerce-gateway-arvato' ),
	),
	'order_management' => array(
		'title'   => __( 'Enable Order Management', 'woocommerce-gateway-arvato' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Arvato order capture on WooCommerce order completion and Arvato order refund on 
		WooCommerce order refund',
			'woocommerce-gateway-arvato' ),
		'default' => 'yes'
	),
	'testmode' => array(
		'title'       => __( 'Arvato testmode', 'woocommerce-gateway-arvato' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable Arvato testmode', 'woocommerce-gateway-arvato' ),
		'default'     => 'no',
	),
	'debug' => array(
		'title'       => __( 'Debug Log', 'woocommerce-gateway-arvato' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'woocommerce-gateway-arvato' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Log Arvato Invoice events, such as IPN requests, inside <code>%s</code>', 'woocommerce-gateway-arvato' ), wc_get_log_file_path( 'arvato-invoice' ) )
	),
);
