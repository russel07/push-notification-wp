<?php
    namespace Rus\Notification;

    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    class Notifications {
        public function __construct() {
            $this->hooks();
        }

        public function hooks() {
            add_action( 'init', [ $this, 'register' ], 0 );
        }

        public function register() {
            register_post_type(
                'push_notification',
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
                        'menu_name'          => 'App Notifications',
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
    }

?>