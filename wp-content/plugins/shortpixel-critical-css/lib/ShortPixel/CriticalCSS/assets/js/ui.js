;( function( $, d, w ) {
    $(function(){
        $('#ccssFlushWebCheck').click(function(e){
            e.preventDefault();

            $.ajax( {
                url        : 'admin-ajax.php',
                method     : 'post',
                data       : {
                    action : 'shortpixel_critical_css_force_web_check'
                },
                beforeSend : function() {
                    $('#ccssFlushWebCheck').prop('disabled', true).addClass('disabled');
                },
                error      : function( error ) {

                },
                success    : function( data ) {
                    if(data && data.status && data.status == 'ok') {
                        $('h2.nav-tab-wrapper').before(`<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"><p><strong>${ccssUILocal.forceWebCheck}</strong></p> </div>`);
                    }

                },
                complete   : function(){
                    $('#ccssFlushWebCheck').prop('disabled', false).removeClass('disabled');
                }
            } );

        });

        $('input[name="shortpixel_critical_css[cache_mode][postTypes]"]').parent().after($('<div>').addClass('spCcssPostTypes'));
        $('input[name="shortpixel_critical_css[cache_mode][templates]"]').parent().after($('<div>').addClass('spCcssTemplates'));

        $('.post_type_values fieldset input[type=hidden], .post_type_values fieldset label').each(function(index, element){
            $(element).detach();
            $('.spCcssPostTypes').append(element);

        });
        $('.template_values fieldset input[type=hidden], .template_values fieldset label').each(function(index, element){
            $(element).detach();
            $('.spCcssTemplates').append(element);
        });

        $('body').on('change', '#wpuf-shortpixel_critical_css\\[cache_mode\\]\\[postTypes\\]', function(e){
            switchSuboptionsBlock(this, $('.spCcssPostTypes'));
        });
        $('body').on('change', '#wpuf-shortpixel_critical_css\\[cache_mode\\]\\[templates\\]', function(e){
            switchSuboptionsBlock(this, $('.spCcssTemplates'));
        });
        switchSuboptionsBlock('#wpuf-shortpixel_critical_css\\[cache_mode\\]\\[postTypes\\]', $('.spCcssPostTypes'));
        switchSuboptionsBlock('#wpuf-shortpixel_critical_css\\[cache_mode\\]\\[templates\\]', $('.spCcssTemplates'));
        //disable to putt off all three modes, one should be always on
        $('#wpuf-shortpixel_critical_css\\[cache_mode\\]\\[postTypes\\],#wpuf-shortpixel_critical_css\\[cache_mode\\]\\[templates\\],#wpuf-shortpixel_critical_css\\[cache_mode\\]\\[posts\\]').change(function(){
            if( $(this).parents('fieldset').find('>label>input[type=checkbox]:checked').length < 1 ){
                $(this).prop('checked', true);
            }

        })
    });
    function switchSuboptionsBlock(option, block)
    {
        if($(option).is(':checked')){
            $(block).css('display', 'block');
            //activate all values if nothing checked
            if($(block).find('input:checked').length < 1) {
                $(block).find('input[type=checkbox]').prop('checked', true);
            }
        } else {
            $(block).css('display', 'none');
        }
    }
} )( jQuery, document, window );
