<?php
namespace Rus\Notification;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main Plugin Initialization Class
 */
final class PluginInit {

    /**
     * The single instance of the class
     *
     * @var PluginInit
     */
    private static $instance;

    /**
     * Gets the singleton instance of the class
     *
     * @return PluginInit
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor for PluginInit class
     * Initializes the plugin by setting up the necessary actions and filters.
     */
    public function __construct() {
        $this->spnwp_init();
    }

    /**
     * Initialize the plugin's core functionalities
     *
     * Registers scripts, REST routes, and actions for front-end and admin.
     */
    public function spnwp_init() {
        add_action( 'admin_enqueue_scripts', array( $this, 'spnwp_custom_admin_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'spnwp_custom_frontend_scripts' ) );
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
        add_action( 'wp_footer', array( $this, 'add_notification_hook_before_footer' ) );

        // Initialize notifications
        new Notifications();
    }

    /**
     * Enqueue admin-specific scripts for notifications
     */
    public function spnwp_custom_admin_scripts() {
        global $typenow;

        if ( 'wp_push_notification' === $typenow ) {
            wp_enqueue_script( 'spnwp-admin', PUSH_NOTIFICATION_PLUGIN_URL . '/assets/js/spnwp_admin.js', array( 'jquery' ), null, true );
        }
    }

    /**
     * Enqueue front-end scripts and styles for notifications
     */
    public function spnwp_custom_frontend_scripts() {
        if ( ! is_admin() ) {
            // Enqueue scripts and styles for the front-end
            wp_enqueue_script( 'spnwp-client', PUSH_NOTIFICATION_PLUGIN_URL . '/assets/js/spnwp_client.js', array( 'jquery' ), null, true );

            // Localize the API URL and logged-in status for use in JS
            $api_url = home_url( '/wp-json/spnwp-push-notification/v1/' );
            $user_id = get_current_user_id();
            wp_localize_script( 'spnwp-client', 'spnwp_vars', array(
                'apiUrl'      => $api_url,
                'is_logged_in' => (bool) $user_id,
            ));

            // Enqueue optional styles for the notifications
            wp_enqueue_style( 'spnwp-custom-style', PUSH_NOTIFICATION_PLUGIN_URL . '/assets/css/spnwp_client.css' );
        }
    }

    /**
     * Registers the REST API routes for the plugin
     */
    public function register_rest_routes() {
        $controller = new NotificationController();
        $controller->register_routes();
    }

    /**
     * Adds notification HTML structure before the footer on the front-end
     */
    public function add_notification_hook_before_footer() {
        $user_id = get_current_user_id();

        if ( $user_id ) :
            ?>
            <div class="spnwp-notification-counter">
            <span id="spnwp-notifications-btn" class="spnwp-notifications-btn">
                <svg class="spnwp-notification-icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 50 50" width="50px" height="50px">
                    <path style="fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;" d="M22.58,7.201C16.761,8.198,13,12.5,13,19c0,15.125-6,14.564-6,17c0,4,9,5,18,5s18-1,18-5c0-2.436-6-1.875-6-17c0-6.5-3.822-10.877-9.723-11.823"/>
                    <circle style="fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;" cx="25" cy="5" r="3"/>
                    <path style="fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;" d="M29.585,41.003C29.852,41.615,30,42.29,30,43c0,2.762-2.238,5-5,5s-5-2.238-5-5c0-0.663,0.129-1.296,0.364-1.875"/>
                </svg>
            </span>
                <span class="spnwp-notification-counter-holder">0</span>
            </div>

            <div class="spnwp-notification-overlay overlay close"></div>
            <div class="spnwp-notification-drawer">
                <div class="spnwp-notification-holder">
                    <div class="spnwp-notification-header"></div>
                    <div class="spnwp-notification-wrapper"></div>
                    <div class="notification-footer"></div>
                </div>
            </div>
        <?php
        endif;
    }
}
