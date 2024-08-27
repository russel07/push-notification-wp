jQuery(document).ready(function($) {
    const apiUrl = spnwp_vars.apiUrl;
    
    // Toggle drawer and overlay on button click
    $('#spnwp-notifications-btn').click(function(event) {
        event.stopPropagation();
        $('.spnwp-notification-drawer').toggleClass('open');
        $('.spnwp-notification-overlay').removeClass('close');
    });

    // Close drawer if clicking outside of it
    $(document).click(function(event) {
        if (!$(event.target).closest('.spnwp-notification-drawer').length && 
            !$(event.target).is('#spnwp-notifications-btn')) {
            $('.spnwp-notification-overlay').addClass('close');
            $('.spnwp-notification-drawer').removeClass('open');
        }
    });

    // Fetch notifications from the API
    function get_notification() {
        let url = apiUrl + 'notifications';

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                var data = response;
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data:', error);
            }
        });
    }

    // Call the function on page load
    get_notification();
});

