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

            new Notifications();

        }
    }
