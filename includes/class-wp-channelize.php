<?php
/**
 * Channelize Class
 *
 * @link       https://channelize.io/
 * @package Channelize
 * @since      2.0.0
 */

defined( 'ABSPATH' ) || exit;

use PrimeMessenger\Primemessenger\User;
use PrimeMessenger\Primemessenger\Client;
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://channelize.io/
 * @since      2.0.0
 *
 * @package    WP_Channelize
 * @subpackage channelize/includes
 */
class WP_Channelize {

	/**
	 * Plugin_name
	 *
	 * @var string
	 * @access protected
	 */
	protected $plugin_name;

	/**
	 * Version
	 *
	 * @var string
	 * @access protected
	 */
	protected $version;

	/**
	 * Plugin_admin
	 *
	 * @var string
	 * @access private
	 */
	private $plugin_admin;
	/**
	 * Plugin_public
	 *
	 * @var string
	 * @access public
	 */
	public $plugin_public;


	/** Initialize the core functionality of the plugin.**/
	public function __construct() {

		if ( defined( 'WP_CHANNELIZE_VERSION' ) ) {
			$this->version = WP_CHANNELIZE_VERSION;
		} else {
			$this->version = '2.0.0';
		}
		$this->plugin_name = 'channelize';
		$this->load_dependencies();
		$this->initialize_dependencies();
		$this->initialize_channelize_user_actions();
	}

	/** Load some required classes **/
	private function load_dependencies() {
		/** Admin Settings */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-channelize-admin.php';
		/** Widgets, shortcodes, and public templates */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-channelize-public.php';
		/** Channelize.io PHP library */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'libraries/channelize-php/vendor/autoload.php';
	}

	/** Create objects of required class **/
	private function initialize_dependencies() {
		$this->plugin_admin  = new WP_Channelize_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->plugin_public = new WP_Channelize_Public( $this->get_plugin_name(), $this->get_version() );
	}

	/** Load all default actions **/
	public function initialize_channelize_user_actions() {
		$config = get_option( 'channelize' );
		if ( ! empty( $config['private_key'] ) ) {
			add_action( 'init', array( $this, 'channelize_user_sync_login' ), 10, 2 );
			add_action( 'wp_login', array( $this, 'channelize_user_login' ), 10, 2 );
			add_action( 'clear_auth_cookie', array( $this, 'channelize_user_logout' ), 10 );
			add_action( 'user_register', array( $this, 'channelize_user_register' ) );
			add_action( 'profile_update', array( $this, 'channelize_wp_profile_update' ), 10, 2 );
			add_action( 'delete_user', array( $this, 'channelize_user_delete' ) );
		}	
	}

	/** In every single request we check cookies is exists or not because if any single case current login user is not loggedin in Channelize.io Chat  then this request logged in again **/
	public function channelize_user_sync_login() {
		$user_data  = wp_get_current_user();
		$user_login = $user_data->user_login;
		try {
			if ( ! isset( $_COOKIE['channelize_access_token'] ) && is_user_logged_in() ) {
				$this->channelize_user_login( $user_login, $user_data );
			}
		} catch ( \Throwable $th ) {
			if ( WP_DEBUG === true ) {
				error_log( print_r( $th->getMessage(), true ) );
			}
		}
	}

	/**
	 * While WordPress user loggedin then send a request to channelize for login.
	 *
	 * @param int   $user_login userid.
	 *
     * @param array $user array.
	 **/
	public function channelize_user_login( $user_login, $user ) {

		try {
			$config             = get_option( 'channelize' );
			Client::$privateKey = $config['private_key'];
			$user_api           = new User();
			$user_data          = array(
				'userId' => (string) $user->ID,
			);
			$res                = $user_api->createAccessToken( $user_data );
			$data               = json_decode( $res->getBody() );
			$access_token       = $data->id;

			setcookie( 'channelize_access_token', $access_token, time() + ( 365 * 24 * 60 * 60 ), '/' );
			$_COOKIE['channelize_access_token'] = $access_token;
		} catch ( \Throwable $th ) {
			if ( WP_DEBUG === true ) {
				error_log( print_r( $th->getMessage(), true ) );
			}
			$get_expection_class = get_class( $th );
			$no_record_found     = 'NoRecordFoundException';
			if ( strpos( $get_expection_class, $no_record_found ) !== false ) {
				$this->channelize_user_register( $user->ID );
				$this->channelize_user_login( $user_login, $user );

			}
		}
	}

