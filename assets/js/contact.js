/*
* ---------------------------------------------------------------------------
* assets/js/contact.js
* ---------------------------------------------------------------------------
* Paste this file at: wp-rentals/assets/js/contact.js
*/


(function($){
if ( typeof jQuery === 'undefined' ) return;
$(function(){
var $form = $('#wpr-inquiry-form');
if ( !$form.length ) return;


$form.on('submit', function(e){
e.preventDefault();
var $btn = $form.find('button[type="submit"]');
var $status = $form.find('.wpr-inquiry-status');
$status.text('');
$btn.prop('disabled', true).text('Sending...');


var data = $form.serializeArray();
// Add nonce explicitly for AJAX endpoint
data.push({ name: 'nonce', value: WPR_Contact.nonce });


$.ajax({
url: WPR_Contact.ajax_url,
method: 'POST',
data: data,
dataType: 'json'
}).done(function( resp ){
if ( resp.success ) {
$status.css('color','green').text( resp.data.message || 'Inquiry sent' );
$form[0].reset();
} else {
var msg = (resp.data && resp.data.message) ? resp.data.message : 'Error sending inquiry';
$status.css('color','red').text( msg );
}
}).fail(function(xhr){
var msg = 'Error sending inquiry';
try { msg = xhr.responseJSON.data.message || msg; } catch(e){}
$status.css('color','red').text( msg );
}).always(function(){
$btn.prop('disabled', false).text('Send Inquiry');
});
});
});
})(jQuery);