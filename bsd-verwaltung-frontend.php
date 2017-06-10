<?php

function draw_events_panel() {
	global $wpdb;
	global $table_name_bookings;

	$user = wp_get_current_user();

	if ($user->data->ID == 0) {

		echo __("Nur registrierte Nutzer d&uuml;rfen diesen Bereich sehen.", 'wp-bsd-verwaltung');

		return;
	}

	$panel = '';

	$args = array(
		'posts_per_page'   => 100,
		'offset'           => 0,
		'category'         => '',
		'category_name'    => '',
		'orderby'          => 'date',
		'order'            => 'DESC',
		'include'          => '',
		'exclude'          => '',
		'meta_key'         => '',
		'meta_value'       => '',
		'post_type'        => 'bsds',
		'post_mime_type'   => '',
		'post_parent'      => '',
		'author'	   => '',
		'author_name'	   => '',
		'post_status'      => 'publish',
		'suppress_filters' => true
	);

	$posts_array = get_posts( $args );

	$x = 1;

	$panel .= '<aside id="secondary" class="col-md-3 col-sm-3">';
	$panel .= '<div class="row">';

	foreach ($posts_array AS $post) {

		$nonce = wp_create_nonce("ajaxloadpost_nonce_".$user->ID);

		$nonce = "'".$nonce."'";

		$post_data = get_post( $post->ID );

		$date = strtotime(date('d.m.Y', time()));

		$bsd_date = strtotime(date('d.m.Y', strtotime(get_post_meta( $post->ID, '_bsd_begin_date', true ))));

		if ($bsd_date < $date) {
			continue;
		}

		$result = $wpdb->get_results($wpdb->prepare("
	        SELECT
	            *
	        FROM 
	        	$table_name_bookings
	      	WHERE
	      		post_id = %d AND 
	      		user_id = %d
	    ", $post->ID, $user->ID));

		$cnt_result = count($result);

		$free_cnt_places = 123;

		$panel .= '<div class="widget">';
			$panel .= '<div class="widget-inner">';
				$panel .= '<h3 class="widget-title">'.get_post_meta( $post->ID, '_bsd_begin_date', true ). ' | ' .$post_data->post_title.'</h3>';
				$panel .= '<div id="store" class="widget-content">';
					$panel .= '<p>';
					$panel .=  '<b>'.__("Beginn:", "wp-bsd-verwaltung").' </b>' . get_post_meta( $post->ID, '_bsd_begin_time', true ) . " Uhr | ";
					$panel .=  '<b>'.__("Ort:", "wp-bsd-verwaltung").' </b>' . get_post_meta( $post->ID, '_bsd_location', true ) . " | ";
					$panel .=  '<b>'.__("Anzahl Posten:", "wp-bsd-verwaltung").' </b>' . get_post_meta( $post->ID, '_bsd_count_persons', true ) . " | ";
			        $panel .=  '<b>'.__("Freie Posten:", "wp-bsd-verwaltung").' </b>' . $free_cnt_places . '<br><br>';
			        $panel .=  $post_data->post_content;
			        $panel .= '</p>';

			        if ($cnt_result == 0) {
				        $panel .= '<div class="widget-footer"><button class="accept_bsd_button_'.$post->ID.'" onclick="book_user_on_event('.$user->ID.', '.$post->ID.', '.$nonce.');">'.__("Melden", "wp-bsd-verwaltung").'</button>';
			        } else {
				        $panel .= '<div class="widget-footer"><button class="accept_bsd_button_'.$post->ID.'" onclick="unbook_user_from_event('.$post->ID.', '.$user->ID.', '.$nonce.');">'.__("Meldung zur&uuml;ckziehen", "wp-bsd-verwaltung").'</button>';
			        }
					if ($result[0]->is_fix == 1) {
						$panel .= '&nbsp;<button id="is_fix_text_'.$post->ID.'" class="is_fix_text">'.__("Du bist f&uuml;r diesen Dienst gesetzt!", "wp-bsd-verwaltung").'</button>';
					}

					$panel .= '</div>';
				$panel .= '</div>';
		    $panel .= '</div>';
		$panel .= '</div>';

        $x++;
	}


	$panel .= '</div>';
	$panel .= '</aside>';

	return $panel;

}
add_shortcode('BSD_Panel', 'draw_events_panel');