	/**
	 * Logout user from channelize.io Chat.
	 *
	 */
	public function channelize_user_logout() {
		$channelize_access_token = null;
		if ( ! empty( sanitize_text_field( wp_unslash( $_COOKIE['channelize_access_token'] ) ) ) ) {
			$channelize_access_token = sanitize_text_field( wp_unslash( $_COOKIE['channelize_access_token'] ) );
		}
		try {
			$config             = get_option( 'channelize' );
			Client::$privateKey = $config['private_key'];
			Client::$userId     = get_current_user_id();
			$user_api           = new User();
			$access_token       = $channelize_access_token;
			$logout_data        = array(
				'deviceId'    => '',
				'accessToken' => $access_token,
			);
			$user_api->logout( $logout_data );
		} catch ( \Throwable $th ) {
			if ( WP_DEBUG === true ) {
				error_log( print_r( $th->getMessage(), true ) );
			}

		}
		setcookie( 'channelize_access_token', '', time() - 3600, '/' );
	}

	/**
	 * While user is created in WordPress then send a request to Channelize.io Chat for add user.
	 *
	 * @param int $user_id integer.
	 */
	public function channelize_user_register( $user_id ) {

		try {
			$user      = get_userdata( $user_id );
			$user_data = array(
				'id'              => $user->ID,
				'displayName'     => $user->display_name,
				'email'           => $user->user_email,
				'profileUrl'      => get_author_posts_url( $user_id ),
				'profileImageUrl' => get_avatar_url( $user_id ),
			);

			$config             = get_option( 'channelize' );
			Client::$privateKey = $config['private_key'];
			$user_api           = new User();
			$user_api->create( $user_data );
		} catch ( \Throwable $th ) {
			if ( WP_DEBUG === true ) {
				error_log( print_r( $th->getMessage(), true ) );
			}
		}
	}

	/**
	 * While user update his/her profile in WordPress then send a request to channelize.io  Chat regarding update.
	 *
	 * @param int   $user_id integer.
	 * @param array $old_user_data array.
	 */
	public function channelize_wp_profile_update( $user_id, $old_user_data ) {

		$user        = get_userdata( $user_id );
		$fields      = array(
			'user_email'   => 'email',
			'display_name' => 'displayName',
		);
		$update_data = array();
		foreach ( $fields as $wp_field => $ch_field ) {
			if ( $old_user_data->$wp_field !== $user->$wp_field ) {
				$update_data[ $ch_field ] = $user->$wp_field;
			}
		}
		if ( count( $update_data ) ) {
			$this->channelize_user_update( $user_id, $update_data );
		}
	}
	/**
	 * While user is deleted in WordPress then send a request to Channelize.io Chat regarding delete user
	 *
	 * @param int $user_id integer.
	 */
	public function channelize_user_delete( $user_id ) {

		try {
			$config             = get_option( 'channelize' );
			Client::$privateKey = $config['private_key'];
			$user_api           = new User( $user_id );
			$user_api->delete();
		} catch ( \Throwable $th ) {
			if ( WP_DEBUG === true ) {
				error_log( print_r( $th->getMessage(), true ) );
			}
		}
	}
	/**
	 * Send update request to Channelize.io Chat
	 *
	 * @param int   $user_id integer.
	 *
	 * @param array $user_data associate array.
	 */
	public function channelize_user_update( $user_id, $user_data ) {

		try {
			$config             = get_option( 'channelize' );
			Client::$privateKey = $config['private_key'];
			$user_api           = new User( $user_id );
			$user_api->update( $user_data );
		} catch ( \Throwable $th ) {
			if ( WP_DEBUG === true ) {
				error_log( print_r( $th->getMessage(), true ) );
			}
		}
	}

	/** Get plugin name **/
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/** Retrieve the version number of the plugin **/
	public function get_version() {
		return $this->version;
	}

}
