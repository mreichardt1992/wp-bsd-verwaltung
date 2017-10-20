<?php

/*
Plugin Name: BSD Verwaltung
Plugin URI:  http://bsd-verwaltung.de
Description: Verwaltung und Vergabe von (Brandsicherheits-)Diensten an die Mannschaft der Feuerwehr
Version:     1.3.0
Author:      Max Reichardt
License:     GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {License URI}.
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

global $wpdb;
global $bsd_table_name_bookings;
$bsd_table_name_bookings = $wpdb->prefix . "bsd_bookings";

// load jquery
wp_enqueue_script( 'jquery' );

include_once 'bsd-verwaltung-frontend.php';

if ( true === is_admin() ) {

	include_once 'bsd-verwaltung-user.php';
	include_once 'bsd-verwaltung-backend.php';
	include_once 'bsd-verwaltung-report.php';
	include_once 'bsd-verwaltung-settings.php';

	wp_enqueue_script( 'bsd_verwaltung_timepicker_script', plugins_url( 'js/timepicker/jquery.timepicker.min.js' , __FILE__ ) );
	wp_enqueue_style( 'bsd_verwaltung__timepicker_style' , plugins_url( 'js/timepicker/jquery.timepicker.css' , __FILE__ ) );

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'jquery-ui-datepicker-style' , '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css' );

}

/*
 * bsd_load_js
 *
 * add plugin js files to frontend
 */

