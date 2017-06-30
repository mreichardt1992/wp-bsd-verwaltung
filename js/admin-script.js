jQuery(function() {

    // Add Color Picker to all inputs that have 'color-field' class
    jQuery(function() {
        jQuery('.color-field').wpColorPicker();
    });
});

jQuery(document).ready( function () {

    jQuery( "#bsd_begin_date" ).datepicker( {
        dateFormat: 'dd.mm.yy',
        minDate:    0
    } );

    jQuery( "#bsd_begin_time" ).timepicker( {
        'timeFormat': 'H:i'
    } );

} );
