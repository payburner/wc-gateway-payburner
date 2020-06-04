<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return apply_filters( 'wc_payburner_settings',
	array(
		'enabled' => array(
			'title'       => __( 'Enable/Disable', 'wc-gateway-payburner' ),
			'label'       => __( 'Enable XRP payments', 'wc-gateway-payburner' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no'
		),
		'title' => array(
			'title'       => __( 'Title', 'wc-gateway-payburner' ),
			'type'        => 'text',
			'description' => __( 'This controls the title which the user sees during checkout.', 'wc-gateway-payburner' ),
			'default'     => __( 'XRP (Digital currency)', 'wc-gateway-payburner' ),
			'desc_tip'    => true,
		),
		'description' => array(
			'title'       => __( 'Description', 'wc-gateway-payburner' ),
			'type'        => 'text',
			'description' => __( 'This controls the description which the user sees during checkout. Leave it empty and it will not show.', 'wc-gateway-payburner' ),
			'default'     => __( 'Pay with XRP.', 'wc-gateway-payburner'),
			'desc_tip'    => true,
		),
		'buttonid' => array(
			'title'       => __( 'Payburner Button Id', 'wc-gateway-payburner' ),
			'type'        => 'text',
			'description' => __( 'Input the Payburner buttonid where you want customers to pay XRP to.', 'wc-gateway-payburner' ),
			'default'     => '',
			'placeholder' => '7b2d5583-a178-434d-8048-516f784f8f92',
			'desc_tip'    => true,
		),
		'logging' => array(
			'title'       => __( 'Logging', 'wc-gateway-payburner' ),
			'label'       => __( 'Log debug messages', 'wc-gateway-payburner' ),
			'type'        => 'checkbox',
			'description' => __( 'Save debug messages to the wc System Status log.', 'wc-gateway-payburner' ),
			'default'     => 'no',
			'desc_tip'    => true,
		)
	)
);