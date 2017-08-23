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

	$current_location       = get_post_meta( $post->ID, '_bsd_location', true );
	$current_begin_date     = date( 'd.m.Y', strtotime( get_post_meta( $post->ID, '_bsd_begin_date', true ) ) );
	$current_begin_time     = get_post_meta( $post->ID, '_bsd_begin_time', true );
	$current_count_persons  = get_post_meta( $post->ID, '_bsd_count_persons', true );
	$current_bsd_leader     = get_post_meta( $post->ID, '_bsd_leader', true );

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
                    <table class="bsd_attendant_list">
                        <tr>
                            <td></td>
                            <td><b>Name</b></td>
                            <td><b>Teilnahme</b></td>
                            <td><b>Wachf&uuml;hrer</b></td>
                        </tr>

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
                        echo '<tr>';


                        $user = get_userdata( $userdata->user_id );

	                    $leader_icon = '';

                        if ( get_user_meta( $userdata->user_id, 'bsd_leader' )[0] == 1 ) {
	                        $leader_icon = '<img src ="' . esc_url( plugin_dir_url( __FILE__ ) ) . 'images/truppfuehrer.png" style="width: 15px; vertical-align: middle;" />';
                        }

	                    echo '<td>' . $leader_icon . '</td>';

	                    echo '<td>' . esc_html( $user->data->display_name ) . '</td>';

                        $is_fix = '';

                        if ( 1 == $userdata->is_fix ) {
                            $is_fix = 'checked="checked"';
                        }

                        $checked_output = '';

                        if ( $current_bsd_leader === $user->data->ID ) {
	                        $checked_output = 'checked="checked"';
                        }

                        echo '<td><input type="checkbox" id="bsd_attendant_' . esc_attr( $user->data->ID ) . '" name="bsd_attendants[]" value="' . esc_attr( $user->data->ID ) . '" ' . esc_attr( $is_fix ) . '></td>';

	                    echo '<td><input type="radio" ' . $checked_output . ' id="bsd_leader_' . esc_attr( $user->data->ID ) . '" name="bsd_leader" value="' . esc_attr( $user->data->ID ) . '"></td>';

                        echo '</tr>';
                    }

                    ?>
                    </table>
                    <br>

                    <?php
                        bsd_user_autocomplete_js($result, $post->ID);
                    ?>

                    <input type="text" name="autocomplete" id="autocomplete" value="" placeholder="User hinzuf&uuml;gen..." />
                </td>
            </tr>
        </table>
    </div>
	<?php
}

