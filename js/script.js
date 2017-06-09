$(window).ready(function () {
    $("#secondary").on('click', '.widget-title', function (e) {
        $(this).next('.widget-content').toggle(200);
        $(this).parents('.widget').toggleClass('active');
    });
});

function book_user_on_event(user_id, post_id, nonce) {
    $.ajax({
        type: "POST",
        url: global.ajaxurl,
        data: {
            action: 'book_user_on_event',
            post_id: post_id,
            user_id: user_id,
            nonce: nonce
        },
        success: function () {

            $('.accept_bsd_button_' + post_id).html('Meldung zur&uuml;ckziehen');
            $('.accept_bsd_button_' + post_id).attr("onclick","unbook_user_from_event('"+post_id+"', '"+user_id+"', '"+nonce+"');");
        }
    });
}

function unbook_user_from_event(post_id, user_id, nonce) {
    $.ajax({
        type: "POST",
        url: global.ajaxurl,
        data: {
            action: 'unbook_user_from_event',
            user_id: user_id,
            post_id: post_id,
            nonce: nonce
        },
        success: function () {

            $('.accept_bsd_button_' + post_id).html('Melden');
            $('.accept_bsd_button_' + post_id).attr("onclick","book_user_on_event('"+user_id+"', '"+post_id+"', '"+nonce+"');");

            $('#is_fix_text_' + post_id).css('display', 'none');
        }
    });
}
