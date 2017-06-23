<?php

function bsd_draw_events_panel() {

	$user = wp_get_current_user();

	if ( false === empty( get_option('access_for_frontend_panels') ) ) {

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

	if (true === empty( get_option( 'color_picker_panel_header' ) )) {
		$color_panel_header = '#eee';
	} else {
		$color_panel_header = get_option( 'color_picker_panel_header' );
	}

	if (true === empty( get_option( 'color_picker_panel_header_active' ) )) {
		$color_panel_header_active = '#666';
	} else {
		$color_panel_header_active = get_option( 'color_picker_panel_header_active' );
	}

	$panel .= '
		<style>
		#bsd-panels .bsd-widget-title {
			background: ' . esc_attr( $color_panel_header ) . ' none repeat scroll 0 0 !important;
		}
		
		#bsd-panels .bsd-widget.active .bsd-widget-title, #bsd-panels .bsd-widget.active ul {
			background: ' . esc_attr( $color_panel_header_active ) . ' none repeat scroll 0 0 !important;
		}
		</style>
	';

	$panel .= '<aside id="bsd-panels" class="col-md-3 col-sm-3">';
	$panel .= '<div class="row">';

	foreach ( $posts_array AS $post ) {

		$nonce = wp_create_nonce( "ajaxloadpost_nonce_".$user->ID );

		$nonce = "'".$nonce."'";

		$post_data = get_post( $post->ID );

		$date = strtotime(date('d.m.Y', time()));

		$bsd_date = strtotime(date('d.m.Y', strtotime( get_post_meta( $post->ID, '_bsd_begin_date', true ))) );

		if ( $bsd_date < $date ) {
			continue;
		}

		$free_cnt_places = bsd_get_event_count_persons( $post->ID, $option = 'difference' );

		$is_user_set_on_event = bsd_get_event_data( $user->ID, $post->ID, false, $return_type = 'event_on_post_and_user' );

		$panel .= '<div class="bsd-widget">';
			$panel .= '<div class="bsd-widget-inner">';
				$panel .= '<h4 class="bsd-widget-title">' . esc_html( date('d.m.Y', $bsd_date) ). ' | ' .esc_html( $post_data->post_title ) . '</h4>';
				$panel .= '<div id="store" class="bsd-widget-content">';
					$panel .= '<p>';
					$panel .=  '<b>' . __( "Beginn:", "wp-bsd-verwaltung" ) . ' </b>' . esc_html( get_post_meta( $post->ID, '_bsd_begin_time', true ) ) . " Uhr | ";
					$panel .=  '<b>' . __( "Ort:", "wp-bsd-verwaltung" ) . ' </b>' . esc_html( get_post_meta( $post->ID, '_bsd_location', true ) ) . " | ";
					$panel .=  '<b>' . __( "Anzahl Posten:", "wp-bsd-verwaltung" ) . ' </b>' . esc_html( get_post_meta( $post->ID, '_bsd_count_persons', true ) ) . " | ";
			        $panel .=  '<b>' . __( "Freie Posten:", "wp-bsd-verwaltung" ) . ' </b>' . esc_html( $free_cnt_places ) . '<br /><br />';
			        $panel .=  $post_data->post_content;
			        $panel .= '</p>';

			        if ( true === empty($is_user_set_on_event) ) {
				        $panel .= '<div class="bsd-widget-footer"><button class="accept_bsd_button_' . esc_attr( $post->ID ) . '" onclick="bsd_book_user_on_event( ' . esc_attr( $user->ID ) . ', ' . esc_attr( $post->ID ) . ', ' . esc_attr( $nonce ) . ' );">' . __( "Melden", "wp-bsd-verwaltung" ) . '</button>';
			        } else {
				        $panel .= '<div class="bsd-widget-footer"><button class="accept_bsd_button_' . esc_attr($post->ID) . '" onclick="bsd_unbook_user_from_event( ' . esc_attr( $post->ID ) . ', ' . esc_attr( $user->ID ) . ', ' . esc_attr( $nonce ) . ' );">' . __( "Meldung zur&uuml;ckziehen", "wp-bsd-verwaltung" ) . '</button>';
			        }

					if ( 1 == $is_user_set_on_event[0]->is_fix ) {
						$panel .= '&nbsp;<button id="is-fix-text-' . esc_attr( $post->ID ) . '" class="is-fix-text">' . __( "Du bist f&uuml;r diesen Dienst gesetzt!", "wp-bsd-verwaltung" ) . '</button>';
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
add_shortcode('BSD_Panel', 'bsd_draw_events_panel');