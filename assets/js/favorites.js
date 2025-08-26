// ------------------ assets/js/favorites.js ------------------
/*
 * Frontend script to toggle favorites
 */
(function($){
    if ( typeof jQuery === 'undefined' ) return;
    $(function(){
        $(document).on('click', '.wpr-fav-btn', function(e){
            e.preventDefault();
            var $btn = $(this);
            var post_id = $btn.data('id');
            if (!post_id) return;
            var original = $btn.text();
            $btn.prop('disabled', true);
            $.post(WPR_FAV.ajax_url, { action: 'wpr_toggle_favorite', post_id: post_id, nonce: WPR_FAV.nonce }).done(function(resp){
                if ( resp && resp.success ) {
                    if ( resp.data.action === 'added' ) {
                        $btn.addClass('wpr-favorited');
                        $btn.attr('aria-pressed','true');
                        $btn.text('Saved');
                    } else {
                        $btn.removeClass('wpr-favorited');
                        $btn.attr('aria-pressed','false');
                        $btn.text('Save to Favorites');
                    }
                } else {
                    alert( (resp && resp.data && resp.data.message) ? resp.data.message : 'Error' );
                }
            }).fail(function(){
                alert('Error communicating with server');
            }).always(function(){
                $btn.prop('disabled', false);
            });
        });

        // On page load, mark favorited buttons (for logged-in we can request list)
        if ( WPR_FAV.logged_in ) {
            // nothing to do: server-rendered button state is preferred
        } else {
            // for guests, try to read cookie and mark buttons
            try {
                var cookie = document.cookie.match('(^|;)\\s*' + encodeURIComponent('wpr_favorites') + '\\s*=\\s*([^;]+)');
                if ( cookie && cookie.length > 2 ) {
                    var csv = decodeURIComponent(cookie[2]);
                    var ids = csv.split(',');
                    ids.forEach(function(id){
                        $('.wpr-fav-btn[data-id="'+id+'"]').addClass('wpr-favorited').attr('aria-pressed','true').text('Saved');
                    });
                }
            } catch(e){}
        }
    });
})(jQuery);


