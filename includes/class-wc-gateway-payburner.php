<?php

/**
 * Payburner gateway main class based on wc
 *
 * @since 1.0.0
 *
 */
class WC_Gateway_Payburner extends WC_Payment_Gateway {

    /**
     * Logging enabled?
     *
     * @var bool
     */
    public $logging;


    function __construct() {
        $this->id = 'payburner';
        $this->method_title = __('Payburner', 'wc-gateway-payburner');
        $this->method_description = __('Payburner works by showing a paybutton and let customers pay XRP to your XRP wallet for orders in your shop.', 'wc-gateway-payburner');
        $this->has_fields = true;
        $this->icon = '/wp-content/plugins/wc-gateway-payburner/assets/img/pay_with_payburner.png';
        $this->order_button_text = "Waiting for payment";


        // Load the form fields.
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        // Get setting values.
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->buttonid = $this->get_option('buttonid');
        $this->logging = 'yes' === $this->get_option('logging');
        $this->test = 'no';

        // Hooks.
        add_action('wc_update_options_payment_gateways_' . $this->id, array(
            $this,
            'process_admin_options'
        ));
        add_action('wp_enqueue_scripts', array( $this, 'payment_scripts' ));



    }



    /**
     * Payment form on checkout page
     */
    public function payment_fields() {
        $user = wp_get_current_user();

        if ( $user->ID ) {
            $user_email = get_user_meta($user->ID, 'billing_email', true);
            $user_email = $user_email ? $user_email : $user->user_email;
        } else {
            $user_email = '';
        }

        $payburner_purchase_reference = uniqid();
        WC()->session->set('paybutton_purchase_reference', $payburner_purchase_reference);
        //Start wrapper
        echo '<div id="xrp-form"
			data-email="' . esc_attr($user_email) . '"
			data-amount="' . esc_attr($this->get_order_total()) . '"
			data-currency="' . esc_attr(strtolower(get_wc_currency())) . '"
			">';

        //Info box
        echo '<div id="payburner-description">';
        if ( $this->description ) {
            echo apply_filters( 'wc_payburner_description', wpautop( wp_kses_post( $this->description ) ) );
        }
        echo '</div>';


        echo '<div id="paybutton-payment-desc">';

        echo '<div id="paybutton-wrapper" style="display: none;">';

        echo '<pay-button allowresetanytime="false" fiatcurrency="' . esc_attr(strtolower(get_wc_currency())) . '" fiatprice="' . esc_attr($this->get_order_total()) . '" reference="' . esc_attr($payburner_purchase_reference) . '" buttonid="' . esc_attr($this->buttonid) . '"/>';

        echo '</div>';

        echo '<div id="paybutton-status">';
        echo 'Waiting for required fields to be completed.';
        echo '</div>';

        echo '</div>';

        echo '<div id="paybutton-process"></div>';

    }

    /**
     * payment_scripts function.
     *
     * Outputs scripts used for stripe payment
     *
     * @access public
     */
    public function payment_scripts() {


        wp_enqueue_script('wc_payburner_js', 'https://www.payburner.com/payburner.js', array( 'jquery' ), WC_PAYBURNER_VERSION, true);
        wp_enqueue_script('wc_button_js', 'https://unpkg.com/@payburner/paybutton.js@0.0.12/dist/pay-button.js', array( 'jquery', 'wc_payburner_js' ), WC_PAYBURNER_VERSION, true);
        wp_enqueue_script('jquery-initialize', plugins_url('assets/js/jquery.initialize.js', WC_PAYBURNER_MAIN_FILE), array( 'jquery' ), WC_PAYBURNER_VERSION, true);

        wp_enqueue_script('wc_nem_js', plugins_url('assets/js/payburner-checkout.js', WC_PAYBURNER_MAIN_FILE), array(
            'jquery',
            'jquery-initialize'
        ), WC_PAYBURNER_VERSION, true);

        //Add js variables
        $payburner_params = array(
            'wc_ajax_url' => WC()->ajax_url(),
            'nounce' => wp_create_nonce("wc-payburner"),
            'store' => get_bloginfo()
        );

        wp_localize_script('wc_nem_js', 'wc_payburner_params', apply_filters('wc_payburner_params', $payburner_params));

    }

    public function validate_fields() {
        $payburner_payment_status = WC()->session->get('payburner_purchase_status');
        if ( empty($payburner_payment_status) || $payburner_payment_status !== 'SETTLED' ) {
            wc_add_notice(__('An XRP payment has not been registered to this checkout. Please contact our support department.', 'wc-gateway-payburner'), 'error');
            return false;
        }
        return true;
    }

    /**
     * Process Payment.
     *
     *
     * @param int $order_id
     *
     * @return array
     */
    public function process_payment( $order_id ) {

        global $wc;
        $order = new WC_Order($order_id);

        $order->update_status('processing', __('Awaiting XRP payment', 'wc-gateway-payburner'));
        update_post_meta($order_id, 'paybutton_purchase_reference', WC()->session->get('paybutton_purchase_reference') );
        update_post_meta($order_id, 'paybutton_purchase_id', WC()->session->get('paybutton_purchase_id') );


        // Reduce stock levels
        $order->reduce_order_stock();

        //Mark as paid
        $order->payment_complete();

        // Remove cart
        $wc->cart->empty_cart();
        WC()->session->set('purchase_id', false);
        WC()->session->set('payburner_reference', false);
        WC()->session->set('payburner_purchase_status', false);

        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url()
        );
    }

    /**
     * Init settings for gateways.
     */
    public function init_settings() {
        parent::init_settings();
        $this->enabled = !empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'] ? 'yes' : 'no';
    }

    /**
     * Initialise Gateway Settings Form Fields
     */
    public function  init_form_fields() {
        $this->form_fields = include('wc-gateway-payburner-settings.php');

        wc_enqueue_js("
			jQuery( function( $ ) {
				
			});
		");
    }

    /**
     * Check if this gateway is enabled
     */
    public function is_available() {
        if ( 'yes' === $this->enabled && $this->buttonid ) {
            return true;
        }
        return false;
    }

}