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
            add_action( 'save_post', [ $this, 'spnwp_save' ], 100, 3 );
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

        public function spnwp_display_additional_custom_fields( $post ) {
            $additional_fields = get_post_meta( $post->ID, 'spnwp_notification_meta', true );

            require_once PUSH_NOTIFICATION_PLUGIN_DIR . '/src/Templates/additional-custom-fields.php';

        }

        /**
         * Notification has been saved.
         *
         * @since 1.0.0
         *
         * @param int     $post_id Post ID.
         * @param WP_Post $post    Post object.
         * @param bool    $update  If its an update.
         */
        public function spnwp_save( $post_id, $post, $update = 1 ) {

            if (
                ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
                ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ||
                ( $post->post_status !== 'publish' && $post->post_status !== 'trash' && $post->post_status !== 'draft' ) ||
                $post->post_type !== 'wp_push_notification'
            ) {
                return;
            }

            if ( ! isset( $_POST['additional_custom_fields_nonce'] ) || ! wp_verify_nonce( $_POST['additional_custom_fields_nonce'], 'spnwp_save_additional_custom_fields' ) ) {
                wp_die( 'Nonce verification failed.' );
            }

            $cta_one_label          = sanitize_text_field($_POST['cta_one_label']);
            $cta_one_value          = sanitize_textarea_field($_POST['cta_one_value']);
            $cta_two_label          = sanitize_text_field($_POST['cta_two_label']);
            $cta_two_value          = sanitize_textarea_field($_POST['cta_two_value']);
            $notification_start     = sanitize_text_field($_POST['notification_start']);
            $notification_end       = sanitize_textarea_field($_POST['notification_end']);

            $data = [
                'cta_one_label'         => $cta_one_label,
                'cta_one_value'         => $cta_one_value,
                'cta_two_label'         => $cta_two_label,
                'cta_two_value'         => $cta_two_value,
                'notification_start'    => $notification_start,
                'notification_end'      => $notification_end
            ];

            update_post_meta( $post_id, 'spnwp_notification_meta', $data );
        }
    }

?>