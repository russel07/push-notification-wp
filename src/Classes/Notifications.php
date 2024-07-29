<?php
    namespace Rus\Notification;

    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    class Notifications {

        public function __construct() {

            $this->spnwp_hooks();

        }

        public function spnwp_hooks() {

            add_action( 'init', [ $this, 'spnwp_register' ], 0 );
            add_action( 'admin_head', [ $this, 'spnwp_remove_add_media' ] );

            add_action( 'add_meta_boxes', [ $this,'spnwp_additional_custom_fields' ] );

        }

        public function spnwp_register() {

            register_post_type(
                'wp_push_notification',
                [
                    'labels'        => [
                        'name'               => 'Push Notifications',
                        'singular_name'      => 'Notification',
                        'add_new'            => 'Add New',
                        'add_new_item'       => 'Add New Notification',
                        'edit_item'          => 'Edit Notification',
                        'new_item'           => 'New Notification',
                        'view_item'          => 'View Notification',
                        'search_items'       => 'Search Notifications',
                        'not_found'          => 'No Notifications found',
                        'not_found_in_trash' => 'No Notifications found in trash',
                        'parent_item_colon'  => '',
                        'menu_name'          => 'Push Notifications',
                    ],
                    'public'       => false,
                    'show_ui'      => true,
                    'show_in_menu' => true,
                    'query_var'    => false,
                    'rewrite'      => false,
                    'hierarchical' => true,
                    'supports'     => [ 'title', 'editor', 'revisions' ],
                    'menu_icon'    => 'dashicons-megaphone',
                ]
            );

        }

        public function spnwp_remove_add_media() {

            $current_screen = get_current_screen();

            if ( $current_screen->post_type == 'wp_push_notification' ) {
                remove_action( 'media_buttons', 'media_buttons' );
            }

        }

        public function spnwp_additional_custom_fields() {

            add_meta_box(
                'spnwp_additional_custom_fields',
                'Additional Fields',
                [ $this, 'spnwp_display_additional_custom_fields' ],
                'wp_push_notification',
                'normal',
                'default'
            );

        }

        public function spnwp_display_additional_custom_fields() {
            
            require_once PUSH_NOTIFICATION_PLUGIN_DIR . '/src/Templates/additional-custom-fields.php';

        }
    }

?>