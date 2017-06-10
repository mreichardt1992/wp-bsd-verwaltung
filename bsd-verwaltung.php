<?php

/*
Plugin Name: BSD Verwaltung
Plugin URI:  https://github.com/mreichardt1992/wp-bsd-verwaltung
Description: Verwaltung und Vergabe von Brandsicherheitsdiensten an die Mannschaft der Feuerwehr
Version:     1.0
Author:      Max Reichardt
License:     GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

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

global $table_name_bookings;
$table_name_bookings = $wpdb->prefix . "bsd_bookings";

include_once 'bsd-verwaltung-user.php';
include_once 'bsd-verwaltung-frontend.php';
include_once 'bsd-verwaltung-backend.php';

// load bootstrap JS
wp_register_script('prefix_jquery', 'https://code.jquery.com/jquery-3.2.1.min.js');
wp_enqueue_script('prefix_jquery');

/*
 * load_js
 *
 * add plugin js files to frontend
 */
function load_js() {
	wp_register_script( 'bsd_verwaltung_script', plugins_url( '/js/script.js' , __FILE__ ) );

	$js_array = array(
		'plugin_dir' => plugin_dir_url( __FILE__ ),
		'ajaxurl' => admin_url('admin-ajax.php')
	);

	wp_localize_script('bsd_verwaltung_script', 'global', $js_array);

	wp_enqueue_script( 'bsd_verwaltung_script' );
}
add_action( 'wp_enqueue_scripts', 'load_js' );

/*
 * load_css
 *
 * add plugin css files to frontend
 */
function load_css() {
	wp_enqueue_style('bsd_verwaltung_style', plugins_url( '/css/styles.css' , __FILE__ ));
}
add_action('wp_enqueue_scripts', 'load_css');

/*
 * bsd_create_db
 *
 * create database table for plugin
 */
