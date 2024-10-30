<?php
/**
 * Channelize Messenger Shortcode
 *
 * @link       https://channelize.io/
 * @package Channelize
 * @since      2.0.0
 */

defined( 'ABSPATH' ) || exit;

/** This view is created for load channelize using react script */

/** Some Functions for Third Party Developer's Those Developer's Can Use this functions For Using All Features oF Channelize */
$chat                 = true;
$video_voice_call     = true;
$disable_call_buttons = false;
$show_search_icon     = true;
if ( function_exists( 'channelize_get_setting_chat' ) ) {
	$chat = channelize_get_setting_chat();
}
if ( function_exists( 'channelize_get_setting_videoVoiceCall' ) ) {
	$video_voice_call = channelize_get_setting_videoVoiceCall();
}
if ( function_exists( 'channelize_get_setting_disableCallButtons' ) ) {
	$disable_call_buttons = channelize_get_setting_disableCallButtons();
}
if ( function_exists( 'channelize_get_setting_showSearchIcon' ) ) {
	$show_search_icon = channelize_get_setting_showSearchIcon();
}
if ( 'docked' === $layout ) {
	?>
	<style type="text/css">
		.ch-conv-window .ch-messages-box {
			height: calc(100% - 117px);
		}
	</style>
	<?php
} else {
	?>
	<style type="text/css">
		.ch-conv-window .ch-messages-box {
			height: calc(100% - 130px);
		}
		.ch-conv-window .ch-send-box textarea {
			padding: 10px 10px 0 20px;
		}
	</style>
	<?php
}
?>

<!-- channelize.io Chat react script -->
<body>
	<div id="root"></div>
	<script>
		window.addEventListener('DOMContentLoaded', () => {
			window.channelizeUI.render({
				publicKey: '<?php echo esc_html( $public_key ); ?>',
				userId: '<?php echo esc_html( $user_id ); ?>',
				accessToken: '<?php echo esc_html( $access_token ); ?>',
				mountDiv: document.getElementById('root'),
				settings: {
					layout: '<?php echo esc_html( $layout ); ?>',
					chat: '<?php echo esc_html( $chat ); ?>',
					videoVoiceCall: '<?php echo esc_html( $video_voice_call ); ?>',
					disableCallButtons: '<?php echo esc_html( $disable_call_buttons ); ?>',
					showSearchIcon: '<?php echo esc_html( $show_search_icon ); ?>'
				}
			});
		});
	</script>
</body>




