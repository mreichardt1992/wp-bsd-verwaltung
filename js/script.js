jQuery( document ).ready( function () {
    jQuery( "#bsd-panels" ).on( 'click', '.bsd-widget-title', function ( e ) {
        jQuery( this ).next( '.bsd-widget-content' ).toggle( 200 );
        jQuery( this ).parents( '.bsd-widget' ).toggleClass( 'active' );
    } );
} );

function bsd_book_user_on_event( user_id, post_id, nonce ) {
    jQuery.ajax( {
        type: "POST",
        url: global.ajaxurl,
        data: {
            action: 'bsd_book_user_on_event',
            post_id: post_id,
            user_id: user_id,
            nonce: nonce
        },
        success: function () {

            jQuery( '.accept_bsd_button_' + post_id ).html( 'Meldung zur&uuml;ckziehen' );
            jQuery( '.accept_bsd_button_' + post_id ).attr( "onclick","bsd_unbook_user_from_event( '"+post_id+"', '"+user_id+"', '"+nonce+"' );" );

            jQuery( '.accept_bsd_button_table_' + post_id ).html( 'Meldung zur&uuml;ckziehen' );
            jQuery( '.accept_bsd_button_table_' + post_id ).attr( "onclick","bsd_unbook_user_from_event( '"+post_id+"', '"+user_id+"', '"+nonce+"' );" );
        }
    } );
}

function bsd_unbook_user_from_event( post_id, user_id, nonce ) {
    jQuery.ajax( {
        type: "POST",
        url: global.ajaxurl,
        data: {
            action: 'bsd_unbook_user_from_event',
            user_id: user_id,
            post_id: post_id,
            nonce: nonce
        },
        success: function () {

            jQuery( '.accept_bsd_button_' + post_id ).html( 'Melden' );
            jQuery( '.accept_bsd_button_' + post_id ).attr( "onclick","bsd_book_user_on_event( '"+user_id+"', '"+post_id+"', '"+nonce+"' );" );

            jQuery( '#is-fix-text-' + post_id ).css( 'display', 'none' );

            jQuery( '.accept_bsd_button_table_' + post_id ).html( 'Melden' );
            jQuery( '.accept_bsd_button_table_' + post_id ).attr( "onclick","bsd_book_user_on_event( '"+user_id+"', '"+post_id+"', '"+nonce+"' );" );

            jQuery( '#is-fix-text-table-' + post_id ).css( 'display', 'none' );
        }
    } );
}
