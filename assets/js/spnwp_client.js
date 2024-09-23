jQuery(document).ready(function($) {
    const apiUrl = spnwp_vars.apiUrl;
    const isLoggedIn = spnwp_vars.is_logged_in;

    let activeNotifications = [];
    let dismissedNotifications = [];

    const SELECTORS = {
        notificationBtn: '#spnwp-notifications-btn',
        notificationDrawer: '.spnwp-notification-drawer',
        notificationOverlay: '.spnwp-notification-overlay',
        closeBtn: '#spnwp-close',
        notificationHeader: '.spnwp-notification-header',
        notificationWrapper: '.spnwp-notification-wrapper',
        toggleDismissed: '.toggle-to-dismiss-notification',
        toggleActive: '.toggle-to-active-notification',
        notificationCounterHolder: '.spnwp-notification-counter-holder',
        dismissNotification: '.spnwp-dismiss',
    };

    // Event listeners for user interactions
    const setupEventListeners = () => {
        $(SELECTORS.notificationBtn).on('click', toggleDrawer);
        $(document).on('click', handleDocumentClick);
        bindCloseButton();
        $(document).on('click', SELECTORS.toggleDismissed, showDismissedNotifications);
        $(document).on('click', SELECTORS.toggleActive, showActiveNotifications);
        $(document).on('click', SELECTORS.dismissNotification, dismissNotification);
    };

    // Bind the close button event listener (call after close button is rendered)
    const bindCloseButton = () => {
        $(document).off('click', SELECTORS.closeBtn).on('click', SELECTORS.closeBtn, closeDrawer);
    };

    // Toggle notification drawer and overlay
    const toggleDrawer = (event) => {
        event.stopPropagation();
        $(SELECTORS.notificationDrawer).toggleClass('open');
        $(SELECTORS.notificationOverlay).removeClass('close');
    };

    // Handle click outside of the notification drawer to close it
    const handleDocumentClick = (event) => {
        if (!$(event.target).closest(SELECTORS.notificationDrawer).length &&
            !$(event.target).is(SELECTORS.notificationBtn)) {
            closeDrawer();
        }
    };

    // Close notification drawer
    const closeDrawer = (event) => {
        if (event) event.stopPropagation();
        $(SELECTORS.notificationOverlay).addClass('close');
        $(SELECTORS.notificationDrawer).removeClass('open');
    };

    // Show dismissed notifications
    const showDismissedNotifications = (event) => {
        event.stopPropagation();
        updateHeader('dismissed');
        renderNotifications(dismissedNotifications, 'dismissed');
    };

    // Show active notifications
    const showActiveNotifications = (event) => {
        event.stopPropagation();
        updateHeader();
        renderNotifications(activeNotifications);
    };

    // Dismiss a notification via API
    const dismissNotification = (event) => {
        event.stopPropagation();
        const notificationId = $(event.target).data('id');
        const url = `${apiUrl}notifications`;

        $.ajax({
            url: url,
            method: 'POST',
            data: { id: notificationId },
            dataType: 'json',
            success: function(response) {
                activeNotifications = response.active_notifications || [];
                dismissedNotifications = response.dismissed_notifications || [];
                updateHeader();
                renderNotifications(activeNotifications);
            },
            error: handleError
        });
    };

    // Update notification header HTML and bind the close button
    const updateHeader = (currentTab = 'active') => {
        let headerHtml = `<span class="spnwp-notifications">(${activeNotifications.length}) New Notifications</span>`;

        if (currentTab === 'active' && dismissedNotifications.length) {
            headerHtml += `<div class="spnwp-toggle-notification"><a class="toggle-to-dismiss-notification" href="#">Dismissed Notifications</a></div>`;
        } else if (currentTab === 'dismissed') {
            headerHtml += `<div class="spnwp-toggle-notification"><a class="toggle-to-active-notification" href="#">Active Notifications</a></div>`;
        }

        headerHtml += `<span id="spnwp-close" class="spnwp-close">X</span>`;
        $(SELECTORS.notificationHeader).html(headerHtml);

        // Re-bind the close button click after rendering
        bindCloseButton();
        updateNotificationCounter();
    };

    // Update notification counter
    const updateNotificationCounter = () => {
        const counterHolder = $(SELECTORS.notificationCounterHolder);
        const count = activeNotifications.length;

        counterHolder.toggleClass('animate-bounce', count > 0);
        counterHolder.text(count || 0);
    };

    // Render notifications
    const renderNotifications = (notifications, currentTab = 'active') => {
        let html = '';

        if (notifications.length) {
            notifications.forEach(notification => {
                html += `
                    <div class="spnwp-notification">
                        <div class="spnwp-notification-title">
                            <div class="title">${notification.title}</div>
                            <div class="date">${getDateDifference(notification.start_date)} ago</div>
                        </div>
                        <div class="spnwp-notification-content">${notification.content}</div>
                        <div class="spnwp-actions">
                            ${notification.main ? `<a href="${notification.main.url}" class="spnwp-btn spnwp-btn-primary" target="_blank">${notification.main.text}</a>` : ''}
                            ${notification.alt ? `<a href="${notification.alt.url}" class="spnwp-link spnwp-link-primary" target="_blank">${notification.alt.text}</a>` : ''}
                            ${currentTab !== 'dismissed' ? `<a href="#" data-id="${notification.id}" class="spnwp-dismiss spnwp-link spnwp-link-primary">Dismiss</a>` : ''}
                        </div>
                    </div>`;
            });
        } else {
            html = `<div class="spnwp-notification"><h1>No ${currentTab === 'dismissed' ? 'dismissed' : 'new'} notifications</h1>`;
            if (currentTab === 'dismissed') {
                html += 'Go to <a class="toggle-to-active-notification text-blue" href="#">Active Notifications</a>';
            }
        }

        $(SELECTORS.notificationWrapper).html(html);
    };

    // Calculate time difference and return in appropriate unit
    const getDateDifference = (startDate) => {
        const start = new Date(startDate);
        const now = new Date();
        const diffMs = now - start;

        const seconds = Math.floor(diffMs / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
        const months = Math.floor(days / 30);

        if (months > 0) return `${months} month${months > 1 ? 's' : ''}`;
        if (days > 0) return `${days} day${days > 1 ? 's' : ''}`;
        if (hours > 0) return `${hours} hour${hours > 1 ? 's' : ''}`;
        if (minutes > 0) return `${minutes} minute${minutes > 1 ? 's' : ''}`;
        return `${seconds} second${seconds > 1 ? 's' : ''}`;
    };

    // Fetch notifications from API
    const fetchNotifications = () => {
        const url = `${apiUrl}notifications`;

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                activeNotifications = response.active_notifications || [];
                dismissedNotifications = response.dismissed_notifications || [];
                updateHeader();
                renderNotifications(activeNotifications);
            },
            error: handleError
        });
    };

    // Handle AJAX errors
    const handleError = (xhr, status, error) => {
        console.error('Error fetching data:', error);
    };

    // Initialize notifications if the user is logged in
    if (isLoggedIn) {
        fetchNotifications();
    }

    // Set up event listeners
    setupEventListeners();
});
