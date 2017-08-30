<?php


add_action( 'show_user_profile', 'bsd_extra_user_profile_fields' );
add_action( 'edit_user_profile', 'bsd_extra_user_profile_fields' );

function bsd_extra_user_profile_fields( $user ) {

	if ( current_user_can( 'administrator' ) && is_admin() ) {

		?>


        <h3>BSD Informationen</h3>

		<?php
		$leader = get_user_meta( $user->ID, 'bsd_leader', true );

		$is_leader = '';
		if ( 1 == $leader ) {
			$is_leader = esc_attr( 'checked="checked"' );
		}
		?>

        <table class="form-table">
            <tr>
                <th><label for="address">Wachf&uuml;hrer</label></th>
                <td>
                    <input type="checkbox" name="bsd_leader" value="1" <?php echo $is_leader; ?> >
                    <span class="description"><?php _e( "Darf diese Person Wachf&uuml;hrer sein?" ); ?></span>

                </td>
            </tr>
        </table>
		<?php
	}

}

add_action( 'personal_options_update', 'bsd_save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'bsd_save_extra_user_profile_fields' );

function bsd_save_extra_user_profile_fields( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}
	update_user_meta( $user_id, 'bsd_leader', $_POST['bsd_leader'] );
}
