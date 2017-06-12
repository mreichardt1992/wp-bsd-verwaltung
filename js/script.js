$(window).ready(function () {
    $("#bsd-panels").on('click', '.bsd-widget-title', function (e) {
        $(this).next('.bsd-widget-content').toggle(200);
        $(this).parents('.bsd-widget').toggleClass('active');
    });
});

function bsd_book_user_on_event(user_id, post_id, nonce) {
    $.ajax({
        type: "POST",
        url: global.ajaxurl,
        data: {
            action: 'bsd_book_user_on_event',
            post_id: post_id,
            user_id: user_id,
            nonce: nonce
        },
        success: function () {

            $('.accept_bsd_button_' + post_id).html('Meldung zur&uuml;ckziehen');
            $('.accept_bsd_button_' + post_id).attr("onclick","bsd_unbook_user_from_event('"+post_id+"', '"+user_id+"', '"+nonce+"');");
        }
    });
}

function bsd_unbook_user_from_event(post_id, user_id, nonce) {
    $.ajax({
        type: "POST",
        url: global.ajaxurl,
        data: {
            action: 'bsd_unbook_user_from_event',
            user_id: user_id,
            post_id: post_id,
            nonce: nonce
        },
        success: function () {

            $('.accept_bsd_button_' + post_id).html('Melden');
            $('.accept_bsd_button_' + post_id).attr("onclick","bsd_book_user_on_event('"+user_id+"', '"+post_id+"', '"+nonce+"');");

            $('#is_fix_text_' + post_id).css('display', 'none');
        }
    });
}
