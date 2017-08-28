jQuery( function() {

    // Add Color Picker to all inputs that have 'color-field' class
    jQuery( function() {
        jQuery( '.color-field' ).wpColorPicker();
    } );
} );

jQuery( document ).ready( function () {

    jQuery( "input[name='bsd_attendants[]']" ).each( function () {

        var user_id = jQuery( this ).attr( 'value' );
        var checked = jQuery( this ).attr( 'checked' );
        var radio = jQuery( '#bsd_leader_'+user_id );

        if ( !checked ) {
            jQuery( '#bsd_leader_'+user_id ).attr( 'disabled', 'disabled' );
            jQuery( '#bsd_leader_'+user_id ).removeAttr( 'checked' );
        } else {
            jQuery( '#bsd_leader_'+user_id ).removeAttr( 'disabled' );
        }

        jQuery( this ).click( function () {
            if ( jQuery( this ).is( ':checked' ) ) {
                radio.removeAttr( 'disabled' );
            } else  {
                radio.attr( 'disabled', 'disabled' );
                radio.removeAttr( 'checked' );
            }
        } )

    } );

    jQuery( "#bsd_begin_date" ).datepicker( {
        dateFormat: 'dd.mm.yy',
        minDate:    0
    } );

    jQuery( "#bsd_begin_time" ).timepicker( {
        'timeFormat': 'H:i'
    } );

});

function bsd_print_upcoming_bsds_report() {

    var divToPrint=document.getElementById( "bsd_export_table" );
    newWin= window.open( "" );

    newWin.document.write(
        "<style>table { width: 100%; border: 1px solid; text-align: left; font-family: Arial; } th,tr,td { border: 1px solid; padding: 2px; }</style>"
    );

    newWin.document.write( divToPrint.outerHTML );
    newWin.print();
    newWin.close();

}


function bsd_print_bsd_user_statistics_report() {

    var divToPrint=document.getElementById( "bsd_report_userstatistics_table" );
    newWin= window.open( "" );

    newWin.document.write(
        "<style>table { width: 100%; border: 1px solid; text-align: left; font-family: Arial; } th,tr,td { border: 1px solid; padding: 2px; }</style>"
    );

    newWin.document.write( divToPrint.outerHTML );
    newWin.print();
    newWin.close();

}