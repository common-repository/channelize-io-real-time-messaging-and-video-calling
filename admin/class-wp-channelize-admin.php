<?php
/**
 * Channelize Admin Class
 *
 * @link       https://channelize.io/
 * @package Channelize
 * @since      2.0.0
 */

defined( 'ABSPATH' ) || exit;

use PrimeMessenger\Primemessenger\Usermigration;

/** Initialize the class and set its properties **/
class WP_Channelize_Admin {
	/**
	 * Plugin_name
	 *
	 * @var string
	 * @access private
	 */
	private $plugin_name;
	/**
	 * Version
	 *
	 * @var string
	 * @access private
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name plugin name.
	 * @param string $version plugin version.
	 **/
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->intialize_admin_actions();
		$this->download_migration_file();
	}

	/** Admin Actions **/
	public function intialize_admin_actions() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu_pages' ) );
		add_action( 'admin_init', array( $this, 'initialize_settings' ) );
		add_action( 'wp_ajax_create_tar_from_users_data', array( $this, 'create_tar_from_users_data' ) );
		add_action( 'wp_ajax_nopriv_create_tar_from_users_data', array( $this, 'create_tar_from_users_data' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_js_files' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_css_files' ) );
	}

	/** Download user migration file **/
	public function download_migration_file() {

		if ( isset( $_POST['download_migration_tar'] ) && ! empty( $_SERVER['HTTP_HOST'] ) ) {
			$website_domain           = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
			$tar_name                 = 'channelize-' . $website_domain . '.tar';
			$update_user_cookie_value = '2';
			setcookie( 'channelize_migration_step_status', $update_user_cookie_value, time() + ( 365 * 24 * 60 * 60 ), '/' );

			if ( ! defined( 'WP_TEMP_DIR' ) ) {
				$archive_file_name = WP_CONTENT_DIR . '/temp/' . $tar_name;
			} else {
				$archive_file_name = get_temp_dir() . $tar_name;
			}
			header( 'Content-type: application/tar' );
			header( 'Pragma: public' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Cache-Control: public' );
			header( 'Content-Description: File Transfer' );
			header( 'Content-type: application/octet-stream' );
			header( "Content-Disposition: attachment; filename=channelize-$website_domain.tar" );
			header( 'Content-length: ' . filesize( $archive_file_name ) );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
			readfile( "$archive_file_name" );
		}
	}

	/** By Default Load Admin Styles **/
	public function load_css_files() {
		$file_path = plugin_dir_url( __FILE__ ) . 'views/css/toggle.css';
		wp_enqueue_style( 'toggel_option_css', $file_path, array(), '2.0.0', 'all' );
	}

	/** By Default Load Admin Script **/
	public function load_js_files() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ajax.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			$this->plugin_name,
			'channelize_ajax_url',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}


	/** Create json & tar file of user migration data **/
	public function create_tar_from_users_data() {

		if ( isset( $_POST['user_checked_status'] ) ) {

			$user_checked_status = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['user_checked_status'] ) ) );
			$user_all_data       = get_users();
			$total_user_count    = count( $user_all_data );
			$user_migration_lib  = new Usermigration();
			require_once plugin_dir_path( __FILE__ ) . 'class-wp-channelize-compressor.php';
			$lib_tar_obj = new WP_Channelize_Compressor();

			for ( $i = 0;$i < $total_user_count;$i++ ) {
				$user_data[ $i ]['id']              = $user_all_data[ $i ]->data->ID;
				$user_data[ $i ]['email']           = $user_all_data[ $i ]->data->user_email;
				$user_data[ $i ]['displayName']     = $user_all_data[ $i ]->data->display_name;
				$user_data[ $i ]['profileImageUrl'] = get_avatar_url( $user_all_data[ $i ]->data->ID );
				$user_data[ $i ]['profileUrl']      = get_author_posts_url( $user_all_data[ $i ]->data->ID );
				$user_data[ $i ]['createdAt']       = $this->channelize_convert_date_time_format( $user_all_data[ $i ]->data->user_registered );

			}

			$migation_json_encode_data = $user_migration_lib->channelize_migration_data( $user_data );
			$generated_name            = 'User.json';
			if ( ! defined( 'WP_TEMP_DIR' ) ) {
				$upload_temp_file_path = WP_CONTENT_DIR . '/temp/';
			} else {
				$upload_temp_file_path = get_temp_dir();
			}
			if ( $this->upload_temp_folder_exists( $upload_temp_file_path ) ) {
				if ( ! $this->create_temp_folder( $upload_temp_file_path ) ) {
					$response = array(
						'status'   => 'fail',
						'response' => 'Unable to create temp directory',
					);
					echo wp_json_encode( $response );
					wp_die();
				}
			}
			$upload_file_name_with_path = $upload_temp_file_path . $generated_name;
			if ( ! $this->migration_user_data_write_json_file( $upload_temp_file_path, $upload_file_name_with_path, $migation_json_encode_data ) ) {
				$response = array(
					'status'   => 'fail',
					'response' => 'Do not have write permission on temp directory',
				);
				echo wp_json_encode( $response );
				wp_die();
			}
			if ( isset( $_SERVER['HTTP_HOST'] ) ) {
				$website_domain = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ); }
			$tar_name = 'channelize-' . $website_domain . '.tar';
			$tar_path = $upload_temp_file_path . $tar_name;
			if ( file_exists( $tar_path ) ) {
				unlink( $tar_path ); }
			$create_tar        = new PharData( $tar_path );
			$create_tar_status = $create_tar->buildFromDirectory( $upload_temp_file_path );
			if ( ! $create_tar_status ) {
				$response = array(
					'status'   => 'fail',
					'response' => 'Tar Not Created Successfully',
				);
			} else {
				setcookie( 'channelize_migration_step_status', '1', time() + ( 365 * 24 * 60 * 60 ), '/' );
				$response = array(
					'status'   => 'success',
					'response' => 'Tar Created Successfully',
				);
			}
			echo wp_json_encode( $response );
		}
		wp_die();
	}
	/**
	 * Channelize_convert_date_time_format for convert format of date time
	 *
	 * @param datetime $date_time datetime.
	 *
	 * @return datetime.
	 */
	public function channelize_convert_date_time_format( $date_time ) {
		return gmdate( 'Y-m-d', strtotime( $date_time ) ) . 'T' . gmdate( 'h:i:s', strtotime( $date_time ) ) . '+00:00';
	}
	/**
	 * Create_temp_folder for temprary dir
	 *
	 * @param string $upload_temp_file_path file path.
	 *
	 * @return true
	 */
	public function create_temp_folder( $upload_temp_file_path ) {
		if ( mkdir( $upload_temp_file_path, 0777 ) ) {
			return true;
		}
	}
	/**
	 * Upload_temp_folder_exists for check temprary dir exists
	 *
	 * @param string $upload_temp_file_path file path.
	 *
	 * @return true
	 */
	public function upload_temp_folder_exists( $upload_temp_file_path ) {
		if ( ! is_dir( $upload_temp_file_path ) ) {
			return true;
		}
	}
	/**
	 * Migration_user_data_write_json_file
	 *
	 * @param string $upload_temp_file_path file path.
	 *
	 * @param string $upload_file_name_with_path file name with path.
	 *
	 * @param string $migation_json_encode_data json data.
	 *
	 * @return true
	 */
	public function migration_user_data_write_json_file( $upload_temp_file_path, $upload_file_name_with_path, $migation_json_encode_data ) {

		if ( is_writable( $upload_temp_file_path ) ) {
			file_put_contents( $upload_file_name_with_path, $migation_json_encode_data );
			return true;}
	}

	/** Register Admin Menu **/
	public function register_admin_menu_pages() {

		global $_wp_last_object_menu;
		$_wp_last_object_menu++;
		add_menu_page( 'Chanelize.io Chat', 'Chanelize.io Chat', 'manage_options', 'channelize-settings', 'channelize_general_settings_page', 'dashicons-format-chat' );
		add_submenu_page( 'channelize-settings', 'General', 'General', 'manage_options', 'channelize-settings' );
		add_submenu_page( 'channelize-settings', 'Migration', 'Migration', 'manage_options', 'channelize_migraton_settings', 'channelize_migraton_settings_page' );
		add_submenu_page( 'channelize-settings', 'User Role Setting', 'User Role Setting', 'manage_options', 'channelize_user_role_settings', 'channelize_user_role_settings_page' );
	}
	/** Innitalize admin settings **/
	public function initialize_settings() {

		register_setting( 'channelize', 'channelize' );
		add_settings_section(
			'channelize_settings_section',
			__( '<div class="wrap"><h1>General Settings</h1></div>', 'channelize' ),
			'channelize_settings_section_callback',
			'channelize'
		);
		add_settings_field(
			'public_key',
			__( 'Public Key', 'channelize' ),
			'public_key_render',
			'channelize',
			'channelize_settings_section'
		);

		add_settings_field(
			'private_key',
			__( 'Private Key', 'channelize' ),
			'private_key_render',
			'channelize',
			'channelize_settings_section'
		);

		// add_settings_field(
		// 'chat_settings',
		// __( '<h2>Chat View Setting</h2>', 'channelize' ),
		// 'chat_settings_render',
		// 'channelize',
		// 'channelize_settings_section'
		// );

		add_settings_field(
			'Docked_View',
			__( 'Enable Docked View Chat', 'channelize' ),
			'default_load',
			'channelize',
			'channelize_settings_section'
		);

		register_setting( 'channelize_migration', 'channelize_migration' );

		add_settings_section(
			'channelize_migration_settings_section',
			__( 'Channelize.io Chat Enable/Disable Setting', 'channelize_migration' ),
			'channelize_settings_migration_callback',
			'channelize_migration'
		);
		add_settings_field(
			'What_all_to_migrate',
			__( 'Enable/Disable', 'channelize_migration' ),
			'channelize_migration_form_callback',
			'channelize_migration',
			'channelize_migration_settings_section'
		);
		register_setting( 'channelize_userrole', 'channelize_userrole' );

		add_settings_section(
			'channelize_userrole_settings_section',
			__( 'User Role Settings', 'channelize_userrole' ),
			'channelize_userrole_section_callback',
			'channelize_userrole'
		);

		add_settings_field(
			'user_role_settings',
			__( 'Enable Chat for following user roles', 'channelize_userrole' ),
			'Enable_chat_user_roles_render',
			'channelize_userrole',
			'channelize_userrole_settings_section'
		);
		/** Docked view setting callback function **/
		function default_load() {

			$options             = get_option( 'channelize' );
			$default_load_status = isset( $options['default_load'] ) ? $options['default_load'] : '';
			?>
			<label class="switch">
			<?php echo '<input id="cmn-toggle-1" class="cmn-toggle cmn-toggle-round" type="checkbox" value="1" name="channelize[default_load]" ' . checked( 1, $default_load_status, false ) . '>'; ?><span class="slider round"></span></label>
			<br><br>
			<?php
			$channelize_docked_path = plugin_dir_url( __FILE__ ) . 'screenshots/docked.png';
			$channelize_full_path   = plugin_dir_url( __FILE__ ) . 'screenshots/full.png';
			?>
			<p class="description" ><a href='<?php echo esc_attr( $channelize_docked_path ); ?>'  target='_blank'>Docked view</a>
			<?php esc_html_e( ' is enable by default. To enable ', 'channelize' ); ?><a href='<?php echo esc_attr( $channelize_full_path ); ?>'  target='_blank'>full view</a><?php esc_html_e( ', add Channelize.io widget or shortcodes. ', 'channelize' ); ?><a href='<?php echo esc_attr_e( 'http://help.channelize.io/61-65--enable-chat-using-shortcodes-and-widget' ); ?>'  target='_blank'>Learn more about adding widget and shortcodes.</a></p>
			<?php
		}
		/** Public key setting callback function **/
		function public_key_render() {

			$options    = get_option( 'channelize' );
			$public_key = isset( $options['public_key'] ) ? $options['public_key'] : '';
			?>
			<input required type='text' name='channelize[public_key]' value='<?php echo esc_html( $public_key ); ?>'>
			<?php
		}
		/** Private key setting callback function **/
		function private_key_render() {
			$options                   = get_option( 'channelize' );
			$private_key               = isset( $options['private_key'] ) ? $options['private_key'] : '';
			$channelize_dashboard_path = plugin_dir_url( __FILE__ ) . 'screenshots/Overview.png';
			?>
			<input required type='text' name='channelize[private_key]' value='<?php echo esc_html( $private_key ); ?>'>
			<br><br>
			<?php
			esc_html_e( 'Get your Public and Private Key from Channelize.io Dashboard', 'channelize' );
			?>
			<a href='<?php echo esc_attr( $channelize_dashboard_path ); ?>'  target='_blank'>Overview page.</a>
			<?php
		}
		/** Channelize setting func*/
		function channelize_settings_migration_callback(){}
		/** Chat_settings_render func*/
		function chat_settings_render(){}
		/** Channelize_settings_section_callback func*/
		function channelize_settings_section_callback() {}

		/** Callback function of general settings **/
		function channelize_general_settings_page() {
			settings_errors();
			?>
			<form action='options.php' method='post'>
				<?php
				settings_fields( 'channelize' );
				do_settings_sections( 'channelize' );
				submit_button();
				?>
			</form>
			<?php
			esc_html_e( 'A detailed guide to configuring Channelize.io Chat Plugin.', 'channelize' );
			?>
			<a href='<?php esc_attr_e( 'http://help.channelize.io/', 'channelize' ); ?>'  target='_blank'>Click here.</a>
			<?php
		}
		/** Callback function of migration tab activation setting **/
		function channelize_migration_form_callback() {
			$options              = get_option( 'channelize_migration' );
			$activate_status_test = isset( $options['activate_status'] ) ? $options['activate_status'] : '';
			?>
			<label class="switch">
				<?php echo '<input id="cmn-toggle-1" class="cmn-toggle cmn-toggle-round" type="checkbox" value="1" name="channelize_migration[activate_status]" ' . checked( 1, $activate_status_test, false ) . '>'; ?>
				<span class="slider round"></span>
			</label>
			<?php
		}
		/** Migration View Load **/
		function channelize_migraton_settings_page() {

			$user_id = get_current_user_id();
			include_once 'views/wp-channelize-migration-view.php';
			channelize_migration_load_tab_css_js();
		}
		/** Required css/js for Channelize.io chat migration view tabs **/
		function channelize_migration_load_tab_css_js() {

			$channelize_migration_tab_style = plugin_dir_url( __FILE__ ) . 'views/css/channelize-migration-tab.css';

			wp_enqueue_style( 'channelize_migration_tab_style', $channelize_migration_tab_style, array(), '2.0.0', 'all', true );

			$channelize_migration_tab_script = plugin_dir_url( __FILE__ ) . 'js/channelize-migration-tab.js';

			wp_enqueue_script( 'channelize_migration_tab_script', $channelize_migration_tab_script, array(), '2.0.0', 'all', true );

		}
		/** User role settings callback func **/
		function channelize_user_role_settings_page() {
			settings_errors();
			?>
			<form action='options.php' method='post'>
				<?php
				settings_fields( 'channelize_userrole' );
				do_settings_sections( 'channelize_userrole' );
				submit_button();
				?>
			</form>
			<p class="ch-detailed-desc">
			<?php
			esc_html_e( 'A detailed guide to configuring Channelize.io Chat Plugin.', 'channelize' );
			?>
			<a href='<?php esc_attr_e( 'http://help.channelize.io/', 'channelize' ); ?>'  target='_blank'>Click here.</a></p>
			<?php
		}
		/** Userrole callback func **/
		function channelize_userrole_section_callback() { }
		/** Required css for multiselect userroles setting **/
		function load_multiselect_enqueue_style() {

			$channelize_userrole_multiselect_style = plugin_dir_url( __FILE__ ) . 'views/css/channelize-user-role-multi-select-style.css';

			wp_enqueue_style( 'channelize_userrole_multiselect_style', $channelize_userrole_multiselect_style, array(), '2.0.0', 'all', true );

			$channelize_userrole_multiselect_botsrp_style = plugin_dir_url( __FILE__ ) . 'views/css/channelize-user-role-multi-select-bootstrap.css';

			wp_enqueue_style( 'channelize_userrole_multiselect_botsrp_style', $channelize_userrole_multiselect_botsrp_style, array(), '2.0.0', 'all', true );

		}
		/** Required js for multiselect userroles setting **/
		function load_multiselect_enqueue_script() {

			$channelize_multiselect_botstrp_script = plugin_dir_url( __FILE__ ) . 'js/channelize-multi-select-bootstrap.js';

			wp_enqueue_script( 'channelize_multiselect_botstrp_script', $channelize_multiselect_botstrp_script, array(), '2.0.0', 'all', true );

			$channelize_multiselect_select_script = plugin_dir_url( __FILE__ ) . 'js/channelize-multi-select.js';

			wp_enqueue_script( 'channelize_multiselect_select_script', $channelize_multiselect_select_script, array(), '2.0.0', 'all', true );

		}
		/** Userrole setting view  **/
		function Enable_chat_user_roles_render() {

			$channelize_userrole = get_option( 'channelize_userrole' );
			if ( isset( $channelize_userrole['user_role_settings'] ) ) {
				$channelize_userrole_data         = $channelize_userrole['user_role_settings'];
				$channelize_userrole_combine_dobl = wp_json_encode( $channelize_userrole_data );
				$channelize_userrole_combine      = str_replace( '"', "'", $channelize_userrole_combine_dobl );
			}
			load_multiselect_enqueue_style();
			load_multiselect_enqueue_script();
			?>
			<table class="form-table">
				<tr>
				<input type="text" class="multi-select-userrole" value="<?php echo esc_html( $channelize_userrole_combine ); ?>">
				<select class="selectpicker"  data-live-search="true" multiple="multiple" name="channelize_userrole[user_role_settings][] " id="channelize_userrole_set">
					<?php
					$editable_roles = array_reverse( get_editable_roles() );
					foreach ( $editable_roles as $role => $details ) {
						?>
							<option value="<?php echo esc_html( $role ); ?>" >
								<?php
								echo esc_html( $role );
								?>
							</option>
						<?php
					}
					?>
				</select>
				</tr>
			</table>
			<?php
			echo esc_html( __( 'Choose for which all user roles you want to enable chat.' ) );
		}
		/**  FAQ settings callback func.**/
		function channelize_FAQ_settings_page(){ }
	}
}

