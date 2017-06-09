<?php


add_action( 'show_user_profile', 'extra_user_profile_fields' );
add_action( 'edit_user_profile', 'extra_user_profile_fields' );

function extra_user_profile_fields( $user ) { ?>
    <h3>BSD Informationen</h3>

	<?php
        $leader = get_the_author_meta( 'bsd_leader', $user->ID );

        $is_leader = 'Test';
        if ($leader == 1) {
	        $is_leader = 'checked';
        }
    ?>


    <table class="form-table">
        <tr>
            <th><label for="address">Wachf&uuml;hrer</label></th>
            <td>
                <input type="checkbox" name="bsd_leader" value="1" <?php echo $is_leader; ?> >
                <span class="description"><?php _e("Darf diese Person Wachf&uuml;hrer sein?"); ?></span>

            </td>
        </tr>
    </table>
<?php }


add_action( 'personal_options_update', 'save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields' );

function save_extra_user_profile_fields( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}
	update_user_meta( $user_id, 'bsd_leader', $_POST['bsd_leader'] );
}
