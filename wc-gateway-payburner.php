<?php
/*
 * Plugin Name: Payburner Payment Gateway
 * Plugin URI: https://wordpress.org/plugins/wc-gateway-payburner/
 * Description: Accept XRP coin payments in your store.
 * Author: Payburner
 * Author URI: https://github.com/payburner
 * Version: 1.0.4
 * Text Domain: wc-gateway-payburner
 * Domain Path: /languages
 *
 *
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_PAYBURNER_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'WC_PAYBURNER_VERSION', '0.0.11' );
define( 'WC_PAYBURNER_MIN_PHP_VER', '5.3.0' );
define( 'WC_PAYBURNER_MIN_WC_VER', '2.5.0' );
define( 'WC_PAYBURNER_MAIN_FILE', __FILE__ );

if ( ! class_exists( 'WC_Payburner' ) ) {

	class WC_Payburner {

		/**
		 * @var Singleton The reference the *Singleton* instance of this class
		 */
		private static $instance;

		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @return Singleton The *Singleton* instance.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Private clone method to prevent cloning of the instance of the
		 * *Singleton* instance.
		 *
		 * @return void
		 */
		private function __clone() {}

		/**
		 * Private unserialize method to prevent unserializing of the *Singleton*
		 * instance.
		 *
		 * @return void
		 */
		private function __wakeup() {}

		/**
		 * Notices (array)
		 * @var array
		 */
		public $notices = array();


		protected function __construct() {
			add_action( 'plugins_loaded', array( $this, 'init_gateways' ) );
		}

		/**
		 * Add the gateways to wc
		 *
		 * @since 1.0.0
		 */
		public function init_gateways() {

            include_once ( plugin_basename('includes/class-payburner-logger.php'));
			include_once ( plugin_basename('includes/class-wc-gateway-payburner.php'));
			include_once ( plugin_basename('includes/class-payburner-ajax.php'));

			/*
			 * Need make wc aware of the Gateway class
			 * */
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateways' ) );

		}

		/**
		 * Add the gateways to wc
		 *
		 * @since 1.0.0
		 */
		public function add_gateways( $methods ) {
			$methods[] = 'WC_Gateway_Payburner';
			return $methods;
		}
	}
	$GLOBALS['wc_payburner'] = WC_Payburner::get_instance();
}