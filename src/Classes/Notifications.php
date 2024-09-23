<?php
namespace Rus\Notification;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Notifications
 *
 * Handles the registration and management of custom post types and custom fields for push notifications.
 */
class Notifications {

    /**
     * Notifications constructor.
     *
     * Initializes the hooks for the plugin.
     */
    public function __construct() {
        $this->spnwp_hooks();
    }

    /**
     * Registers WordPress hooks for notifications.
     *
     * @return void
     */
    public function spnwp_hooks() {
        add_action( 'init', [ $this, 'spnwp_register' ], 0 );
        add_action( 'admin_head', [ $this, 'spnwp_remove_add_media' ] );
        add_action( 'add_meta_boxes', [ $this, 'spnwp_additional_custom_fields' ] );
        add_action( 'save_post', [ $this, 'spnwp_save' ], 100, 3 );
    }

    /**
     * Registers the custom post type for Push Notifications.
     *
     * @return void
     */
    public function spnwp_register() {
        register_post_type(
            'wp_push_notification',
            [
                'labels'        => [
                    'name'               => __( 'Push Notifications', 'spnwp' ),
                    'singular_name'      => __( 'Notification', 'spnwp' ),
                    'add_new'            => __( 'Add New', 'spnwp' ),
                    'add_new_item'       => __( 'Add New Notification', 'spnwp' ),
                    'edit_item'          => __( 'Edit Notification', 'spnwp' ),
                    'new_item'           => __( 'New Notification', 'spnwp' ),
                    'view_item'          => __( 'View Notification', 'spnwp' ),
                    'search_items'       => __( 'Search Notifications', 'spnwp' ),
                    'not_found'          => __( 'No Notifications found', 'spnwp' ),
                    'not_found_in_trash' => __( 'No Notifications found in trash', 'spnwp' ),
                    'menu_name'          => __( 'Push Notifications', 'spnwp' ),
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

    /**
     * Removes the 'Add Media' button from the editor on the Push Notifications post type.
     *
     * @return void
     */
    public function spnwp_remove_add_media() {
        $current_screen = get_current_screen();

        if ( 'wp_push_notification' === $current_screen->post_type ) {
            remove_action( 'media_buttons', 'media_buttons' );
        }
    }

    /**
     * Adds additional custom fields to the Push Notifications post type.
     *
     * @return void
     */
    public function spnwp_additional_custom_fields() {
        add_meta_box(
            'spnwp_additional_custom_fields',
            __( 'Additional Fields', 'spnwp' ),
            [ $this, 'spnwp_display_additional_custom_fields' ],
            'wp_push_notification',
            'normal',
            'default'
        );
    }

    /**
     * Displays the custom fields for Push Notifications.
     *
     * @param WP_Post $post The current post object.
     *
     * @return void
     */
    public function spnwp_display_additional_custom_fields( $post ) {
        $meta_data = get_post_meta( $post->ID, 'spnwp_notification_meta', true );
        $additional_fields = maybe_unserialize( $meta_data );

        // Include template file for custom fields
        require_once PUSH_NOTIFICATION_PLUGIN_DIR . '/src/Templates/additional-custom-fields.php';
    }

    /**
     * Saves the custom fields for Push Notifications when the post is saved.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     * @param bool    $update  Whether this is an update.
     *
     * @return void
     */
    public function spnwp_save( $post_id, $post, $update ) {

        // Bail if doing autosave, ajax, or not a valid post type.
        if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
            ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ||
            ( 'publish' !== $post->post_status && 'trash' !== $post->post_status && 'draft' !== $post->post_status ) ||
            'wp_push_notification' !== $post->post_type ) {
            return;
        }

        // Verify nonce for security.
        if ( ! isset( $_POST['additional_custom_fields_nonce'] ) || ! wp_verify_nonce( $_POST['additional_custom_fields_nonce'], 'spnwp_save_additional_custom_fields' ) ) {
            wp_die( __( 'Nonce verification failed.', 'spnwp' ) );
        }

        // Sanitize input fields.
        $cta_one_label      = sanitize_text_field( $_POST['cta_one_label'] );
        $cta_one_value      = sanitize_textarea_field( $_POST['cta_one_value'] );
        $cta_two_label      = sanitize_text_field( $_POST['cta_two_label'] );
        $cta_two_value      = sanitize_textarea_field( $_POST['cta_two_value'] );
        $notification_start = sanitize_text_field( $_POST['notification_start'] );
        $notification_end   = sanitize_text_field( $_POST['notification_end'] );

        // Set the current time if start date is empty.
        if ( empty( $notification_start ) ) {
            $notification_start = current_time( 'mysql' );
        }

        // Prepare and save data.
        $data = maybe_serialize( [
            'cta_one_label'      => $cta_one_label,
            'cta_one_value'      => $cta_one_value,
            'cta_two_label'      => $cta_two_label,
            'cta_two_value'      => $cta_two_value,
            'notification_start' => $notification_start,
            'notification_end'   => $notification_end,
        ] );

        update_post_meta( $post_id, 'spnwp_notification_meta', $data );
    }
}
?>