function bsd_user_autocomplete_js( $set_users, $post_id ) {

	$nonce = wp_create_nonce( "ajaxloadpost_nonce" );

	$set_user_array = array();

	foreach ( $set_users as $set_user ) {
		$set_user_array[] = $set_user->user_id;
    }

    $args = array(
                'order_by' => 'name',
                'exclude' => $set_user_array
            );

	$users = get_users( $args );

	if( $users ) {
		foreach ( $users as $k => $user ) {
			$source[ $k ]['ID']    = $user->ID;
			$source[ $k ]['label'] = $user->display_name;
		}

		?>
        <script type="text/javascript">
            jQuery( document ).ready(function ( $ ) {
                var users = <?php echo json_encode( array_values( $source ) ); ?>;

                jQuery( 'input[name="autocomplete"]' ).autocomplete({
                    source: users,
                    minLength: 2,
                    select: function ( event, ui ) {

                        jQuery( '.bsd_attendant_list > tbody:last-child' ).append( '<tr><td></td><td>' + ui.item.value + '</td><td><input type="checkbox" id="bsd_attendant_' + ui.item.ID + '" name="bsd_attendants[]" value="' + ui.item.ID + '" ></td><td><input type="radio" id="bsd_leader_' + ui.item.ID + '" name="bsd_leader" value="' + ui.item.ID + '" disabled="disabled"></td></tr>' );

                        jQuery( "input[name='bsd_attendants[]']" ).each( function () {

                            var user_id = jQuery( this ).attr( 'value' );
                            var checked = jQuery( this ).attr( 'checked' );
                            var radio = jQuery( '#bsd_leader_' + user_id );

                            if ( !checked ) {
                                jQuery( '#bsd_leader_' + user_id ).attr( 'disabled', 'disabled' );
                                jQuery( '#bsd_leader_' + user_id ).removeAttr( 'checked' );
                            } else {
                                jQuery( '#bsd_leader_' + user_id ).removeAttr( 'disabled' );
                            }

                            jQuery( this ).click( function () {
                                if ( jQuery( this ).is( ':checked' ) ) {
                                    radio.removeAttr( 'disabled' );
                                } else {
                                    radio.attr( 'disabled', 'disabled' );
                                    radio.removeAttr( 'checked' );
                                }
                            })

                        });

                        jQuery.ajax({
                            type: "POST",
                            url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                            data: {
                                action: 'bsd_add_user_to_event_by_admin',
                                user_id: ui.item.ID,
                                post_id: "<?php echo $post_id; ?>",
                                nonce: "<?php echo $nonce; ?>"
                            },
                            success: function () {

                            }
                        });
                    }
                });
            });
        </script>
		<?php
	}
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

		if ( isset( $_REQUEST['bsd_leader'] ) ) {
			$leader_id = $_REQUEST['bsd_leader'];

			$is_leader = 0;

			if ( $leader_id == $bsd_applied_user->user_id ) {
				$is_leader = 1;
				update_post_meta( $post_id, '_bsd_leader', sanitize_text_field( $leader_id ) );
            }
	    }

		if ( true === empty( $bsd_attendants_set_fix ) ) {

			$wpdb->update( $bsd_table_name_bookings,
                array(
			        'is_fix'        => 0,
                    'fix_mail_sent' => null,
			        'is_leader' => $is_leader
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
                    'fix_mail_sent' => null,
                    'is_leader' => $is_leader
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
                    'fix_mail_sent' => date( 'Y-m-d H:s', time() ),
                    'is_leader' => $is_leader
			    ),
                array(
                    'post_id' => $post_id,
                    'user_id' => $bsd_applied_user->user_id
                )
            );

			bsd_send_mail( $post_id, $bsd_applied_user->user_id, 'agree_on_bsd' );

		} elseif ( true === in_array( $bsd_applied_user->user_id, $bsd_attendants_set_fix ) && $bsd_applied_user->is_fix == 1 ) {
			$wpdb->update( $bsd_table_name_bookings,
				array(
					'is_fix'        => 1,
					'fix_mail_sent' => date( 'Y-m-d H:s', time() ),
					'is_leader' => $is_leader
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
	$columns['bsd_get_users_fix'] = __( 'Meldungen / Fix', 'twentythirteen' );

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

	global $wpdb;
	global $bsd_table_name_bookings;

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

		case 'bsd_get_users_attended_fix' :

			$result_attended = $wpdb->get_results( $wpdb->prepare( "
                                SELECT
                                    *
                                FROM
                                    $bsd_table_name_bookings
                                WHERE
                                    post_id = %d					        					    
                            ", $post_id ) );

			$result_fix = $wpdb->get_results( $wpdb->prepare( "
                                SELECT
                                    *
                                FROM
                                    $bsd_table_name_bookings
                                WHERE
                                    post_id = %d AND 
                                    is_fix = 1					        					    
                            ", $post_id ) );

			$count_users_attended = count( $result_attended );

			$count_users_fix = count( $result_fix );

			$terms = (string) $count_users_attended . ' / ' . (string) $count_users_fix;
			if ( is_string( $terms ) ) {
				echo $terms;
			} else {
				_e( 'Keine Daten verf&uuml;gbar', 'twentythirteen' );
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