<?php

/*
 * bsds_add_data_field
 *
 * add meta field to add/edit page of BSD Post Site
 */
function bsds_add_data_field(){
	add_meta_box( 'bsd_data_field', 'BSD Daten', 'bsd_build_data_field', 'BSDs', 'normal', 'low' );
}
add_action( 'add_meta_boxes_bsds', 'bsds_add_data_field' );

/*
 * bsd_build_data_field
 *      $post = object of post
 *
 * form for BSD data
 */
function bsd_build_data_field( $post ){
	wp_nonce_field( basename( __FILE__ ), 'bsd_data_field_nonce' );

	global $wpdb;
	global $table_name_bookings;

	$current_location = get_post_meta( $post->ID, '_bsd_location', true );
	$current_begin_date = date('d.m.Y', strtotime(get_post_meta( $post->ID, '_bsd_begin_date', true )));
	$current_begin_time = get_post_meta( $post->ID, '_bsd_begin_time', true );
	$current_count_persons = get_post_meta( $post->ID, '_bsd_count_persons', true );


	$users_leader = get_users(array(
		'meta_key'     => 'bsd_leader',
		'meta_value'   => '1'
	));

	?>
	<div class='inside'>
		<table>
			<tr>
				<td width="250px">
					<label for="bsd_location">Veranstaltungsort</label><br>
					<input type="text" name="bsd_location" id="bsd_location" value="<?php echo $current_location; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="bsd_begin_date">Datum (tt.mm.jjjj)</label><br>
					<input type="text" name="bsd_begin_date" id="bsd_begin_date" value="<?php echo $current_begin_date; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="bsd_begin_time">Uhrzeit (SS:MM)</label><br>
					<input type="text" name="bsd_begin_time" id="bsd_begin_time" value="<?php echo $current_begin_time; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="bsd_count_persons">Anzahl Teilnehmer</label><br>
					<input type="text" name="bsd_count_persons" id="bsd_count_persons" value="<?php echo $current_count_persons; ?>" />
				</td>
			</tr>
		</table>

        <label for="bsd_leader_field">Wachposten</label><br>

		<?php

		$result = $wpdb->get_results($wpdb->prepare("
					        SELECT
					            *
							FROM
							  	$table_name_bookings
							WHERE
								post_id = %d					        					    
					    ", $post->ID));

		foreach ($result AS $userdata) {

			$user = get_userdata( $userdata->user_id );

			$is_fix = '';

			if ($userdata->is_fix == 1) {
				$is_fix = 'checked';
            }

			echo '<input type="checkbox" id="bsd_attendant_'.$user->data->ID.'" name="bsd_attendants[]" value="'.$user->data->ID.'" '.$is_fix.'><label for="bsd_attendant_'.$user->data->ID.'">'. $user->data->display_name .'</label><br>';
		}
		?>

	</div>
	<?php
}

/*
 * bsd_save_data_field_data
 *      $post_id = ID of post
 *
 * save form for BSD data
 */
function bsd_save_data_field_data( $post_id ){

	//echo "<pre>" . print_r($_REQUEST) . "</pre>";die();

    global $wpdb;
	global $table_name_bookings;

	if ( !isset( $_POST['bsd_data_field_nonce'] ) || !wp_verify_nonce( $_POST['bsd_data_field_nonce'], basename( __FILE__ ) ) ){
		return;
	}

	// return if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}

	if ( isset( $_REQUEST['bsd_location'] ) ) {
		update_post_meta( $post_id, '_bsd_location', sanitize_text_field( $_POST['bsd_location'] ) );
	}

	if ( isset( $_REQUEST['bsd_begin_date'] ) ) {
		update_post_meta( $post_id, '_bsd_begin_date', sanitize_text_field( date('Y-m-d', strtotime($_POST['bsd_begin_date'])) ) );
	}

	if ( isset( $_REQUEST['bsd_begin_time'] ) ) {
		update_post_meta( $post_id, '_bsd_begin_time', sanitize_text_field( $_POST['bsd_begin_time'] ) );
	}

	if ( isset( $_REQUEST['bsd_count_persons'] ) ) {
		update_post_meta( $post_id, '_bsd_count_persons', sanitize_text_field( $_POST['bsd_count_persons'] ) );
	}

	if ( isset( $_REQUEST['bsd_attendants'] ) ) {

		$result = $wpdb->get_results($wpdb->prepare("
			SELECT
		      *
			FROM
			  $table_name_bookings
			WHERE
			  post_id = %d AND 
			  is_fix = 1
		", $post_id));

		foreach ($result AS $userdata) {
		    if (!in_array($userdata->user_id, $_REQUEST['bsd_attendants'])) {
			    $wpdb->update( $table_name_bookings, array( 'is_fix' => 0 ), array( 'user_id' => $userdata->user_id, 'post_id' => $post_id ) );

			    send_bsd_mail($post_id, $userdata->user_id, 'reject_on_bsd_by_admin');
            }
        }

	    foreach ($_REQUEST['bsd_attendants'] AS $user_id) {
		    $wpdb->update( $table_name_bookings, array( 'is_fix' => 1 ), array( 'user_id' => $user_id, 'post_id' => $post_id ) );

		    send_bsd_mail($post_id, $user_id, 'agree_on_bsd');
        }
	} else {
		$wpdb->update( $table_name_bookings, array( 'is_fix' => 0 ), array( 'post_id' => $post_id ) );
    }

}
add_action( 'save_post_bsds', 'bsd_save_data_field_data', 10, 2 );

/*
 * set_custom_edit_bsds_columns
 *      $columns = ID of post
 *
 * set columns for overview-table of custom post type "BSDs"
 */
function set_custom_edit_bsds_columns($columns) {
	unset( $columns['bsds'] );
	$columns['bsd_location'] = __( 'Ort', 'twentythirteen' );
	$columns['bsd_begin_date'] = __( 'BSD Datum', 'twentythirteen' );

	return $columns;
}
add_filter( 'manage_bsds_posts_columns', 'set_custom_edit_bsds_columns' );

/*
 * custom_bsds_column
 *      $column = specific column of overview table
 *      $post_id = ID of post
 *
 * Add the data to the custom columns for the BSDs post type
 */
function custom_bsds_column( $column, $post_id ) {

	switch ( $column ) {

		case 'Ort' :
			$terms = get_post_meta( $post_id, '_bsd_location', true );
			if ( is_string( $terms ) )
				echo $terms;
			else
				_e( 'Kein Ort verfügbar', 'twentythirteen' );
			break;

		case 'BSD Beginn' :
			$terms = get_post_meta( $post_id, '_bsd_begin_date', true ) . " - " . get_post_meta( $post_id, '_bsd_begin_time', true ) . " Uhr";
			if ( is_string( $terms ) )
				echo $terms;
			else
				_e( 'Kein Datum verfügbar', 'twentythirteen' );
			break;

	}
}
add_action( 'manage_bsds_posts_custom_column' , 'custom_bsds_column', 10, 2 );

/*
 * my_edit_bsds_columns
 *
 * Set names for columns of overview table
 */
function my_edit_bsds_columns() {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Veranstaltung' ),
		'author' => __( 'Ersteller' ),
		'BSD Beginn' => __( 'BSD Beginn' ),
		'Ort' => __( 'Ort' )
	);

	return $columns;
}
add_filter( 'manage_edit-bsds_columns', 'my_edit_bsds_columns' ) ;