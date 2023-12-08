;( function( $, d, w ) {
const formHtml = `<div id="shortpixelContactForm" class="shortpixelContactForm">
    <div class="form-messages"></div>
    <form action="/admin-ajax.php" method="POST">
        <input type="hidden" name="source" value="">
        <input type="hidden" name="quriobot_answers" value="">
        <p>${ccssContactFormLocal.description}</p>
        <input type="text" name="name" value="" placeholder="${ccssContactFormLocal.name}" required="required">
        <input type="email" name="email" value="" placeholder="${ccssContactFormLocal.email}" required="required">
        <textarea rows="6" name="message" value=""
                  placeholder="${ccssContactFormLocal.texthint}" required="required"
                  ></textarea>
        <input type="submit" name="submit" value="${ccssContactFormLocal.send}">
    </form>
</div>`;
const buttonHtml = `
<div id="shortpixelContactFormWidgetButton" class="shortpixelContactFormWidgetButton" tabindex="0" role="button" 
     title="${ccssContactFormLocal.buttonTitle}">
    <i class="glyphicon glyphicon-remove" title="${ccssContactFormLocal.close}"></i>
    <p>${ccssContactFormLocal.buttonText}</p>
</div>
`;
$('#shortpixelContactFormContainer').html(formHtml + buttonHtml);
$('body').on('mouseover', '#shortpixelContactFormWidgetButton', function(){
    $('#shortpixelContactFormWidgetButton').addClass('hovered');
});
$('body').on('mouseout', '#shortpixelContactFormWidgetButton', function(){
    $('#shortpixelContactFormWidgetButton').removeClass('hovered');
});
$('body').on('click', '#shortpixelContactFormWidgetButton', function(){
    if($('#shortpixelContactFormWidgetButton').hasClass('opened')) {
        $('#shortpixelContactFormWidgetButton').removeClass('opened');
        $('#shortpixelContactForm').removeClass('opened');

    } else {
        $('#shortpixelContactForm .form-messages').hide();
        $('#shortpixelContactForm input[type=submit]').prop('disabled', false);
        $('#shortpixelContactFormWidgetButton').addClass('opened');
        $('#shortpixelContactForm').addClass('opened').removeClass('sent');
    }

});
$('body').on('click', '#shortpixelContactFormWidgetButton.hovered i', function(e){
    e.preventDefault();
    $('#shortpixelContactFormWidgetButton').fadeOut();
    return false;
});
$('body').on('submit', '#shortpixelContactForm form', function(e) {
    e.preventDefault();
    $('#shortpixelContactForm div.form-messages').hide().removeClass('error').removeClass('success');
    const form = $(this);
    let data = form.serializeArray()
    data = data.concat({
            name: 'action',
            value: 'shortpixel_critical_css_contact',
        },{
            name: 'submit',
            value: 'Send',
        },
    );

    $.ajax( {
        url        : 'admin-ajax.php',
        method     : 'post',
        data       : data,
        beforeSend : function() {
            form.find('input[type=submit]').prop('disabled', true);
        },
        error      : function( error ) {
            $('#shortpixelContactForm div.form-messages')
                .addClass('error')
                .text( ccssContactFormLocal.formError )
                .fadeIn();
            form.find('input[type=submit]').prop('disabled', false);
        },
        success    : function( data ) {
            if(data && data.response && data.response.search('input_error') != -1) {
                form.find('input[type=submit]').prop('disabled', false);
                $('#shortpixelContactForm div.form-messages')
                    .addClass('error')
                    .text( ccssContactFormLocal.formError )
                    .fadeIn();
            } else {
                $('#shortpixelContactForm').addClass('sent');
                $('#shortpixelContactForm form')[0].reset();
                $('#shortpixelContactForm div.form-messages')
                    .addClass('updated')
                    .text( ccssContactFormLocal.formSuccess )
                    .fadeIn();
            }

        },
        complete   : function(jqxr, textStatus) {


        }
    } );

})
} )( jQuery, document, window );