function bsd_load_js() {
	wp_register_script( 'bsd_verwaltung_script', plugins_url( '/js/script.js' , __FILE__ ) );

	$js_array = array(
		'plugin_dir' => plugin_dir_url( __FILE__ ),
		'ajaxurl' => admin_url( 'admin-ajax.php' )
	);

	wp_localize_script( 'bsd_verwaltung_script', 'global', $js_array );

	wp_enqueue_script( 'bsd_verwaltung_script' );
	wp_register_script( 'bsd_verwaltung_script', plugins_url( '/js/script.js' , __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'bsd_load_js' );

/*
 * bsd_load_css
 *
 * add plugin css files to frontend
 */
function bsd_load_css() {
	wp_enqueue_style( 'bsd_verwaltung_style', plugins_url( '/css/styles.css' , __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'bsd_load_css' );


/*
 * bsd_load_admin_css
 *
 * add plugin css files to frontend
 */
function bsd_load_admin_css() {
	wp_enqueue_style( 'bsd_verwaltung_admin_style', plugins_url( '/css/admin_styles.css' , __FILE__ ) );
}
add_action( 'admin_enqueue_scripts', 'bsd_load_admin_css' );

/*
 * bsd_load_admin_js
 *
 * add plugin css files to frontend
 */
function bsd_load_admin_js() {
	wp_enqueue_script( 'bsd_verwaltung_script' );
	wp_register_script( 'bsd_verwaltung_script', plugins_url( '/js/admin_script.js' , __FILE__ ) );

	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-autocomplete' );

}
add_action( 'admin_enqueue_scripts', 'bsd_load_admin_js' );


function bsd_add_color_picker() {

	if( is_admin() ) {

		// Add the color picker css file
		wp_enqueue_style( 'wp-color-picker' );

		// Include our custom jQuery file with WordPress Color Picker dependency
		wp_enqueue_script( 'bsd_settings_color_picker', plugins_url( 'js/admin-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
	}
}
add_action( 'admin_enqueue_scripts', 'bsd_add_color_picker' );

/*
 * bsd_create_db
 *
 * create database table for plugin
 */
function bsd_create_db() {

	global $wpdb;
	global $bsd_table_name_bookings;

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $bsd_table_name_bookings (
      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      `insert_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `user_id` bigint(20) NOT NULL,
      `post_id` bigint(20) NOT NULL,
      `user_type` bigint(20) NOT NULL,
      `is_leader` bigint(20) NULL DEFAULT NULL,
      `is_fix` bigint(20) NOT NULL,
      `fix_mail_sent` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      FOREIGN KEY (user_id) REFERENCES " . $wpdb->prefix . "users (ID),
      FOREIGN KEY (post_id) REFERENCES " . $wpdb->prefix . "posts (ID)
	) $charset_collate;
	";

	update_option( 'bsd_current_db_version', 1 );

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
register_activation_hook( __FILE__, 'bsd_create_db' );

/*
 * bsd_add_default_values_settings
 *
 * add options to wp settings API
 */
function bsd_update_db() {
	global $wpdb;
	global $bsd_table_name_bookings;

	$current_version = get_option( 'bsd_current_db_version', 0 );

	switch( $current_version )
	{
		// First update
		case 1:

			$wpdb->query( "ALTER TABLE $bsd_table_name_bookings ADD is_leader bigint(20) NULL DEFAULT NULL" );

			$current_version++;
	}

	update_option( 'bsd_current_db_version', $current_version );
}
add_action( 'plugins_loaded', 'bsd_update_db' );


/*
 * bsd_update_posts_after_time
 *
 * move posts to trash when bsd time is over
 */
function bsd_update_posts_after_time() {

	$args = array(
		'posts_per_page'   => 100,
		'offset'           => 0,
		'category'         => '',
		'category_name'    => '',
		'include'          => '',
		'exclude'          => '',
		'post_type'        => 'bsds',
		'post_mime_type'   => '',
		'post_parent'      => '',
		'author'           => '',
		'author_name'      => '',
		'post_status'      => array( 'publish' ),
		'suppress_filters' => true,
	);

	$posts_array = get_posts( $args );

	date_default_timezone_set( 'Europe/Berlin' );

	$date = strtotime( date( 'd.m.Y', time() ) );

	foreach ( $posts_array AS $post ) {

		$post_date = strtotime( get_post_meta( $post->ID, '_bsd_begin_date', true ) );

		if ( $date > $post_date && $post->post_status == 'publish' ) {
			$post->post_status = 'trash';
			wp_update_post( $post );
		}
	}
}
add_action('admin_init', 'bsd_update_posts_after_time');

/*
 * bsd_add_default_values_settings
 *
 * add options to wp settings API
 */
function bsd_add_default_values_settings() {
	add_option( 'agree_on_bsd', 'Hallo [user_name],<br /><br />Du wurdest für einen Brandsicherheitsdienst gesetzt. Folgend findest du die Infos zum betreffenden Dienst:<br /><br />[bsd_title]<br />Datum: [bsd_datum]<br />Beginn: [bsd_uhrzeit] Uhr<br />Anzahl Posten: [bsd_anzahl_personen]<br />Weitere Infos:<br /><br />[bsd_info]<br /><br />Diese E-Mail wurde automatisch generiert, bitte antworte nicht darauf.' );
	add_option( 'reject_on_bsd_by_admin', 'Hallo [user_name],<br /><br />Du wurdest von einem Brandsicherheitsdienst abgezogen, für den du bereits gesetzt warst. Folgend findest du die Infos zum betreffenden Dienst:<br /><br />[bsd_title]<br />Datum: [bsd_datum]<br />Beginn: [bsd_uhrzeit] Uhr<br />Anzahl Posten: [bsd_anzahl_personen]<br />Weitere Infos:<br /><br />[bsd_info]<br /><br />Diese E-Mail wurde automatisch generiert, bitte antworte nicht darauf.' );
	add_option( 'reject_on_bsd_by_user', 'Hallo Admin,<br /><br />Der User "[user_name]" hat sich von einem Brandsicherheitsdienst zurückgezogen, für den er bereits gesetzt war. Folgend findest du die Infos zum betreffenden Dienst:<br /><br />[bsd_title]<br />Datum: [bsd_datum]<br />Beginn: [bsd_uhrzeit] Uhr<br />Anzahl Posten: [bsd_anzahl_personen]<br />Weitere Infos:<br /><br />[bsd_info]<br /><br />Diese E-Mail wurde automatisch generiert, bitte antworte nicht darauf.' );
	add_option( 'color_picker_panel_header', '#eeeeee' );
	add_option( 'color_picker_panel_header_active', '#666666' );
	add_option( 'cron_last_search_for_bsd', time() );
	add_option( 'access_for_frontend_panels', 0 );
}
register_activation_hook( __FILE__, 'bsd_add_default_values_settings' );

/*
 * bsd_create_posttype
 *
 * registr custom post type "BSDs"
 */
function bsd_create_posttype() {

	register_post_type( 'BSDs',
		// CPT Options
		array(
			'labels' => array(
				'name'                  => __( 'BSDs' ),
				'singular_name'         => __( 'BSD' ),
				'all_items'             => __( 'Alle Dienste' ),
				'add_new'               => __( 'Dienst hinzufügen' ),
				'add_new_item'          => __( 'Neuen Dienst hinzufügen' ),
				'edit_item'             => __( 'Dienst bearbeiten' ),
				'search_items'          => __( 'Dienste durchsuchen' ),
				'not_found'             => __( 'Keine Dienste gefunden' ),
				'not_found_in_trash'    => __( 'Keine Dienste im Papierkorb gefunden' )
			),
			'public'      => true,
			'has_archive' => true,
			'rewrite'     => array( 'slug' => 'BSDs' ),
		)
	);
}
add_action( 'init', 'bsd_create_posttype' );


/*
 * bsd_get_event_count_persons
 *
 *
 */
function bsd_get_event_count_persons( $post_id = 0, $option = 'all' ) {

	if ( 0 == $post_id ) {
		return 'no post_id';
	}

	if ( 'all' == $option ) {

		$cnt_data = count( bsd_get_event_data( 0,$post_id,0, 'events_on_post' ) );

	} elseif ( 'fix_only' == $option ) {

		$cnt_data = count( bsd_get_event_data( 0,$post_id,1, 'events_on_post' ) );

	} elseif ( 'difference' == $option ) {
		$cnt_data_all = get_post_meta( $post_id, '_bsd_count_persons', true );

		$cnt_data_fix = count( bsd_get_event_data( 0,$post_id,1, 'events_on_post' ) );

		$cnt_data = $cnt_data_all - $cnt_data_fix;
	}

	return $cnt_data;
}

/*
 * bsd_get_event_data
 *
 *
 */
function bsd_get_event_data( $user_id = 0, $post_id = 0, $is_fix = false, $return_type = 'all_events' ) {
	global $wpdb;
	global $bsd_table_name_bookings;

	$where = '';

	switch ( $return_type ) {
		case 'all_events':
			$where = '';
			break;

		case 'events_on_user':
			$where = $wpdb->prepare( "user_id = %d", $user_id );
			break;

		case 'events_on_post':
			$where = $wpdb->prepare( "post_id = %d", $post_id );
			break;

		case 'event_on_post_and_user':
			$where = $wpdb->prepare( "post_id = %d AND user_id = %d", $post_id, $user_id );
			break;
	}

	if ( 1 == $is_fix ) {
		$where .= " AND is_fix = 1";
	}

	$result = $wpdb->get_results( "
		SELECT
			*
		FROM 
			$bsd_table_name_bookings
		WHERE
			$where
	" );

	return $result;
}

/*
 * bsd_book_user_on_event
 *
 * add User to BSD table
 */
function bsd_book_user_on_event() {
	if ( ! wp_verify_nonce( $_POST['nonce'], "ajaxloadpost_nonce_" . $_POST['user_id'] ) ) {
		exit( "Wrong nonce" );
	}

	global $wpdb;
	global $bsd_table_name_bookings;

	$post_id = intval( $_POST['post_id'] );
	$user_id = intval( $_POST['user_id'] );

	if ( ! $post_id ) {
		return false;
	}

	if ( ! $user_id ) {
		return false;
	}

	$data = array(
		'post_id' => $post_id,
		'user_id' => $user_id
	);

	$insert = $wpdb->insert( $bsd_table_name_bookings, $data );

	echo $insert;

	wp_die();
}
add_action( 'wp_ajax_nopriv_bsd_book_user_on_event', 'bsd_book_user_on_event' );
add_action( 'wp_ajax_bsd_book_user_on_event', 'bsd_book_user_on_event' );


/*
 * bsd_add_user_to_event_by_admin
 *
 * add User to BSD table
 */
function bsd_add_user_to_event_by_admin() {
	if ( ! wp_verify_nonce( $_POST['nonce'], "ajaxloadpost_nonce" ) ) {
		exit( "Wrong nonce" );
	}

	global $wpdb;
	global $bsd_table_name_bookings;

	$post_id = intval( $_POST['post_id'] );
	$user_id = intval( $_POST['user_id'] );

	if ( ! $post_id ) {
		return false;
	}

	if ( ! $user_id ) {
		return false;
	}

	$data = array(
		'post_id' => $post_id,
		'user_id' => $user_id
	);

	$insert = $wpdb->insert( $bsd_table_name_bookings, $data );

	echo $insert;

	wp_die();
}
add_action( 'wp_ajax_nopriv_bsd_add_user_to_event_by_admin', 'bsd_add_user_to_event_by_admin' );
add_action( 'wp_ajax_bsd_add_user_to_event_by_admin', 'bsd_add_user_to_event_by_admin' );

/*
 * bsd_unbook_user_from_event
 *
 * delete User from BSD table
 */
function bsd_unbook_user_from_event() {
	if ( ! wp_verify_nonce( $_POST['nonce'], "ajaxloadpost_nonce_" . wp_get_current_user()->ID ) ) {
		exit( "Wrong nonce" );
	}

	global $wpdb;
	global $bsd_table_name_bookings;

	$post_id = intval( $_POST['post_id'] );
	$user_id = intval( $_POST['user_id'] );

	if ( ! $post_id ) {
		return false;
	}

	if ( ! $user_id ) {
		return false;
	}

	$data = array(
		'post_id' => $post_id,
		'user_id' => $user_id
	);

	$bsd_applied_user = $wpdb->get_results( $wpdb->prepare( "
                SELECT
					*
                FROM
					$bsd_table_name_bookings
                WHERE
					post_id = %d AND 
					user_id = %d
            ", $data['post_id'], $data['user_id'] ) );

	if ( 1 == $bsd_applied_user[0]->is_fix ) {
		bsd_send_mail( $data['post_id'], $data['user_id'], 'reject_on_bsd_by_user' );
	}

	$delete = $wpdb->delete( $bsd_table_name_bookings, array( 'user_id' => $data['user_id'], 'post_id' => $data['post_id'] ) );

	echo $delete;

	wp_die();
}
add_action( 'wp_ajax_nopriv_bsd_unbook_user_from_event', 'bsd_unbook_user_from_event' );
add_action( 'wp_ajax_bsd_unbook_user_from_event', 'bsd_unbook_user_from_event' );

/*
 * bsd_send_mail
 *      $post_id = ID of Post (BSD)
 *      $user_id = ID of WP User
 *      $mailtype = Type of mail
 *
 * Send Mail to User and/or Admin by interactions in Plugin, like attending on a BSD, rejecting a BSD etc.
 */
function bsd_send_mail( $post_id, $user_id, $mailtype ) {

	global $wpdb;
	global $bsd_table_name_bookings;

	$post_data = get_post( $post_id );

	$user = get_userdata( $user_id );

	$admin = get_userdata( $post_data->post_author );

	$to = $user->user_email;

	switch ( $mailtype ) {
		case 'agree_on_bsd':
				$subject = 'Brandsicherheitsdienst - Zusage';

				$message = get_option( $mailtype );

				$message = str_replace( '[user_name]', $user->display_name, $message );
				$message = str_replace( '[bsd_titel]', $post_data->post_title, $message );
				$message = str_replace( '[bsd_ort]', get_post_meta( $post_id, '_bsd_location', true ), $message );
				$message = str_replace( '[bsd_datum]', date( 'd.m.Y', strtotime( get_post_meta( $post_id, '_bsd_begin_date', true ) ) ), $message );
				$message = str_replace( '[bsd_uhrzeit]', get_post_meta( $post_id, '_bsd_begin_time', true ), $message );
				$message = str_replace( '[bsd_anzahl_personen]', get_post_meta( $post_id, '_bsd_count_persons', true ), $message );
				$message = str_replace( '[bsd_info]', $post_data->post_content, $message );

				$bsd_applied_user = $wpdb->get_results( $wpdb->prepare( "
		            SELECT
							*
		            FROM
							$bsd_table_name_bookings
		            WHERE
						post_id = %d AND
						is_leader = 1
            	", $post_id, $user_id ) );

				$user = get_userdata( $bsd_applied_user[0]->user_id );

				$message = str_replace( '[bsd_wachführer]', $user->data->display_name, $message );

				$message = nl2br( $message, false );

			break;
		case 'reject_on_bsd_by_admin':
				$subject = 'Brandsicherheitsdienst - Absage';

				$message = get_option( $mailtype );

				$message = str_replace( '[user_name]', $user->display_name, $message );
				$message = str_replace( '[bsd_titel]', $post_data->post_title, $message );
				$message = str_replace( '[bsd_ort]', get_post_meta( $post_id, '_bsd_location', true ), $message );
				$message = str_replace( '[bsd_datum]', date('d.m.Y', strtotime( get_post_meta( $post_id, '_bsd_begin_date', true ) ) ), $message );
				$message = str_replace( '[bsd_uhrzeit]', get_post_meta( $post_id, '_bsd_begin_time', true ), $message );
				$message = str_replace( '[bsd_anzahl_personen]', get_post_meta( $post_id, '_bsd_count_persons', true ), $message );
				$message = str_replace( '[bsd_info]', $post_data->post_content, $message );

				$bsd_applied_user = $wpdb->get_results( $wpdb->prepare( "
			            SELECT
								*
			            FROM
								$bsd_table_name_bookings
			            WHERE
							post_id = %d AND
							is_leader = 1
	                ", $post_id, $user_id ) );

				$user = get_userdata( $bsd_applied_user[0]->user_id );

				$message = str_replace( '[bsd_wachführer]', $user->data->display_name, $message );

				$message = nl2br( $message, false );

			break;
		case 'reject_on_bsd_by_user':
			//mail to user

			$subject = 'Brandsicherheitsdienst - User-Absage';

			$message = get_option( $mailtype );

			$message = str_replace( '[user_name]', $user->display_name, $message );
			$message = str_replace( '[bsd_titel]', $post_data->post_title, $message );
			$message = str_replace( '[bsd_ort]', get_post_meta( $post_id, '_bsd_location', true ), $message );
			$message = str_replace( '[bsd_datum]', date( 'd.m.Y', strtotime( get_post_meta( $post_id, '_bsd_begin_date', true ) ) ), $message );
			$message = str_replace( '[bsd_uhrzeit]', get_post_meta( $post_id, '_bsd_begin_time', true ), $message );
			$message = str_replace( '[bsd_anzahl_personen]', get_post_meta( $post_id, '_bsd_count_persons', true ), $message );
			$message = str_replace( '[bsd_info]', $post_data->post_content, $message );

			$bsd_applied_user = $wpdb->get_results( $wpdb->prepare( "
			            SELECT
								*
			            FROM
								$bsd_table_name_bookings
			            WHERE
							post_id = %d AND
							is_leader = 1
	                ", $post_id, $user_id ) );

			$user = get_userdata( $bsd_applied_user[0]->user_id );

			$message = str_replace( '[bsd_wachführer]', $user->data->display_name, $message );

			$message = nl2br( $message , false );

			$args = array(
				'role__in' => array('wehrfhrung', 'administrator'),
			);

			$admin_users = get_users( $args );

			$to = array();

			foreach ( $admin_users AS $user ) {

				$userdata = get_userdata( $user->ID );

				$to[] = $userdata->user_email;

			}
			break;
	}

	add_filter( 'wp_mail_from', function() {

		$domain = get_site_url();

		$domain = str_replace( 'http://', '', $domain );
		$domain = str_replace( 'https://', '', $domain );
		$domain = str_replace( 'www.', '', $domain );

		$domain = 'BSD-Verwaltung@' . $domain;

		return $domain;

	});

	add_filter( 'wp_mail_from_name', function() {
		return 'BSD-Verwaltung';
	});

	add_filter( 'wp_mail_content_type', 'bsd_set_html_mail_content_type' );
	wp_mail( $to, $subject, $message );
	remove_filter( 'wp_mail_content_type', 'bsd_set_html_mail_content_type' );
}

/*
 * bsd_set_html_mail_content_type
 *
 * set the mail content type to text/html
 */
function bsd_set_html_mail_content_type() {
	return 'text/html';
}


function bsd_cron_exec () {

	global $wpdb;
	
	$notification_count = get_option( 'bsd_mail_notification_count', 0 );

	if ( $notification_count < 1 ) {
		return;
	}

	$user_mails = $wpdb->get_results( "
        SELECT
            user_login, display_name, user_email
        FROM 
            " . $wpdb->prefix . "users
    " );

    add_filter( 'wp_mail_from', function() {

        $domain = get_site_url();

        $domain = str_replace( 'http://', '', $domain );
        $domain = str_replace( 'https://', '', $domain );
        $domain = str_replace( 'www.', '', $domain );

        $domain = 'BSD-Verwaltung@' . $domain;

        return $domain;

    });

	foreach ( $user_mails AS $user_mail ) {

		$message = 'Hallo ' . $user_mail->display_name . ',<br><br> auf ' .  get_site_url() . ' gibt es neue Brandsicherheitsdienste. Du findest sie im internen Bereich.<br><br>Zum Login hier <a href="' .  get_site_url() . '/wp-admin">klicken</a>.<br><br>Bitte antworte nicht auf diese Mail, da sie automatisch generiert wurde.';

        add_filter( 'wp_mail_content_type', 'bsd_set_html_mail_content_type' );
        wp_mail( $user_mail->user_email, 'Neue BSDs', $message );
        remove_filter( 'wp_mail_content_type', 'bsd_set_html_mail_content_type' );

	}

	update_option( 'bsd_mail_notification_count', 0 );
}
add_action( 'bsd_cron_hook', 'bsd_cron_exec' );



if ( "1" === get_option( 'bsd_enable_daily_mail_notification', false ) ) {

	if ( false === wp_next_scheduled( 'bsd_cron_hook' ) ) {

		wp_schedule_event( time(), 'daily', 'bsd_cron_hook' );

	}

} else {

	wp_unschedule_event( wp_next_scheduled( 'bsd_cron_hook' ), 'bsd_cron_hook' );

}