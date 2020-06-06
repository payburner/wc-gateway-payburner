<?php

class PayburnerApi {

	private static $instance;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function get_purchase( $buttonid, $purchaseid) {

		$path = '/v1/gateway/paybuttons/' . $buttonid . '/purchase/'.sanitize_text_field($purchaseid);

        $res = wp_remote_get('http://gateway.payburner.com'.$path);
        $res = rest_ensure_response($res);

		if(empty($res) && empty($res->status) && $res->status !== 200){
			return false;
		}
		$purchase = json_decode($res->data['body']);
		if(is_object($purchase) && !empty($purchase->data) && !empty($purchase->data->purchaseId)){
			return $purchase;
		}
		return false;
	}


}