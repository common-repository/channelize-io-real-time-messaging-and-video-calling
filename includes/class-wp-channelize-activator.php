<?php
/**
 * Channelize Activator Class
 *
 * @link       https://channelize.io/
 * @package Channelize
 * @since      2.0.0
 */

defined( 'ABSPATH' ) || exit;

/** Channelize Activate Class */
class WP_Channelize_Activator {


	/**
	 * Activate Channelize
	 */
	public static function activate() {

		flush_rewrite_rules();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$channelize_userrole = get_option( 'channelize_userrole' );
		/** Save Default Settings of User Roles */
		if ( empty( $channelize_userrole ) ) {
			$default_user_role_settings = array( 'user_role_settings' => array( 'subscriber', 'contributor', 'author', 'editor', 'administrator'));
			update_option( 'channelize_userrole', $default_user_role_settings );
		}
		$channelize_status = get_option( 'channelize' );
		if ( empty( $channelize_status ) ) {
			$default_docked_view = array( 'default_load' => '1' );
			update_option( 'channelize', $default_docked_view );
		}
	}

}
