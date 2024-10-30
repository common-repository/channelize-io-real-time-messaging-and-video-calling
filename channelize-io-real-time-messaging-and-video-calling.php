<?php
/**
 * The WP Integration plugin for Channelize.io platform
 *
 * @link              https://channelize.io/
 * @since             2.0.0
 * @package           WP_Channelize
 *
 * Plugin Name:       Channelize.io - Real-time Messaging and Video Calling
 * Plugin URI:        N/A
 * Description:       Channelize.io allows you to quickly integrate a beautiful modern chat & high quality calling experience into your website in just a few clicks. You can easily customize it to craft a chat interface of their choice. It's best suited for community and social networking websites.
 * Version:           2.0.0
 * Author:            Channelize.io
 * Author URI:        https://channelize.io/
 * Text Domain:       channelize
 */

defined( 'ABSPATH' ) || exit;

define( 'WP_CHANNELIZE_VERSION', '2.0.0' );

require plugin_dir_path( __FILE__ ) . 'includes/class-wp-channelize.php';
$wp_channelize_plugin = new WP_Channelize();

/** Plugin Activation Code **/
function wp_channelize_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-channelize-activator.php';
	WP_Channelize_Activator::activate();
}

/** Plugin Deactivation Code **/
function wp_channelize_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-channelize-deactivator.php';
	WP_Channelize_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'wp_channelize_activate' );
register_deactivation_hook( __FILE__, 'wp_channelize_deactivate' );

