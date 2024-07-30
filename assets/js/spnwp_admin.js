jQuery(document).ready(function($) {
    $('#title').attr('required', 'required');
    $('#publish').click(function(e) {
        var title = $('#title').val();
        $('#title-error').remove();
        if ( ! title ) {
            $('#titlewrap').append('<div id="title-error" style="color: red; margin-top: 5px;">Notification title is required!</div>');
            $('#title').focus();
            e.preventDefault();
        } else {
            $('#post').submit();
        }
    });
});