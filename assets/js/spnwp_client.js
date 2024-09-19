jQuery(document).ready(function($) {
    const apiUrl = spnwp_vars.apiUrl;
    let active_notifications = '';
    let dismissed_notifications = '';
    // Toggle drawer and overlay on button click
    $('#spnwp-notifications-btn').click(function(event) {
        event.stopPropagation(); // Prevents event from bubbling up to the document
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

    // Close drawer if click on X (Close button)
    $(document).on('click', '#spnwp-close', function(event) {
        event.stopPropagation();  // Stop the event from reaching the document click handler
        $('.spnwp-notification-overlay').addClass('close');
        $('.spnwp-notification-drawer').removeClass('open');
    });

    // Show dismissed notifications
    $(document).on('click', '.toggle-to-dismiss-notification', function(event) {
        event.stopPropagation();
        append_header_html( 'dismissed' );
        render_body( dismissed_notifications, 'dismissed' );
    });

    // Show active notifications
    $(document).on('click', '.toggle-to-active-notification', function(event) {
        event.stopPropagation();
        append_header_html();
        render_body(active_notifications);
    });

    // Function to update header HTML
    function append_header_html( current_tab = 'active' ) {
        let header_html = '<span class="spnwp-notifications">';
        let notification_count = active_notifications.length ? active_notifications.length : 0;
        let notification_holder = $('.spnwp-notification-counter-holder');
        if (notification_count) {
            notification_holder.addClass('animate-bounce');
            notification_holder.text(notification_count);
        } else {
            notification_holder.removeClass('animate-bounce');
            notification_holder.text(0);
        }

        if ( current_tab === 'active' ) {
            header_html += '(' + notification_count + ')  New Notifications</span>';
            if (dismissed_notifications.length) {
                header_html += '<div class="spnwp-toggle-notification"><a class="toggle-to-dismiss-notification" href="#">Dismissed Notifications</a></div>';
            }
        } else {
            header_html += 'Notifications</span><div class="spnwp-toggle-notification"><a class="toggle-to-active-notification" href="#">Active Notifications</a></div>';
        }

        header_html += '<span id="spnwp-close" class="spnwp-close">X</span>';
        //$('.spnwp-notification-header').html(''); // Clears any existing content
        $('.spnwp-notification-header').html(header_html);
    }

    // Function to render the body of notifications
    function render_body( notifications, current_tab = 'active' ) {
        let html_body = '';

        $.each(notifications, function(index, notification) {
            html_body += '<div class="spnwp-notification">';
            html_body += '<div class="spnwp-notification-title"><div class="title">' + notification.title + '</div><div class="date">37 minutes ago</div></div>';
            html_body += '<div class="spnwp-notification-content">' + notification.content + '</div>';
            html_body += '<div class="spnwp-actions">';

            if (notification.main) {
                html_body += '<a href="' + notification.main.url + '" class="spnwp-btn spnwp-btn-primary" target="_blank">' + notification.main.text + '</a>';
            }

            if (notification.alt) {
                html_body += '<a href="' + notification.alt.url + '" class="spnwp-link spnwp-link-primary" target="_blank">' + notification.alt.text + '</a>';
            }

            html_body += '<a href="#" data-id="'+ notification.id +'" class="spnwp-dismiss spnwp-link spnwp-link-primary">Dismiss</a></div></div>';
        });
        //$('.spnwp-notification-wrapper').html(''); // Clears any existing content
        $('.spnwp-notification-wrapper').html(html_body); // Ensure content is replaced, not appended repeatedly
    }

    // Fetch notifications from the API
    function get_notification() {
        let url = apiUrl + 'notifications';

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                active_notifications = response.active_notifications ? response.active_notifications : [];
                dismissed_notifications = response.dismissed_notifications ? response.dismissed_notifications : [];
                append_header_html();
                render_body(active_notifications);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching data:', error);
            }
        });
    }

    // Call the function on page load
    get_notification();
});
