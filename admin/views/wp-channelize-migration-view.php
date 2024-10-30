<?php
/**
 * Channelize Admin Migration Settings
 *
 * @link       https://channelize.io/
 * @package Channelize
 * @since      2.0.0
 */

defined( 'ABSPATH' ) || exit;
settings_errors();
$options                = get_option( 'channelize_migration' );
$check_migration_status = isset( $options['activate_status'] ) ? $options['activate_status'] : '';
?>
<?php if ( ! empty( $check_migration_status ) ) { ?>
<div class="tab-pane" id="ch-final-msg">
	<p class="complete-migration"><?php echo esc_html( __( 'Channelize.io Chat is now active on your Website. Enjoy Channelizing.', 'channelize' ) ); ?> </p>

</div>
	<?php
} else {
	?>
<div class="ch-wrap-migration"><?php echo esc_html( __( 'Migration Settings', 'channelize' ) ); ?></div>
<div id="short-desc"><?php echo esc_html( __( 'To allow communication b/w two users first migrate them to your Channelize.io services. ', 'channelize' ) ); ?><a href='<?php esc_attr_e( 'http://help.channelize.io/61-64--migrate--activate-channelize-io-services', 'channelize' ); ?>'  target='_blank'>Learn more about migration steps</a></div>
<!-- create nav -->
<nav>
<div class="nav nav-tabs" id="nav-tab" role="tablist">
	<a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true"><?php echo esc_html( __( 'Create Tar', 'channelize' ) ); ?></a>
	<a class="nav-item nav-link" id="channelize-download-tar-tab" data-toggle="tab" href="#channelize-download-tar" role="tab" aria-controls="channelize-download-tar" aria-selected="false"><?php echo esc_html( __( 'Download Tar', 'channelize' ) ); ?></a>
	<a class="nav-item nav-link" id="activate-channelize-migration-tab" data-toggle="tab" href="#activate-channelize-migration" role="tab" aria-controls="activate-channelize-migration" aria-selected="false"><?php echo esc_html( __( 'Enable Chat', 'channelize' ) ); ?></a>
</div>
</nav>
<div class="tab-content" >
<!-- create Tar -->
<div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">  
	<!-- form for start migration activity -->
	<form method='post'  >
	<table class="form-table">
		<tr>
		<th scope="row" class="tar-desc"><?php echo esc_html( __( 'What all you want to migrate to your Channelize.io chat application?', 'channelize' ) ); ?>
		</th>
		<td><input type="checkbox"  checked id="Users-migration-status" ><?php echo esc_html( __( 'Users', 'channelize' ) ); ?></td>
		</tr>
		<tr>
		<th id="tab-btn-migration" ><input type="button" value="<?php echo esc_html( __( 'Next', 'channelize' ) ); ?>" id="create-tar-migration" class="button button-primary tar-btn" ></th>
		</tr>
	</table>
	</form>
</div>
<!-- Download Tar -->
<div class="tab-pane fade" id="channelize-download-tar" role="tabpanel" aria-labelledby="channelize-download-tar-tab" > 
<div id="channelize-download-tar-sub" class="migration-tab-2" >
<form method="post" >
	<table class="form-table">
<tr>
	<th scope="row" >
		<?php echo esc_html( __( 'Note:- A .Tar file is created of all your users data to get them migrated to your Channelize.io chat application. Share this file with Channelize.io support team at support@channelize.io.', 'channelize' ) ); ?></th>
	</tr>	  
	<tr>
		<th scope="row" id="tab-btn-migration-2" > <input type="submit" value="<?php echo esc_html( __( 'Download Tar', 'channelize' ) ); ?>" name="download_migration_tar"  id="toggle-my-form" class="button-primary" ></th>
		</tr>
		</table>
	</form>
	</div>
</div>
<!-- Enable chat -->
<div class="tab-pane fade" id="activate-channelize-migration" role="tabpanel" aria-labelledby="activate-channelize-migration-tab" >
<form action='options.php' method='post'>
	<?php
		settings_fields( 'channelize_migration' );
		do_settings_sections( 'channelize_migration' );
		submit_button();}
		?>
		<p class="ch-detailed-desc"><?php esc_html_e( 'A detailed guide to configuring Channelize.io Chat Plugin.', 'channelize' ); ?>
		<a href='<?php esc_attr_e( 'http://help.channelize.io/', 'channelize' ); ?>'  target='_blank'>Click here.</a></p>
	</form>
</div>
</div>
