<?php

/*
 * bsds_add_data_field
 *
 * add meta field to add/edit page of BSD Post Site
 */
function bsds_add_data_field() {
	add_meta_box( 'bsd_data_field', 'BSD Daten', 'bsd_build_data_field', 'BSDs', 'normal', 'low' );
}

add_action( 'add_meta_boxes_bsds', 'bsds_add_data_field' );

/*
 * bsd_build_data_field
 *      $post = object of post
 *
 * form for BSD data
 */
function bsd_build_data_field( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'bsd_data_field_nonce' );

	global $wpdb;
	global $bsd_table_name_bookings;

	$current_location      = get_post_meta( $post->ID, '_bsd_location', true );
	$current_begin_date    = date( 'd.m.Y', strtotime( get_post_meta( $post->ID, '_bsd_begin_date', true ) ) );
	$current_begin_time    = get_post_meta( $post->ID, '_bsd_begin_time', true );
	$current_count_persons = get_post_meta( $post->ID, '_bsd_count_persons', true );


	$users_leader = get_users( array(
		'meta_key'   => 'bsd_leader',
		'meta_value' => '1'
	) );

	?>
    <div class='inside'>
        <table>
            <tr>
                <td width="250px">
                    <label for="bsd_location">Veranstaltungsort</label><br />
                    <input type="text" name="bsd_location" id="bsd_location"
                           value="<?php echo esc_html( $current_location ); ?>" size="30" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bsd_begin_date">Datum (tt.mm.jjjj)</label><br />
                    <input type="text" name="bsd_begin_date" id="bsd_begin_date"
                           value="<?php echo esc_html( $current_begin_date ); ?>" size="10" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bsd_begin_time">Uhrzeit (SS:MM)</label><br />
                    <input type="text" name="bsd_begin_time" id="bsd_begin_time"
                           value="<?php echo esc_html( $current_begin_time ); ?>" size="5"  />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bsd_count_persons">Anzahl Teilnehmer</label><br />
                    <input type="text" name="bsd_count_persons" id="bsd_count_persons"
                           value="<?php echo esc_html( $current_count_persons ); ?>" size="2" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bsd_leader_field">Wachposten</label><br />

                    <?php

                    $result = $wpdb->get_results( $wpdb->prepare( "
                                SELECT
                                    *
                                FROM
                                    $bsd_table_name_bookings
                                WHERE
                                    post_id = %d					        					    
                            ", $post->ID ) );

                    foreach ( $result AS $userdata ) {

                        $user = get_userdata( $userdata->user_id );

                        $is_fix = '';

                        if ( 1 == $userdata->is_fix ) {
                            $is_fix = 'checked="checked"';
                        }

                        echo '<input type="checkbox" id="bsd_attendant_' . esc_attr( $user->data->ID ) . '" name="bsd_attendants[]" value="' . esc_attr( $user->data->ID ) . '" ' . esc_attr( $is_fix ) . '><label for="bsd_attendant_' . esc_attr( $user->data->ID ) . '" />' . esc_html( $user->data->display_name ) . '&nbsp;<img src ="' . esc_url( plugin_dir_url( __FILE__ ) ) . 'images/truppfuehrer.png" style="width: 15px; vertical-align: middle;" /></label><br />';
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
	<?php
}

/*
 * bsd_save_data_field_data
 *      $post_id = ID of post
 *
 * save form for BSD data
 */
function bsd_save_data_field_data( $post_id ) {

	global $wpdb;
	global $bsd_table_name_bookings;

	if ( false === isset( $_POST['bsd_data_field_nonce'] ) || false === wp_verify_nonce( $_POST['bsd_data_field_nonce'], basename( __FILE__ ) ) ) {
		return;
	}

	// return if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_REQUEST['bsd_location'] ) ) {

	    $bsd_location = $_POST['bsd_location'];

	    if ( false === is_string($bsd_location) ) {
		    $bsd_location = '';
        }

        if ( strlen($bsd_location) > 30 ) {
		    $bsd_location = '';
        }

		update_post_meta( $post_id, '_bsd_location', sanitize_text_field( $bsd_location ) );
	}

	if ( isset( $_REQUEST['bsd_begin_date'] ) ) {

	    $begin_date = date( 'Y-m-d', strtotime( $_POST['bsd_begin_date'] ) );
		$today = date("Y-m-d");

	    if ( $begin_date < $today ) {
		    $begin_date = $today;
        }

	    if ( 10 !== strlen($begin_date) ) {
		    $begin_date = $today;
        }

	    if ( false === is_numeric( str_replace( '-', '', $begin_date ) ) ) {
		    $begin_date = $today;
        }

		update_post_meta( $post_id, '_bsd_begin_date', sanitize_text_field( $begin_date ) );
	}

	if ( isset( $_REQUEST['bsd_begin_time'] ) ) {

	    $begin_time = $_POST['bsd_begin_time'];
	    $now = date("H:s");

		if ( 5 !== strlen($begin_time) ) {
			$begin_time = $now;
		}

		if ( false === is_numeric( str_replace( ':', '', $begin_time ) ) ) {
			$begin_time = $now;
		}

		update_post_meta( $post_id, '_bsd_begin_time', sanitize_text_field( $begin_time ) );
	}

	if ( isset( $_REQUEST['bsd_count_persons'] ) ) {

		$count_persons = intval( $_POST['bsd_count_persons'] );

		if ( false === $count_persons ) {
			$count_persons = '';
		}

		if ( strlen( $count_persons ) > 2 ) {
			$count_persons = substr( $count_persons, 0, 2 );
		}

		update_post_meta( $post_id, '_bsd_count_persons', sanitize_text_field( $count_persons ) );
	}

	$bsd_attendants_set_fix = $_REQUEST['bsd_attendants'];

	$bsd_applied_users = $wpdb->get_results( $wpdb->prepare( "
                SELECT
                  *
                FROM
                  $bsd_table_name_bookings
                WHERE
                  post_id = %d
            ", $post_id ) );

	foreach ( $bsd_applied_users AS $bsd_applied_user ) {

		if ( true === empty( $bsd_attendants_set_fix ) ) {

			$wpdb->update( $bsd_table_name_bookings,
                array(
			        'is_fix'        => 0,
                    'fix_mail_sent' => null
			    ), array(
                    'post_id' => $post_id,
                    'user_id' => $bsd_applied_user->user_id
                )
            );

			bsd_send_mail( $post_id, $bsd_applied_user->user_id, 'reject_on_bsd_by_admin' );

		} elseif ( false === in_array( $bsd_applied_user->user_id, $bsd_attendants_set_fix ) && $bsd_applied_user->is_fix == 0 ) {

			continue;

		} elseif ( false === in_array( $bsd_applied_user->user_id, $bsd_attendants_set_fix ) && $bsd_applied_user->is_fix == 1 ) {

			$wpdb->update( $bsd_table_name_bookings,
                array(
                    'is_fix'        => 0,
                    'fix_mail_sent' => null
			    ),
                array(
                    'post_id' => $post_id,
                    'user_id' => $bsd_applied_user->user_id
                )
            );

			bsd_send_mail( $post_id, $bsd_applied_user->user_id, 'reject_on_bsd_by_admin' );

		} elseif ( true === in_array( $bsd_applied_user->user_id, $bsd_attendants_set_fix ) && $bsd_applied_user->is_fix == 0 ) {

			$wpdb->update( $bsd_table_name_bookings,
                array(
                    'is_fix'        => 1,
                    'fix_mail_sent' => date( 'Y-m-d H:s', time() )
			    ),
                array(
                    'post_id' => $post_id,
                    'user_id' => $bsd_applied_user->user_id
                )
            );

			bsd_send_mail( $post_id, $bsd_applied_user->user_id, 'agree_on_bsd' );

		}
	}
}

add_action( 'save_post_bsds', 'bsd_save_data_field_data', 10, 2 );

/*
 * bsd_set_custom_edit_bsds_columns
 *      $columns = ID of post
 *
 * set columns for overview-table of custom post type "BSDs"
 */
function bsd_set_custom_edit_bsds_columns( $columns ) {

	unset( $columns['bsds'] );

	$columns['bsd_location']   = __( 'Ort', 'twentythirteen' );
	$columns['bsd_begin_date'] = __( 'Dienstbeginn', 'twentythirteen' );

	return $columns;
}
add_filter( 'manage_bsds_posts_columns', 'bsd_set_custom_edit_bsds_columns' );

/*
 * bsd_custom_bsds_column
 *      $column = specific column of overview table
 *      $post_id = ID of post
 *
 * Add the data to the custom columns for the BSDs post type
 */
function bsd_custom_bsds_column( $column, $post_id ) {

	switch ( $column ) {

		case 'bsd_location' :
			$terms = get_post_meta( $post_id, '_bsd_location', true );
			if ( is_string( $terms ) ) {
				echo $terms;
			} else {
				_e( 'Kein Ort verf&uuml;gbar', 'twentythirteen' );
			}
			break;

		case 'bsd_begin_date' :
			$terms = date( 'd.m.Y', strtotime( get_post_meta( $post_id, '_bsd_begin_date', true ) ) ) . " - " . get_post_meta( $post_id, '_bsd_begin_time', true ) . " Uhr";
			if ( is_string( $terms ) ) {
				echo $terms;
			} else {
				_e( 'Kein Datum verf&uuml;gbar', 'twentythirteen' );
			}
			break;

	}
}
add_action( 'manage_posts_custom_column', 'bsd_custom_bsds_column', 10, 2 );


function bsd_set_sortable_columns( $columns ) {

	$columns['bsd_location']   = '_bsd_location';
	$columns['bsd_begin_date'] = '_bsd_begin_date';

	return $columns;
}
add_filter( 'manage_edit-bsds_sortable_columns', 'bsd_set_sortable_columns' );


function bsd_order_custom_column_by_begin_date( $pieces, $query ) {
	global $wpdb;

	/**
	 * We only want our code to run in the main WP query
	 * AND if an orderby query variable is designated.
	 */
	if ( $query->is_main_query() && ( $orderby = $query->get( 'orderby' ) ) ) {

		// Get the order query variable - ASC or DESC
		$order = strtoupper( $query->get( 'order' ) );

		// Make sure the order setting qualifies. If not, set default as ASC
		if ( ! in_array( $order, array( 'ASC', 'DESC' ) ) ) {
			$order = 'ASC';
        }

		switch( $orderby ) {

			case '_bsd_begin_date':

				$pieces[ 'join' ] .= " LEFT JOIN $wpdb->postmeta wp_rd ON wp_rd.post_id = {$wpdb->posts}.ID AND wp_rd.meta_key = '_bsd_begin_date'";

				$pieces[ 'orderby' ] = "STR_TO_DATE( wp_rd.meta_value,'%Y-%m-%d' ) $order, " . $pieces[ 'orderby' ];

				break;
		}
	}
	return $pieces;
}
add_filter( 'posts_clauses', 'bsd_order_custom_column_by_begin_date', 1, 2 );