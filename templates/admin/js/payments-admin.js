// File: wp-rentals/admin/js/payments-admin.js
(function($){
    $(function(){
        $(document).on('click', '.wpr-verify-btn, .wpr-reject-btn', function(e){
            e.preventDefault();
            var $btn = $(this);
            var id = $btn.data('id');
            var action = $btn.hasClass('wpr-verify-btn') ? 'verify' : 'reject';
            if (!id) return;
            var original = $btn.text();
            $btn.prop('disabled', true).text( action === 'verify' ? WPR_PAY_ADMIN.i18n.verifying : WPR_PAY_ADMIN.i18n.rejecting );

            $.post(WPR_PAY_ADMIN.ajax_url, { action: 'wpr_admin_verify_payment', payment_id: id, do: action, nonce: WPR_PAY_ADMIN.nonce }, function(resp){
                if ( resp && resp.success ) {
                    var msg = resp.data.message || 'OK';
                    // update status cell
                    $('.wpr-pay-status[data-id="'+id+'"]').text( action === 'verify' ? 'Verified' : 'Rejected' );
                    // remove verify/reject actions
                    $btn.closest('tr').find('.wpr-verify-btn, .wpr-reject-btn').remove();
                    // show WP admin notice
                    $('<div class="updated inline"><p>'+msg+'</p></div>').insertBefore('.wrap h1').delay(2500).fadeOut();
                } else {
                    alert( (resp && resp.data && resp.data.message) ? resp.data.message : 'Error' );
                }
            }).fail(function(){ alert('Request failed'); }).always(function(){ $btn.prop('disabled', false).text(original); });
        });
    });
})(jQuery);
