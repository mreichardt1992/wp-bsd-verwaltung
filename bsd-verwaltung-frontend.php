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
		'include'          => '',
		'exclude'          => '',
		'post_type'        => 'bsds',
		'post_mime_type'   => '',
		'post_parent'      => '',
		'author'	       => '',
		'author_name'	   => '',
		'post_status'      => 'publish',
		'suppress_filters' => true,
		'meta_key'         => '_bsd_begin_date',
		'orderby'          => 'meta_value',
		'order'            => 'ASC'
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

		$free_cnt_places = get_event_count_persons($post->ID, $option = 'difference');

		$is_user_set_on_event = get_event_data($user->ID, $post->ID, false, $return_type = 'event_on_post_and_user');

		$panel .= '<div class="widget">';
			$panel .= '<div class="widget-inner">';
				$panel .= '<h3 class="widget-title">'.date('d.m.Y', $bsd_date). ' | ' .$post_data->post_title.'</h3>';
				$panel .= '<div id="store" class="widget-content">';
					$panel .= '<p>';
					$panel .=  '<b>'.__("Beginn:", "wp-bsd-verwaltung").' </b>' . get_post_meta( $post->ID, '_bsd_begin_time', true ) . " Uhr | ";
					$panel .=  '<b>'.__("Ort:", "wp-bsd-verwaltung").' </b>' . get_post_meta( $post->ID, '_bsd_location', true ) . " | ";
					$panel .=  '<b>'.__("Anzahl Posten:", "wp-bsd-verwaltung").' </b>' . get_post_meta( $post->ID, '_bsd_count_persons', true ) . " | ";
			        $panel .=  '<b>'.__("Freie Posten:", "wp-bsd-verwaltung").' </b>' . $free_cnt_places . '<br><br>';
			        $panel .=  $post_data->post_content;
			        $panel .= '</p>';

			        if (empty($is_user_set_on_event)) {
				        $panel .= '<div class="widget-footer"><button class="accept_bsd_button_'.$post->ID.'" onclick="book_user_on_event('.$user->ID.', '.$post->ID.', '.$nonce.');">'.__("Melden", "wp-bsd-verwaltung").'</button>';
			        } else {
				        $panel .= '<div class="widget-footer"><button class="accept_bsd_button_'.$post->ID.'" onclick="unbook_user_from_event('.$post->ID.', '.$user->ID.', '.$nonce.');">'.__("Meldung zur&uuml;ckziehen", "wp-bsd-verwaltung").'</button>';
			        }
					if ($is_user_set_on_event[0]->is_fix == 1) {
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