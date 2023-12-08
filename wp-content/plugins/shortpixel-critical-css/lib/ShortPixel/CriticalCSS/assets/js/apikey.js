;( function( $, d, w ) {
$(function(){
    let data = {
        action : 'shortpixel_critical_css_getapikey',
    };

    $.ajax( {
        url        : 'admin-ajax.php',
        method     : 'post',
        data       : data,
        success    : function( data ) {
            const apiKey = data.key || '';
            $('tr.apikey td').html('<input type="hidden" id="ccssApiKeyDefault"  value=""><input type="text" class="regular-text" id="ccssApiKey" value="" autocomplete="off">\n' +
                '<button id="ccssApikeyUpdate" class="button button-primary" style="display: none;"></button>\n' +
                '<p class="notice notice-error hidden" style="margin-left: 0;"></p>' +
                '<p class="description"></p>')
            $('#ccssApiKeyDefault').val(apiKey);
            $('#ccssApiKey').val(apiKey);
            $('#ccssApikeyUpdate').text( ccssApikeyLocal.buttonText );
            $('tr.apikey td .description').html( apiKey=='' ? ccssApikeyLocal.descriptionRegister : ccssApikeyLocal.descriptionLogin );
        }
    } );
});
$('body').on('keyup','#ccssApiKey',function(e){
    if( $('#ccssApiKeyDefault').val() == $('#ccssApiKey').val() ) {
        $('#ccssApikeyUpdate').hide();
    } else {
        $('#ccssApikeyUpdate').show();
    }
});
$('body').on('click','#ccssApikeyUpdate',function(e){
    e.preventDefault();
    let data = {
        action : 'shortpixel_critical_css_updateapikey',
        key    : $('#ccssApiKey').val(),
    };
    $.ajax( {
        url        : 'admin-ajax.php',
        method     : 'post',
        data       : data,
        success    : function( data ) {
            if(data.status == 'error') {
                $('tr.apikey td .notice-error').text(data.error).show();
            } else {
                location.reload();
            }
        }
    } );
})

} )( jQuery, document, window );