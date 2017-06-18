<?php

/*
Plugin Name: BSD Verwaltung
Plugin URI:  http://bsd-verwaltung.de
Description: Verwaltung und Vergabe von (Brandsicherheits-)Diensten an die Mannschaft der Feuerwehr
Version:     0.1.0
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

global $bsd_table_name_bookings;
$bsd_table_name_bookings = $wpdb->prefix . "bsd_bookings";

// load jquery
wp_enqueue_script('jquery');

include_once 'bsd-verwaltung-user.php';
include_once 'bsd-verwaltung-frontend.php';
include_once 'bsd-verwaltung-backend.php';
include_once 'bsd-verwaltung-settings.php';

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
      `is_fix` bigint(20) NOT NULL,
      `fix_mail_sent` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      FOREIGN KEY (user_id) REFERENCES " . $wpdb->prefix . "users (ID),
      FOREIGN KEY (post_id) REFERENCES " . $wpdb->prefix . "posts (ID)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
register_activation_hook( __FILE__, 'bsd_create_db' );

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
				'name'          => __( 'BSDs' ),
				'singular_name' => __( 'BSD' )
			),
			'public'      => true,
			'has_archive' => true,
			'rewrite'     => array('slug' => 'BSDs'),
		)
	);
}
// Hooking up our function to theme setup
add_action( 'init', 'bsd_create_posttype' );

/*
 * bsd_set_custom_post_type_options
 *
 * set data/arguments on custom post type "BSDs"
 */
function bsd_set_custom_post_type_options() {

	// Set UI labels for Custom Post Type
	$labels = array(
		'name'                => _x( 'BSDs', 'Post Type General Name', 'twentythirteen' ),
		'singular_name'       => _x( 'BSD', 'Post Type Singular Name', 'twentythirteen' ),
		'menu_name'           => __( 'BSDs', 'twentythirteen' ),
		'parent_item_colon'   => __( 'Übergeordneter BSD', 'twentythirteen' ),
		'all_items'           => __( 'Alle BSDs', 'twentythirteen' ),
		'view_item'           => __( 'BSD anzeigen', 'twentythirteen' ),
		'add_new_item'        => __( 'BSD hinzufügen', 'twentythirteen' ),
		'add_new'             => __( 'hinzufügen', 'twentythirteen' ),
		'edit_item'           => __( 'BSD bearbeiten', 'twentythirteen' ),
		'update_item'         => __( 'BSD aktualisieren', 'twentythirteen' ),
		'search_items'        => __( 'BSD suchen', 'twentythirteen' ),
		'not_found'           => __( 'Nicht gefunden', 'twentythirteen' ),
		'not_found_in_trash'  => __( 'Nichts im Papierkorb gefunden', 'twentythirteen' ),
	);

	// Set other options for Custom Post Type
	$args = array(
		'label'               => __( 'BSDs', 'twentythirteen' ),
		'description'         => __( 'Brandsicherheitsdienste', 'twentythirteen' ),
		'labels'              => $labels,
		// Features this CPT supports in Post Editor
		'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
		// You can associate this CPT with a taxonomy or custom taxonomy.
		'taxonomies'          => array( 'genres' ),
		/* A hierarchical CPT is like Pages and can have
		* Parent and child items. A non-hierarchical CPT
		* is like Posts.
		*/
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => false,
		'show_in_menu'        => false,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => false,
		'capability_type'     => 'page',
	);

	// Registering your Custom Post Type
	register_post_type( 'BSDs', $args );

}

/*
 * Hook into the 'init' action so that the function
 * Containing our post type registration is not
 * unnecessarily executed.
 */
add_action( 'init', 'bsd_set_custom_post_type_options', 0 );

/*
 * bsd_get_event_count_persons
 *
 *
 */
function bsd_get_event_count_persons($post_id = 0, $option = 'all') {

	if ( 0 == $post_id ) {
		return 'no post_id';
	}

	if ( 'all' == $option ) {

		$cnt_data = count( bsd_get_event_data(0,$post_id,0, 'events_on_post') );

	} elseif ( 'fix_only' == $option ) {

		$cnt_data = count( bsd_get_event_data(0,$post_id,1, 'events_on_post') );

	} elseif ( 'difference' == $option ) {
		$cnt_data_all = get_post_meta( $post_id, '_bsd_count_persons', true );

		$cnt_data_fix = count( bsd_get_event_data(0,$post_id,1, 'events_on_post') );

		$cnt_data = $cnt_data_all - $cnt_data_fix;
	}

	return $cnt_data;
}

