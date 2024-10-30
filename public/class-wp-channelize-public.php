<?php
/**
 * Channelize Public Class
 *
 * @link       https://channelize.io/
 * @package Channelize
 * @since      2.0.0
 */

defined( 'ABSPATH' ) || exit;
/** WP_Channelize_Public is a public class having shortcodes widgets etc **/
class WP_Channelize_Public {
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
	 * Shortcode_rendered
	 *
	 * @var Array.
	 * @access private
	 */
	private static $shortcode_rendered = array();

	/**
	 * Initialize default constructor.
	 *
	 * @param string $plugin_name plugin name.
	 * @param string $version plugin version.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->settings    = null;
		$this->load_channelize_widget();
		$this->channelize_add_action_public();
	}
	/** Channelize_add_action_public add actions*/
	public function channelize_add_action_public() {

		/** Load channelize on every page */
		add_action( 'init', array( $this, 'channelize_default_load_shortcode' ) );

		/** Innitailize shortcode */
		add_shortcode( 'channelize-widget', array( $this, 'channelize_widget_shortcode' ) );
		/** Load channelize Script & style*/
		add_action( 'init', array( $this, 'channelize_public_enqueue_script' ) );
	}

	/** Load Channelize cdn **/
	public function channelize_public_enqueue_script() {

		wp_enqueue_script( 'channelize_ui', plugin_dir_url( __FILE__ ) . 'views/js/channelize/channelize-ui.min.js', array(), '2.0.0', false );

		$fullview = plugin_dir_url( __FILE__ ) . 'views/css/style.css';

		wp_enqueue_style( 'full_view_data_css', $fullview, '2.0.0', 'screen' );
	}
	/** Channelize_default_load_shortcode load on every request*/
	public function channelize_default_load_shortcode() {

		/** Check the Status of Docked View */
		$options_ch = get_option( 'channelize' );
		if ( isset( $options_ch['default_load'] ) ) {
			$channelize_status = $options_ch['default_load'];
			if ( ! empty( $channelize_status ) ) {
				/** Use this shortcode on every-page */
				add_filter( 'the_content', array( $this, 'render_shortcode_everypages' ) );
			}
		}
	}

	/** Channelize.io Chat widget code **/
	public function load_channelize_widget() {

		require_once 'class-wp-channelize-widget.php';
		$channelize_widget = new WP_Channelize_Widget();

	}
	/**
	 * Load Channelize.io Chat deafult on every page/post except contain shortcode.
	 *
	 * @param string $content content.
	 **/
	public function render_shortcode_everypages( $content ) {

		$new_content = $content;
		global $post;
		if ( has_shortcode( $post->post_content, esc_html( 'channelize-widget' ) ) ) {
			return $new_content;
		} else {
			$new_content .= do_shortcode( force_balance_tags( wp_kses_post( '[channelize-widget]' ) ) );
			return $new_content;
		}
	}
	/**
	 * Render channelize-widget shortcode.
	 *
	 * @param string $atts attributes.
	 **/
	public function channelize_widget_shortcode( $atts ) {

		require_once 'shortcode-channelize-widget.php';
		return render_channelize_shortcode( $this, $atts );
	}
	/**
	 * Check shortcode is already rendered.
	 *
	 * @param string $shortcode shortcode.
	 **/
	public static function is_shortcode_rendered( $shortcode ) {

		return isset( self::$shortcode_rendered[ $shortcode ] );
	}
	/**
	 * Add shortcode.
	 *
	 * @param string $shortcode shortcode.
	 **/
	public static function add_shortcode_rendered( $shortcode ) {

		self::$shortcode_rendered[ $shortcode ] = true;
	}
	/**
	 * Create a default instance of shortcode attribute layout.
	 *
	 * @param string $shortcode shortcode.
	 * @param string $attrs attribubtes.
	 *
	 */
	public function get_shortcode_attrs( $shortcode, $attrs ) {

		$instance = shortcode_atts(
			array(
				'layout' => 'docked',
			),
			$attrs,
			$shortcode
		);
		return $instance;
	}
}
