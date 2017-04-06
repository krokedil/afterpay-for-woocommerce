=== Arvato for WooCommerce ===
Contributors: krokedil, niklashogefjord, slobodanmanic
Tags: ecommerce, e-commerce, woocommerce, afterpay, arvato
Requires at least: 4.2
Tested up to: 4.7.3
Stable tag: 1.2
Requires WooCommerce at least: 2.4
Tested WooCommerce up to: 2.6.14
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Arvato for WooCommerce is a plugin that extends WooCommerce, allowing you to take payments via Arvato.

== Description ==

With this extension you get access to [Arvatos](https://www.arvato.com/se-en/) three payment methods - Invoice, Part Payment and Account - in Sweden & Norway.

= Get started =
More information on how to get started can be found in the [plugin documentation](http://docs.krokedil.com/documentation/afterpay-for-woocommerce/).

== Installation ==

1. Download and unzip the latest release zip file.
2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
3. Upload the entire plugin directory to your /wp-content/plugins/ directory.
4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
5. Go to --> WooCommerce --> Settings --> Checkout and configure your Arvato settings.

== Frequently Asked Questions ==
= Which countries does this payment gateway support? =
Sweden is the only available country at the moment. Norway will be added in short.

= Where can I find Arvato for WooCommerce documentation? =
For help setting up and configuring Arvato for WooCommerce please refer to our [documentation](http://docs.krokedil.com/documentation/afterpay-for-woocommerce/).

= Where can I get support? =
If you get stuck, you can ask for help in the Plugin Forum.

If you need help with installation and configuration Krokedil offer premium (paid) support. More information about our concierge service can be found on [the Arvato for WooCommerce product page](https://krokedil.se/produkt/afterpay/).


== Changelog ==

= 1.2		- 2017.04.06 =
* Feature	- Added setting to be able to sell only to companies or individuals.
* Feature	- Added setting to enable separate shipping address for companies if wanted. This feature requires a separate agreement with Arvato.
* Tweak		- AfterPay terms link now redirects to external site for Norwegian customers.
* Tweak		- Disable get address button when request is being performed (to avoid multiple concurrent calls).
* Tweak		- Possible to add/change personal ID number in user settings page.
* Fix		- Don’t show payment method in checkout if not enabled in settings.
* Fix		- Avoid errors in is_available() if check is performed in backend.
* Fix		- Updated Swedish translation.

= 1.1.2		- 2017.01.21 =
* Tweak		- Updated terms text for Sweden.
* Tweak		- Moved Swedish terms text to separate file.

= 1.1.1		- 2016.11.28 =
* Fix		- Don't set .focus() on Arvato personal number input field on js-event updated_checkout(). updated_checkout() might be triggered during entering info in Postal number & City fields.

= 1.1 		- 2016.11.01 =
* Feature	- Added support for Norway.
* Feature	- Added invoice fee feature.
* Tweak		- Added terms text for Sweden & Norway.

= 1.0.2 	- 2016.06.17 =
* Tweak 	- Defined path to translation files (load_plugin_textdomain).
* Tweak 	- Added Swedish translation.
* Tweak		- Updated display of Personal/organization number in checkout.
* Fix		- Only check for entered personal number in checkout when Arvato is selected payment method.
* Fix		- Updated html markup for radio buttons when displaying installment plans in checkout for part payment.

= 1.0.1 =
* Tweak 	- Readme update.

= 1.0 =
* Initial release