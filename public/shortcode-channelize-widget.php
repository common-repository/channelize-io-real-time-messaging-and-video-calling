<?php
/**
 * Channelize Shortcode
 *
 * @link       https://channelize.io/
 * @package Channelize
 * @since      2.0.0
 */

defined( 'ABSPATH' ) || exit;
/**
 * Render_channelize_shortcode.
 *
 * @param string $channelize channelize-widget.
 * @param string $attrs attributes.
 */
function render_channelize_shortcode( $channelize, $attrs ) {

	/** Avoid duplicated shortcode rendered */
	if ( WP_Channelize_Public::is_shortcode_rendered( '[channelize-widget]' ) ) {
		return '<!-- Shortcode Already Rendered -->';
	}

	/** Set instance of shortcode attribute 'layout' */
	$instance = $channelize->get_shortcode_attrs( 'channelize-widget', $attrs );
	$layout   = $instance['layout'];

	/**********Validations for shortcodes check the admin settings first. */
	/** Validate the user roles settings. */
	$channelize_role_setting = get_option( 'channelize_userrole' );
	if ( ! empty( $channelize_role_setting ) ) {
		$channelize_all_settings = $channelize_role_setting['user_role_settings'];
		$user                    = wp_get_current_user();
		$user_role_setting_exist = array_intersect( $channelize_all_settings, $user->roles );
		$user_role_setting_count = count( $user_role_setting_exist );
	}

	/** Validate migration is success or not */
	$channelize_migration = get_option( 'channelize_migration' );
	if ( ! empty( $channelize_migration ) ) {
		$activate_status = $channelize_migration['activate_status'];
	}

	/** Get the public key from channelize settings */
	$options    = get_option( 'channelize' );
	$public_key = $options['public_key'];

	/** Get current login userid */
	$user_id = get_current_user_id();

	/** Get the cookies */
	if ( isset( $_COOKIE['channelize_access_token'] ) ) {
		$access_token = sanitize_text_field( wp_unslash( $_COOKIE['channelize_access_token'] ) );
	}

	if ( ! empty( $layout ) && ! empty( $user_role_setting_count ) > 0 && ! empty( $activate_status ) && ! empty( $public_key ) && ! empty( $access_token ) && ! empty( $user_id ) ) {
		ob_start();
		/** Add the channelize script in view file. */
		require_once 'views/wp-channelize-shortcode.php';
		$out = ob_get_clean();
	} else {
		$out = '<!-- channelize.io: No channel found! -->';
	}

	WP_Channelize_Public::add_shortcode_rendered( '[channelize-widget]' );
	return $out;
}
