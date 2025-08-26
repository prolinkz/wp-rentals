/**
## 4) JS for display toggle: `assets/js/display.js`

Path: `wp-rentals/assets/js/display.js`

```javascript

 */ 

(function($){
    if ( typeof jQuery === 'undefined' ) return;
    $(function(){
        var $container = $('#wpr-archive-list');
        function setMode(m){
            if ( m === 'list' ) {
                $container.removeClass('wpr-grid').addClass('wpr-list');
                localStorage.setItem('wpr_view', 'list');
            } else {
                $container.removeClass('wpr-list').addClass('wpr-grid');
                localStorage.setItem('wpr_view', 'grid');
            }
        }
        $('#wpr-toggle-list').on('click', function(){ setMode('list'); });
        $('#wpr-toggle-grid').on('click', function(){ setMode('grid'); });
        var saved = localStorage.getItem('wpr_view') || 'grid'; setMode(saved);
    });
})(jQuery);