=== Payburner Payment Gateway ===
Contributors: payburner
Tags: xrp woocommerce payment gateway
Donate link: http://example.com/
Requires at least: 4.0
Tested up to: 4.8
Requires PHP: 5.6
Stable tag: 1.0.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is an XRP payment gateway for wc, using Payburner.

== Description ==
This gateway allows you to accept XRP payment on wc enabled wp sites using the Payburner service.  Payburner is an XRP wallet deployed as a browser extension.

This Wordpress plugin imports two javascript files from https://www.payburner.com in order to connect the page to the browser extension and to render and control the Payburner pay button.

The source code for these two javascript files is located at:

https://github.com/payburner/payburner.js
https://github.com/payburner/paybutton.js

payburner.js interacts solely with the payburner browser extension which can be found at https://chrome.google.com/webstore/detail/payburner-browser-extensi/ghigcfhmoaokccllienfhdhdndkfhmop

The browser extension itself is a what is called a non-custodial hot wallet.  The users maintain full control over the wallet and their funds.

paybutton.js interacts with https://gateway.payburner.com to manage the status of the payment on the payburner payment gateway.

On the back end, the php class class-payburner-api.php interacts with https://gateway.payburner.com to check the payment status.

The privacy policy of Payburner and its related sites, including https://www.payburner.com and https://gateway.payburner.com can be found at: https://www.payburner.com/payburner-privacy-policy.txt

== Installation ==
1. Upload the plugin folder to the \"/wp-content/plugins/\" directory.
2. Activate the plugin through the \"Plugins\" menu in WordPress.
3. Enable and configure the gateway in the wc payments management tab.

== Frequently Asked Questions ==
= How do I obtain a paybutton id ? =
Download and install the payburner browser extension from the chrome store (https://chrome.google.com/webstore/detail/payburner-browser-extensi/ghigcfhmoaokccllienfhdhdndkfhmop).  Then create a pay button using the pay button manager.  For further information, please consult the docs: https://github.com/payburner/paybutton-docs

== Screenshots ==
1. The payburner payment form payburner-payment-form.png
2. The wc payment gateways wc-payment-gateways.png
3. The payment gateway setup payburner-payment-gateway-setup.png

== Changelog ==

= 1.0.2 =
* Added privacy policy verbiage

= 1.0.1 =
* Updated for wordpress plugin requirements.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.1 =
Added privacy policy verbiage.

= 1.0.1 =
Updated for wordpress plugin requirements.

= 1.0.0 =
This is our initial version.