// File: assets/js/search.js
(function(){
    if ( typeof jQuery === 'undefined' ) return;
    var $ = jQuery;

    function debounce(fn, delay){
        var t;
        return function(){
            var args = arguments;
            clearTimeout(t);
            t = setTimeout(function(){ fn.apply(null, args); }, delay);
        };
    }

    function buildQuery($form, page){
        var data = {};
        data.s = $form.find('#wpr_s').val();
        data.property_type = $form.find('#wpr_property_type').val();
        data.property_city = $form.find('#wpr_property_city').val();
        data.property_area = $form.find('#wpr_property_area').val();
        data.min_price = $form.find('#wpr_min_price').val();
        data.max_price = $form.find('#wpr_max_price').val();
        data.bedrooms = $form.find('#wpr_bedrooms').val();
        data.bathrooms = $form.find('#wpr_bathrooms').val();
        data.sort = $form.find('#wpr_sort').val();
        data.page = page || 1;
        data.per_page = (window.WPR_SEARCH_CFG && window.WPR_SEARCH_CFG.per_page) ? window.WPR_SEARCH_CFG.per_page : 9;
        return data;
    }

    function renderResults(container, data){
        var $c = $(container);
        $c.empty();
        if ( ! data.results || data.results.length === 0 ){
            $c.html('<p>' + (window.WPR_Search_NoResults || 'No properties found.') + '</p>');
            $('#wpr-search-pagination').empty();
            return;
        }

        var html = '<div class="wpr-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">';
        data.results.forEach(function(r){
            html += '<div class="wpr-card" style="border:1px solid #e1e1e1;padding:12px;border-radius:6px;background:#fff;">';
            if ( r.thumbnail ) html += '<a href="'+r.permalink+'"><img src="'+r.thumbnail+'" alt="'+escapeHtml(r.title)+'" style="width:100%;height:160px;object-fit:cover;border-radius:4px;" /></a>';
            html += '<h3 style="font-size:16px;margin:10px 0;"><a href="'+r.permalink+'">'+escapeHtml(r.title)+'</a></h3>';
            if ( r.price !== null ) html += '<div style="font-weight:600">'+r.price+'</div>';
            html += '<div style="font-size:13px;color:#666;">'+(r.excerpt || '')+'</div>';
            html += '<p style="margin-top:8px;"><a class="button" href="'+r.permalink+'">View</a></p>';
            html += '</div>';
        });
        html += '</div>';
        $c.html(html);

        // pagination
        renderPagination('#wpr-search-pagination', data.page, data.pages);
    }

    function renderPagination(container, current, pages){
        var $wrap = $(container);
        $wrap.empty();
        if ( pages <= 1 ) return;
        var html = '<div class="wpr-pagination" style="display:flex;gap:6px;flex-wrap:wrap;">';
        for ( var i=1;i<=pages;i++ ){
            var cls = (i==current)? 'background:#0073aa;color:#fff;padding:6px 10px;border-radius:4px;text-decoration:none;':'padding:6px 10px;border-radius:4px;background:#f1f1f1;text-decoration:none;color:#333;';
            html += '<a href="#" data-page="'+i+'" style="'+cls+'">'+i+'</a>';
        }
        html += '</div>';
        $wrap.html(html);
    }

    function escapeHtml(text) {
        return String(text).replace(/[&<>"'`=\/]/g, function (s) { return '&#' + s.charCodeAt(0) + ';'; });
    }

    function doSearch($form, page){
        var container = '#wpr-search-results';
        var data = buildQuery($form, page);
        var url = (window.WPR_SEARCH && window.WPR_SEARCH.rest_url) ? window.WPR_SEARCH.rest_url : '/wp-json/wpr/v1/search';
        $('#wpr-search-results').html('<p>Loading…</p>');
        $.ajax({
            url: url,
            method: 'GET',
            data: data,
            dataType: 'json'
        }).done(function(resp){
            renderResults(container, resp);
        }).fail(function(xhr){
            $('#wpr-search-results').html('<p>Error fetching results</p>');
        });
    }

    $(document).ready(function(){
        var $form = $('#wpr-search-form');
        if ( ! $form.length ) return;

        var debounced = debounce(function(){ doSearch($form, 1); }, 350);

        $form.on('change', 'select, input', debounced );
        $form.on('input', '#wpr_s', debounced );

        $form.on('click', '#wpr_search_btn', function(e){ e.preventDefault(); doSearch($form,1); });

        // pagination clicks
        $(document).on('click', '#wpr-search-pagination a', function(e){
            e.preventDefault();
            var page = $(this).data('page');
            doSearch($form, page);
        });

        // initial load
        doSearch($form, 1);
    });
})();
