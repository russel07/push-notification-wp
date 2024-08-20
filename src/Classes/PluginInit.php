<?php
    namespace Rus\Notification;

    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    final class PluginInit {
        
        private static $instance;

        public static function get_instance() {

            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
                self::$instance = new PluginInit();
            }

            return self::$instance;

        }

        public function __construct() {

            $this->spnwp_init();

        }

        public function spnwp_init() {
            add_action('admin_enqueue_scripts', [ $this, 'spnwp_custom_admin_scripts']);
            add_action('rest_api_init', [$this, 'register_rest_routes']);

            new Notifications();

        }

        public function spnwp_custom_admin_scripts() {
            global $typenow;
            if ($typenow === 'wp_push_notification') { 
                wp_enqueue_script('spnwp-admin', PUSH_NOTIFICATION_PLUGIN_URL . '/assets/js/spnwp_admin.js', array('jquery'), null, true);
            }
        }

        public function register_rest_routes() {
            (new NotificationController)->register_routes();
        }
    }