/*
 * bsd_get_event_data
 *
 *
 */
function bsd_get_event_data( $user_id = 0, $post_id = 0, $is_fix = false, $return_type = 'all_data' ) {
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
		exit("Wrong nonce");
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
 * bsd_unbook_user_from_event
 *
 * delete User from BSD table
 */
function bsd_unbook_user_from_event() {
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
		bsd_send_mail($data['post_id'], $data['user_id'], 'reject_on_bsd_by_user');
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

	$headers = array(
		'From' => 'BSD-Verwaltung <bsd@ffbn.de>'
	);

	$post_data = get_post( $post_id );

	$user = get_userdata( $user_id );

	$admin = get_userdata( $post_data->post_author );

	$to = $user->user_email;

	switch ( $mailtype ) {
		case 'agree_on_bsd':
				$subject = 'Brandsicherheitsdienst - Zusage';

				$message = get_option( $mailtype );

				$message = str_replace('[user_name]', $user->display_name, $message);
				$message = str_replace('[bsd_title]', $post_data->post_title, $message);
				$message = str_replace('[bsd_datum]', date('d.m.Y', strtotime( get_post_meta( $post_id, '_bsd_begin_date', true ) ) ), $message);
				$message = str_replace('[bsd_uhrzeit]', get_post_meta( $post_id, '_bsd_begin_time', true ), $message);
				$message = str_replace('[bsd_anzahl_personen]', get_post_meta( $post_id, '_bsd_count_persons', true ), $message);
				$message = str_replace('[bsd_info]', $post_data->post_content, $message);

				$message = nl2br( esc_html( $message ), false );

				add_filter( 'wp_mail_content_type', 'bsd_set_html_mail_content_type' );
				wp_mail( $to, $subject, $message, $headers );
				remove_filter( 'wp_mail_content_type', 'bsd_set_html_mail_content_type' );

			break;
		case 'reject_on_bsd_by_admin':
				$subject = 'Brandsicherheitsdienst - Absage';

				$message = get_option( $mailtype );

				$message = str_replace('[user_name]', $user->display_name, $message);
				$message = str_replace('[bsd_title]', $post_data->post_title, $message);
				$message = str_replace('[bsd_datum]', date('d.m.Y', strtotime( get_post_meta( $post_id, '_bsd_begin_date', true ) ) ), $message);
				$message = str_replace('[bsd_uhrzeit]', get_post_meta( $post_id, '_bsd_begin_time', true ), $message);
				$message = str_replace('[bsd_anzahl_personen]', get_post_meta( $post_id, '_bsd_count_persons', true ), $message);
				$message = str_replace('[bsd_info]', $post_data->post_content, $message);

				$message = nl2br( esc_html( $message ), false );

				add_filter( 'wp_mail_content_type', 'bsd_set_html_mail_content_type' );
				wp_mail( $to, $subject, $message, $headers);
				remove_filter( 'wp_mail_content_type', 'bsd_set_html_mail_content_type' );

			break;
		case 'reject_on_bsd_by_user':
			//mail to user

			$subject = 'Brandsicherheitsdienst - User-Absage';

			$message = get_option( $mailtype );

			$message = str_replace('[user_name]', $admin->display_name, $message);
			$message = str_replace('[bsd_title]', $post_data->post_title, $message);
			$message = str_replace('[bsd_datum]', date('d.m.Y', strtotime( get_post_meta( $post_id, '_bsd_begin_date', true ) ) ), $message);
			$message = str_replace('[bsd_uhrzeit]', get_post_meta( $post_id, '_bsd_begin_time', true ), $message);
			$message = str_replace('[bsd_anzahl_personen]', get_post_meta( $post_id, '_bsd_count_persons', true ), $message);
			$message = str_replace('[bsd_info]', $post_data->post_content, $message);

			$message = nl2br( esc_html( $message ), false );

			$to = $admin->user_email;

			add_filter( 'wp_mail_content_type', 'bsd_set_html_mail_content_type' );
			wp_mail( $to, $subject, $message, $headers );
			remove_filter( 'wp_mail_content_type', 'bsd_set_html_mail_content_type' );

			break;
	}
}

/*
 * wpdocs_set_html_mail_content_type
 *
 * set the mail content type to text/html
 */
function bsd_set_html_mail_content_type() {
	return 'text/html';
}

