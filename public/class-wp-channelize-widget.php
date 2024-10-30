<?php
/**
 * Channelize Messenger Widget
 *
 * @link       https://channelize.io/
 * @package Channelize
 * @since      2.0.0
 */

defined( 'ABSPATH' ) || exit;
/** WP_Channelize_Widget class having functionality of Channelize.io Chat widget */
class WP_Channelize_Widget extends WP_Widget {
	/** Constructer.*/
	public function __construct() {
		parent::__construct(
		/** Base ID of your widget */
			'channelize_widget',
			/** Widget name will appear in UI */
			__( 'Channelize.io Chat Widget', 'channelize_widget_domain' ),
			/** Widget description */
			array( 'description' => __( 'channelize.io widget', 'channelize_widget_domain' ) )
    	);
		add_action( 'widgets_init', array( $this, 'wpb_load_widget' ) );
	}
	/**
	 * Creating widget front-end.
	 *
	 * @param string $args arguments.
	 * @param string $instance instance.
	 **/
	public function widget( $args, $instance ) {

		$title         = apply_filters( 'widget_title', $instance['title'] );
		$layout_status = apply_filters( 'widget_layout_status', $instance['layout_status'] );
		echo do_shortcode( force_balance_tags( wp_kses_post( '[channelize-widget layout=' . $layout_status . ']' ) ) );
		if ( ! empty( $title ) ) {
			?>
			<h2 class="widget-title subheading heading-size-3"><?php echo esc_html( $title ); ?> </h2>
            <?php
		}
	}

	/**
	 * Widget Backend.
	 *
	 * @param instance $instance instance.
	 **/
	public function form( $instance ) {

		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'Channelize.io Chat', 'channelize_widget_domain' );
		}
		if ( isset( $instance['layout_status'] ) ) {

			$layout_status = $instance['layout_status'];
		} else {
			$layout_status = __( 'docked', 'channelize_widget_domain' );
		}
		/** Widget admin form */
		?>
		<p>
		<label for="<?php echo esc_html( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo esc_html( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_html( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_html( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo esc_html( $this->get_field_id( 'layout_status' ) ); ?>"><?php esc_html_e( 'Layout: (full/docked)' ); ?></label> 
		<select class="widefat" id="<?php echo esc_html( $this->get_field_id( 'layout_status' ) ); ?>" name="<?php echo esc_html( $this->get_field_name( 'layout_status' ) ); ?>" >
			<option value="docked" <?php selected( 'docked', $layout_status ); ?>>Docked</option>
			<option value="full" <?php selected( 'full', $layout_status ); ?>>Full</option>
		</select>
		</p>
		<?php
	}
	/**
	 * Updating widget replacing old instances with new.
	 *
	 * @param instance $new_instance instance.
	 * @param instance $old_instance old instance.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance                  = array();
		$instance['title']         = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['layout_status'] = ( ! empty( $new_instance['layout_status'] ) ) ? wp_strip_all_tags( $new_instance['layout_status'] ) : '';

		return $instance;
	}
	/** Register and load the widget **/
	public function wpb_load_widget() {

		register_widget( 'WP_Channelize_Widget' );
	}
	/** Class channelize_widget ends here */
}



