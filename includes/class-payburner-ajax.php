<?php


class Payburner_Ajax {

	private static $instance;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct( ) {
		$this->init();
	}

    public static function init() {
        \add_action('init', array(__CLASS__, 'define_ajax'), 0);
        \add_action('template_redirect', array(__CLASS__, 'do_wc_ajax'), 0);
        self::add_ajax_events();
    }

    public static function get_endpoint($request = '') {
        return esc_url_raw(add_query_arg('wc-ajax', $request, remove_query_arg(array('remove_item', 'add-to-cart', 'added-to-cart'))));
    }

    public static function define_ajax() {
        if (!empty($_GET['wc-ajax'])) {
            if (!defined('DOING_AJAX')) {
                define('DOING_AJAX', true);
            }
            if (!defined('WC_DOING_AJAX')) {
                define('WC_DOING_AJAX', true);
            }
            // Turn off display_errors during AJAX events to prevent malformed JSON
            if (!WP_DEBUG || (WP_DEBUG && !WP_DEBUG_DISPLAY)) {
                @ini_set('display_errors', 0);
            }
            $GLOBALS['wpdb']->hide_errors();
        }
    }

    private static function wc_ajax_headers() {
        \send_origin_headers();
        @header('Content-Type: text/html; charset=' . get_option('blog_charset'));
        @header('X-Robots-Tag: noindex');
        \send_nosniff_header();
        \nocache_headers();
        \status_header(200);
    }

    public static function do_wc_ajax() {
        global $wp_query;
        if (!empty($_GET['wc-ajax'])) {
            $wp_query->set('wc-ajax', sanitize_text_field($_GET['wc-ajax']));
        }
        if ($action = $wp_query->get('wc-ajax')) {
            self::wc_ajax_headers();
            \do_action('wc_ajax_' . sanitize_text_field($action));
            die();
        }
    }

    public static function add_ajax_events() {
        // wc_EVENT => nopriv
        $ajax_events = array(
	        'check_for_payment'                   => true,
        );
        foreach ($ajax_events as $ajax_event => $nopriv) {
            \add_action('wp_ajax_wc_' . $ajax_event, array(__CLASS__, $ajax_event));
            if ($nopriv) {
                \add_action('wp_ajax_nopriv_wc_' . $ajax_event, array(__CLASS__, $ajax_event));
                // WC AJAX can be used for frontend ajax requests
                \add_action('wc_ajax_' . $ajax_event, array(__CLASS__, $ajax_event));
            }
        }
    }

	public static function check_for_payment( ){
		//Check the hash passed for to js for doing ajax calls.
		\check_ajax_referer('wc-payburner', 'nounce');

		/*
		 *
		 * Lets prepare some variables we need
		 * */

		//The hash should be the same as generated in class-wc-gateway-payburner.php.
		$ref_id = wp_create_nonce( "3h62h6u26h42h6i2462h6u4h624" );

		//Get settings from the payment gateway
        $payburner_options = get_option('wc_payburner_settings');
		$buttonid = $payburner_options['buttonid'];

        $purchaseId = $_POST['purchaseId'];
        WC()->session->set('paybutton_purchase_id', $purchaseId);

		//Get the purchase from the payburner gateway.
		include_once ('class-payburner-api.php');
		$purchase = PayburnerApi::get_purchase($buttonid,$purchaseId);

		//If we dident get any transactions, return an error.
        if(!$purchase){

            $path = '/v1/gateway/paybuttons/'.$buttonid.'/purchase/'.$purchaseId;

            $res ='http://gateway.payburner.com'.$path;

			self::error("The purchase wasn't found on Payburner: ".$res);
			return false;
		}

		if ($purchase->data->status !== 'SETTLED') {
            self::send(array(
                'match' => false,
                'matched_transaction' => false,
                'purchase_id' => $purchaseId,
                'status' => $purchase->data->status,
            ));
            return false;
        }
		else if ($purchase->data->status === 'SETTLED') {
            $payburner_purchase_reference = WC()->session->get('paybutton_purchase_reference');
            if ($purchase->data->refId !== $payburner_purchase_reference) {
                self::error("The purchase was found on Payburner but the reference doesn't match refFound=".$purchase->data->refId);
                return false;
            }
            else {
                WC()->session->set('payburner_purchase_status', $purchase->data->status);
                self::send(array(
                    'match' => true,
                    'matched_transaction' => $ref_id,
                    'purchase_id' => $purchaseId,
                    'status' => $purchase->data->status,
                ));

            }
        }
	}



	private static function send($message = 0){
		wp_send_json_success($message);
		wp_die();
		return false;
	}

	private static function error($message = 0){
	    wp_send_json_error($message);
	    wp_die();
	    return false;
    }

}// End class AJAX

Payburner_Ajax::get_instance();