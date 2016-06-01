<?php

require_once( '../woocommerce/woocommerce.php' );
require_once( 'woocommerce-gateway-afterpay.php' );

class WC_AfterPay_Cancel_Reservation_Test extends WP_UnitTestCase {

	private $username = 'WooComTestSE';
	private $password = 'm8K1Dfuj';
	private $client_id = '7852';
	private $checkout_endpoint = 'https://sandboxapi.horizonafs.com/eCommerceServices/eCommerce/Checkout/v2/CheckoutServices.svc?wsdl';

	public function test_pre_check_customer_request() {
		$args = array(
			'User'         => array(
				'ClientID' => $this->client_id,
				'Username' => $this->username,
				'Password' => $this->password
			),
			'Customer'     => array(
				'Address'                 => array(
					'CountryCode' => 'SE',
				),
				'CustomerCategory'        => 'Person',
				'Organization_PersonalNo' => '4202021111',
			),
			'OrderDetails' => array(
				'Amount'            => 419.2,
				'CurrencyCode'      => 'SEK',
				'OrderChannelType'  => 'Internet',
				'OrderDeliveryType' => 'Normal',
				'OrderLines'        => array(
					array(
						'GrossUnitPrice'  => 18,
						'ItemDescription' => 'Happy Ninja',
						'ItemID'          => 37,
						'LineNumber'      => 'a5bfc9e07964f8dddeb95fc584cd965d',
						'NetUnitPrice'    => 15.2542,
						'Quantity'        => 1,
						'VatPercent'      => 18,
					),
					array(
						'GrossUnitPrice'  => 401.2,
						'ItemDescription' => 'Flat Rate',
						'ItemID'          => 'flat_rate',
						'LineNumber'      => 'flat_rate',
						'NetUnitPrice'    => 340,
						'Quantity'        => 1,
						'VatPercent'      => 18,
					),
				)
			)
		);

		// PreCheckCustomer
		$soap_client = new SoapClient( $this->checkout_endpoint );
		$response = $soap_client->PreCheckCustomer( $args );
		$this->assertTrue( $response->IsSuccess );
	}

}