function bsd_create_db() {

	global $wpdb;
	$version = get_option( 'bsd_version', '1.0' );
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'bsd_bookings';

	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      `insert_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `user_id` bigint(20) NOT NULL,
      `post_id` bigint(20) NOT NULL,
      `user_type` bigint(20) NOT NULL,
      PRIMARY KEY (`id`),
      FOREIGN KEY (user_id) REFERENCES " . $wpdb->prefix . "users (ID),
      FOREIGN KEY (post_id) REFERENCES " . $wpdb->prefix . "posts (ID)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
register_activation_hook( __FILE__, 'bsd_create_db' );

/*
 * create_posttype
 *
 * registr custom post type "BSDs"
 */
function create_posttype() {

	register_post_type( 'BSDs',
		// CPT Options
		array(
			'labels' => array(
				'name' => __( 'BSDs' ),
				'singular_name' => __( 'BSD' )
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'BSDs'),
		)
	);
}
// Hooking up our function to theme setup
add_action( 'init', 'create_posttype' );

/*
 * custom_post_type
 *
 * set data/arguments on custom post type "BSDs"
 */
function custom_post_type() {

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
add_action( 'init', 'custom_post_type', 0 );

/*
 * get_event_count_persons
 *
 *
 */
function get_event_count_persons($post_id = 0, $option = 'all') {

	if ($post_id == 0) {
		return 'no post_id';
	}

	if ($option == 'all') {

		$cnt_data = count(get_event_data(0,$post_id,0, 'events_on_post'));

	} elseif ($option == 'fix_only') {

		$cnt_data = count(get_event_data(0,$post_id,1, 'events_on_post'));

	} elseif ($option == 'difference') {
		$cnt_data_all = get_post_meta( $post_id, '_bsd_count_persons', true );

		$cnt_data_fix = count(get_event_data(0,$post_id,1, 'events_on_post'));

		$cnt_data = $cnt_data_all - $cnt_data_fix;
	}

	return $cnt_data;
}

/*
 * get_event_data
 *
 *
 */
function get_event_data($user_id = 0, $post_id = 0, $is_fix = false, $return_type = 'all_data') {
	global $wpdb;
	global $table_name_bookings;

	$where = '';

	switch ($return_type) {
		case 'all_events':
			$where = '';
			break;

		case 'events_on_user':
			$where = $wpdb->prepare("user_id = %d", $user_id);
			break;

		case 'events_on_post':
			$where = $wpdb->prepare("post_id = %d", $post_id);
			break;

		case 'event_on_post_and_user':
			$where = $wpdb->prepare("post_id = %d AND user_id = %d", $post_id, $user_id);
			break;
	}

	if ($is_fix == 1) {
		$where .= " AND is_fix = 1";
	}


	$sql = "
	        SELECT
	            *
	        FROM 
	        	$table_name_bookings
	      	WHERE
	      		$where
	    ";

	$result = $wpdb->get_results($sql);

	return $result;
}

/*
 * book_user_on_event
 *
 * add User to BSD table
 */
function book_user_on_event() {
	if ( !wp_verify_nonce( $_POST['nonce'], "ajaxloadpost_nonce_".$_POST['user_id'])) {
		exit("Wrong nonce");
	}

	global $wpdb;
	global $table_name_bookings;

	$data = array(
		'post_id' => $_POST['post_id'],
		'user_id' => $_POST['user_id']
	);

	$insert = $wpdb->insert( $table_name_bookings, $data);

	echo $insert;

	wp_die();
}
add_action( 'wp_ajax_nopriv_book_user_on_event', 'book_user_on_event' );
add_action( 'wp_ajax_book_user_on_event', 'book_user_on_event' );

/*
 * unbook_user_from_event
 *
 * delete User from BSD table
 */
function unbook_user_from_event() {
	if ( !wp_verify_nonce( $_POST['nonce'], "ajaxloadpost_nonce_".$_POST['user_id'])) {
		exit("Wrong nonce");
	}

	global $wpdb;
	global $table_name_bookings;

	$delete = $wpdb->delete( $table_name_bookings, array( 'user_id' => $_POST['user_id'], 'post_id' => $_POST['post_id'] ));

	echo $delete;

	wp_die();
}
add_action( 'wp_ajax_nopriv_unbook_user_from_event', 'unbook_user_from_event' );
add_action( 'wp_ajax_unbook_user_from_event', 'unbook_user_from_event' );

/*
 * send_bsd_mail
 *      $post_id = ID of Post (BSD)
 *      $user_id = ID of WP User
 *      $mailtype = Type of mail
 *
 * Send Mail to User and/or Admin by interactions in Plugin, like attending on a BSD, rejecting a BSD etc.
 */
function send_bsd_mail($post_id, $user_id, $mailtype) {

	$headers = array(
		'From' => 'BSD-Verwaltung <bsd@ffbn.de>'
	);

	$user = get_userdata( $user_id );

	$post_data = get_post( $post_id );

	$to = $user->user_email;

	switch ($mailtype) {
		case 'agree_on_bsd':
				$subject = 'Brandsicherheitsdienst - Zusage';
				$message = 'Du wurdest für einen Brandsicherheitsdienst gesetzt. Folgend findest du die Infos zum betreffenden Dienst:<br><br>';
				$message .= $post_data->post_title . '<br>';
				$message .= 'Datum: ' . get_post_meta( $post_id, '_bsd_begin_date', true ) . '<br>';
				$message .= 'Beginn: ' . get_post_meta( $post_id, '_bsd_begin_time', true ) . ' Uhr<br>';
				$message .= 'Anzahl Posten: ' . get_post_meta( $post_id, '_bsd_count_persons', true ) . '<br>';
				$message .= 'Weitere Infos: ' . $post_data->post_content . '<br><br>';
				$message .= 'Diese E-Mail wurde automatisch generiert, bitte antworte nicht darauf.';
			break;
		case 'reject_on_bsd_by_admin':
				$subject = 'Brandsicherheitsdienst - Absage';
				$message = 'Du wurdest von einem Brandsicherheitsdienst abgezogen, für den du gesetzt warst. Folgend findest du die Infos zum betreffenden Dienst:<br><br>';
				$message .= $post_data->post_title . '<br>';
				$message .= 'Datum: ' . get_post_meta( $post_id, '_bsd_begin_date', true ) . '<br>';
				$message .= 'Beginn: ' . get_post_meta( $post_id, '_bsd_begin_time', true ) . ' Uhr<br>';
				$message .= 'Anzahl Posten: ' . get_post_meta( $post_id, '_bsd_count_persons', true ) . '<br>';
				$message .= 'Weitere Infos: ' . $post_data->post_content . '<br><br>';
				$message .= 'Diese E-Mail wurde automatisch generiert, bitte antworte nicht darauf.';
			break;
		case 'reject_on_bsd_by_user':
				$subject = 'Brandsicherheitsdienst - Absage';
				$message = 'Du hast dich von einem Brandsicherheitsdienst zurückgezogen, für den du bereits gesetzt warst. Folgend findest du die Infos zum betreffenden Dienst:<br><br>';
				$message .= $post_data->post_title . '<br>';
				$message .= 'Datum: ' . get_post_meta( $post_id, '_bsd_begin_date', true ) . '<br>';
				$message .= 'Beginn: ' . get_post_meta( $post_id, '_bsd_begin_time', true ) . ' Uhr<br>';
				$message .= 'Anzahl Posten: ' . get_post_meta( $post_id, '_bsd_count_persons', true ) . '<br>';
				$message .= 'Weitere Infos: ' . $post_data->post_content . '<br><br>';
				$message .= 'Diese E-Mail wurde automatisch generiert, bitte antworte nicht darauf.';
			break;
	}

	add_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );

	wp_mail( $to, $subject, $message, $headers);

	remove_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );
}

/*
 * wpdocs_set_html_mail_content_type
 *
 * set the mail content type to text/html
 */
function wpdocs_set_html_mail_content_type() {
	return 'text/html';
}
