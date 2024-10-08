<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Connect_Account_Settings {

	/**
	 * @var WC_Connect_Service_Settings_Store
	 */
	protected $settings_store;

	/**
	 * @var WC_Connect_Payment_Methods_Store
	 */
	protected $payment_methods_store;

	public function __construct(
		WC_Connect_Service_Settings_Store $settings_store,
		WC_Connect_Payment_Methods_Store $payment_methods_store
	) {
			$this->settings_store        = $settings_store;
			$this->payment_methods_store = $payment_methods_store;
	}


	public function get() {
		$payment_methods_warning = false;
		$payment_methods_success = $this->payment_methods_store->fetch_payment_methods_from_connect_server();

		if ( ! $payment_methods_success ) {
			$payment_methods_warning = __( 'There was a problem updating your saved credit cards.', 'woocommerce-services' );
		}

		$connection_owner            = WC_Connect_Jetpack::get_connection_owner();
		$connection_owner_wpcom_data = WC_Connect_Jetpack::get_connection_owner_wpcom_data();
		$last_box_id                 = get_user_meta( get_current_user_id(), 'wc_connect_last_box_id', true );
		$last_box_id                 = $last_box_id === 'individual' ? '' : $last_box_id;
		$last_service_id             = get_user_meta( get_current_user_id(), 'wc_connect_last_service_id', true );
		$last_carrier_id             = get_user_meta( get_current_user_id(), 'wc_connect_last_carrier_id', true );
		$wcshipping_migration_state  = intval( get_option( 'wcshipping_migration_state' ) );

		return array(
			'storeOptions' => $this->settings_store->get_store_options(),
			'formData'     => $this->settings_store->get_account_settings(),
			'formMeta'     => array(
				'can_manage_payments'        => $this->settings_store->can_user_manage_payment_methods(),
				'can_edit_settings'          => true,
				'master_user_name'           => $connection_owner ? $connection_owner->display_name : '',
				'master_user_login'          => $connection_owner ? $connection_owner->user_login : '',
				'master_user_wpcom_login'    => $connection_owner_wpcom_data ? $connection_owner_wpcom_data['login'] : '',
				'master_user_email'          => $connection_owner_wpcom_data ? $connection_owner_wpcom_data['email'] : '',
				'payment_methods'            => $this->payment_methods_store->get_payment_methods(),
				'add_payment_method_url'     => $this->payment_methods_store->get_add_payment_method_url(),
				'warnings'                   => array( 'payment_methods' => $payment_methods_warning ),
				'is_eligible_to_migrate'     => $this->settings_store->is_eligible_for_migration(),
				'wcshipping_migration_state' => $wcshipping_migration_state,
			),
			'userMeta'     => array(
				'last_box_id'     => $last_box_id,
				'last_service_id' => $last_service_id,
				'last_carrier_id' => $last_carrier_id,
			),
		);
	}
}
