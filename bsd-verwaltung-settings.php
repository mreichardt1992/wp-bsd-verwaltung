<?php


//Add settings to menu
add_action( 'admin_menu', 'bsd_options_add_page' );
function bsd_options_add_page() {
	add_submenu_page('edit.php?post_type=bsds', 'Einstellungen', 'Einstellungen', 'manage_options', basename(__FILE__), 'bsd_options_do_page');

	//call register settings function
	add_action( 'admin_init', 'bsd_register_plugin_settings' );
}

function bsd_register_plugin_settings() {
	//register our settings
	register_setting( 'bsd-plugin-settings-group', 'agree_on_bsd', 'bsd_mail_agree_on_bsd_validate' );
	register_setting( 'bsd-plugin-settings-group', 'reject_on_bsd_by_admin', 'bsd_mail_reject_on_bsd_by_admin_validate' );
	register_setting( 'bsd-plugin-settings-group', 'reject_on_bsd_by_user', 'bsd_mail_reject_on_bsd_by_user_validate' );
}

function bsd_options_do_page() {
	?>
	<div class="wrap">
		<form id="bsd-settings-form" method="post" action="options.php">
			<?php settings_fields( 'bsd-plugin-settings-group' ); ?>
			<?php do_settings_sections( 'bsd-plugin-settings-group' ); ?>
			<?php screen_icon(); ?>
			<h1 class="wp-heading-inline"><?php _e( 'BSD Einstellungen', 'twentythirteen' ); ?></h1>
			<br /><br />
			<h1 class="wp-heading-inline"><?php _e( 'E-Mail Texte', 'twentythirteen' ); ?></h1>
			<p>
				Hier k&ouml;nnen die Texte der vom Plugin versendeten E-Mails angepasst werden. Es stehen Platzhalter zur Verf&uuml;gung, die vor dem Versenden der E-Mail durch die korrekten Inhalte ausgetauscht werden.
				<br /><br />
				Platzhalter: [user_name] [bsd_title] [bsd_datum] [bsd_uhrzeit] [bsd_anzahl_personen] [bsd_info]
			</p>
			<h3 class="wp-heading-inline"><?php _e( 'Zusage des Dienstes an User', 'twentythirteen' ); ?></h3>
			<p>
				<textarea rows="10" cols="100" id="agree_on_bsd" name="agree_on_bsd" ><?php echo esc_attr( get_option('agree_on_bsd') ); ?></textarea>
			</p>
			<h3 class="wp-heading-inline"><?php _e( 'Absage des Dienstes an bereits gesetzten User durch Admin', 'twentythirteen' ); ?></h3>
			<p>
				<textarea rows="10" cols="100" id="reject_on_bsd_by_admin" name="reject_on_bsd_by_admin" ><?php echo esc_attr( get_option( 'reject_on_bsd_by_admin' ) ); ?></textarea>
			</p>

			<h3 class="wp-heading-inline"><?php _e( 'Absage des Dienstes an Admin durch User', 'twentythirteen' ); ?></h3>
			<p>
				<textarea rows="10" cols="100" id="reject_on_bsd_by_user" name="reject_on_bsd_by_user" ><?php echo esc_attr( get_option( 'reject_on_bsd_by_user' ) ); ?></textarea>
			</p>
			<?php settings_errors(); ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function bsd_mail_agree_on_bsd_validate( $input ) {

	$input = trim( $input );

	$input = sanitize_textarea_field( $input );

	if ( true === empty( $input ) ) {
		add_settings_error('agree_on_bsd','agree_on_bsd','Das Textfeld "Zusage des Dienstes an User" darf nicht leer sein.','error');

		return get_option('agree_on_bsd');
	}

	return $input;
}

function bsd_mail_reject_on_bsd_by_admin_validate( $input ) {

	$input = trim( $input );

	$input = sanitize_textarea_field( $input );

	if ( true === empty( $input ) ) {
		add_settings_error('reject_on_bsd_by_user','reject_on_bsd_by_user','Das Textfeld "Absage des Dienstes an Admin durch User" darf nicht leer sein.','error');

		return get_option('reject_on_bsd_by_user');
	}

	return $input;
}

function bsd_mail_reject_on_bsd_by_user_validate( $input ) {
	$input = trim( $input );

	$input = sanitize_textarea_field( $input );

	if ( true === empty( $input ) ) {
		add_settings_error('reject_on_bsd_by_user','reject_on_bsd_by_user','Das Textfeld "Absage des Dienstes an Admin durch User" darf nicht leer sein.','error');

		return get_option('reject_on_bsd_by_user');
	}

	return $input;
}