<?php

//Add settings to menu
add_action( 'admin_menu', 'bsd_export_add_page' );
function bsd_export_add_page() {
	add_submenu_page( 'edit.php?post_type=bsds', 'Berichte', 'Berichte', 'manage_options', basename( __FILE__ ), 'bsd_report_do_page' );

	//call register settings function
	add_action( 'admin_init', 'bsd_register_plugin_settings' );
}


function bsd_report_do_page() {

	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'bsd_upcoming_bsds';

	if( isset( $_GET[ 'tab' ] ) ) {
		$active_tab = $_GET[ 'tab' ];
	} // end if

	?>
	<div class="wrap">

		<h2>Berichte</h2>

		<h2 class="nav-tab-wrapper">
			<a href="edit.php?post_type=bsds&page=bsd-verwaltung-report.php&tab=bsd_upcoming_bsds" class="nav-tab <?php echo $active_tab == 'bsd_upcoming_bsds' ? 'nav-tab-active' : ''; ?>">Kommende Dienste</a>
	        <a href="edit.php?post_type=bsds&page=bsd-verwaltung-report.php&tab=bsd_userstatistics" class="nav-tab <?php echo $active_tab == 'bsd_userstatistics' ? 'nav-tab-active' : ''; ?>">Userstatistik</a>
		</h2>

		<br><br>

		<?php

		switch ( $active_tab ) {

			case 'bsd_upcoming_bsds':
				echo bsd_upcoming_bsds();
				break;

			case 'bsd_userstatistics':
				echo bsd_userstatistics();
				break;

		}

		?>

	</div>

	<?php

}

function bsd_upcoming_bsds() {

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
		'meta_key'         => '_bsd_begin_date',
		'meta_query'       => array(
			'key'     => '_bsd_begin_date',
			'value'   => date( "Y-m-d h:i:s" ),
			'compare' => '>=',
			'type'    => 'DATE'
		),
		'orderby'          => 'meta_value',
		'order'            => 'ASC'
	);

	$posts_array = get_posts( $args );

	?>
	<table id="bsd_export_table" class="wp-list-table widefat fixed striped posts">
		<thead>
			<tr>
				<th width="5%">#</th>
				<th width="20%">Dienst</th>
				<th>Ort</th>
				<th>Datum</th>
				<th>Beginn</th>
				<th>zu besetzen / besetzt</th>
				<th width="30%">gesetzte Personen</th>
			</tr>
		</thead>
		<tbody>

	<?php

	$x = 1;

	foreach ( $posts_array AS $post ) {

		$date = strtotime( date( 'd.m.Y', time() ) );

		$bsd_date = strtotime( date( 'd.m.Y', strtotime( get_post_meta( $post->ID, '_bsd_begin_date', true ) ) ) );

		if ( $bsd_date < $date ) {
			continue;
		}

		$fix_users = bsd_get_event_data( 0, $post->ID, $is_fix = true, $return_type = 'events_on_post' );

		?>

		<tr>
			<td><?php echo $x; ?></td>
			<td><?php echo $post->post_title; ?></td>
			<td><?php echo get_post_meta( $post->ID, '_bsd_location', true ); ?></td>
			<td><?php echo date( 'd.m.Y', $bsd_date ); ?></td>
			<td><?php echo get_post_meta( $post->ID, '_bsd_begin_time', true ) . ' Uhr'; ?></td>
			<td><?php echo get_post_meta( $post->ID, '_bsd_count_persons', true ) . ' / ' . count( $fix_users ); ?></td>

			<?php

			echo '<td>';

			if ( count( $fix_users ) !== 0 ) {

				$fix_users_output = '';

				foreach ( $fix_users AS $fix_user ) {

					$fix_users_output .= get_userdata( $fix_user->user_id )->display_name . ', ';

				}

				echo substr( $fix_users_output, 0, -2 );
			}

			echo '</td>';

			?>

		</tr>

		<?php
		$x++;
	}

	?>
		</tbody>
	</table><br>

    <button id="bsd_report_print_button" class="button button-primary" onclick="bsd_print_upcoming_bsds_report()" >Drucken</button>
	<?php
}

function bsd_userstatistics() {

	$get_users = get_users();

	$users = array();

	foreach ( $get_users AS $get_user ) {

		$bsd_data = bsd_get_event_data( $get_user->ID, 0, $is_fix = true, $return_type = 'events_on_user' );

		$users[$get_user->ID] = array(
			'bsd_count' => count( $bsd_data ),
			'name' => $get_user->display_name
		);
	}

	rsort($users);

	?>

	<table id="bsd_report_userstatistics_table" class="wp-list-table widefat fixed striped posts">
		<thead>
		<tr>
			<th width="5%">#</th>
			<th width="20%">User</th>
			<th>Anzahl der Dienste</th>
		</tr>
		</thead>
		<tbody>

	<?php

	$x = 1;

	foreach ( $users AS $user ) {
		?>
		<tr>
			<td><?php echo $x; ?></td>
			<td><?php echo $user['name']; ?></td>
			<td><?php echo $user['bsd_count']; ?></td>
		</tr>

		<?php

		$x++;
	}

	?>

		</tbody>
	</table><br>

    <button id="bsd_report_print_button" class="button button-primary" onclick="bsd_print_bsd_user_statistics_report()" >Drucken</button>

	<?php

}