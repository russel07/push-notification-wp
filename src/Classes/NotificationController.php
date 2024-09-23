<?php
namespace Rus\Notification;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class NotificationController {
    const REST_NAMESPACE = 'spnwp-push-notification/v1';
    const REST_ROUTE     = 'notifications';

    /**
     * Registers the REST API routes.
     */
    public function register_routes() {
        register_rest_route(
            self::REST_NAMESPACE,
            '/' . self::REST_ROUTE,
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'spnwp_get_notifications' ),
                    'permission_callback' => '__return_true',
                ),
            )
        );

        register_rest_route(
            self::REST_NAMESPACE,
            '/' . self::REST_ROUTE,
            array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'spnwp_dismiss_notification' ),
                    'permission_callback' => '__return_true',
                    'args'                => array(
                        'id' => array(
                            'required'          => true,
                            'validate_callback' => function( $param, $request, $key ) {
                                return is_numeric( $param );
                            },
                            'sanitize_callback' => 'absint',
                            'description'       => __( 'The ID of the notification to be dismissed.', 'spnwp' ),
                        ),
                    ),
                ),
            )
        );
    }

    /**
     * Retrieves notifications for the current user.
     *
     * @return WP_REST_Response
     */
    public function spnwp_get_notifications() {
        $user_id                = get_current_user_id();
        $dismissed_notifications = get_option( 'spnwp_dismissed_notification_' . $user_id, array() );
        $notifications           = array(
            'active_notifications'    => array(),
            'dismissed_notifications' => array(),
        );

        if ( ! is_array( $dismissed_notifications ) ) {
            $dismissed_notifications = array();
        }

        $data = get_posts(
            array(
                'posts_per_page' => -1,
                'post_type'      => 'wp_push_notification',
                'no_found_rows'  => true,
                'order'          => 'DESC',
                'post_status'    => 'publish',
            )
        );

        if ( ! empty( $data ) && ! is_wp_error( $data ) ) {
            foreach ( $data as $item ) {
                $notification = array(
                    'id'      => absint( $item->ID ),
                    'title'   => $item->post_title,
                    'content' => $item->post_content,
                );

                $custom_fields = $this->prepare_custom_fields( $item->ID );
                $notification  = array_merge( $notification, $custom_fields );

                if ( in_array( $item->ID, $dismissed_notifications ) ) {
                    $notifications['dismissed_notifications'][] = $notification;
                } elseif ( $this->filter_active_notification( $notification ) ) {
                    $notifications['active_notifications'][] = $notification;
                }
            }
        }

        return new WP_REST_Response( $notifications, 200 );
    }

    /**
     * Dismisses a notification for the current user.
     *
     * @param WP_REST_Request $request The REST request.
     * @return WP_REST_Response
     */
    public function spnwp_dismiss_notification( WP_REST_Request $request ) {
        $post_id = $request->get_param( 'id' );
        $user_id = get_current_user_id();

        $dismissed_notifications = get_option( 'spnwp_dismissed_notification_' . $user_id, array() );

        if ( ! is_array( $dismissed_notifications ) ) {
            $dismissed_notifications = array();
        }

        if ( ! in_array( $post_id, $dismissed_notifications ) ) {
            $dismissed_notifications[] = $post_id;
            update_option( 'spnwp_dismissed_notification_' . $user_id, $dismissed_notifications );
        }

        return $this->spnwp_get_notifications();
    }

    /**
     * Prepares custom fields for a notification.
     *
     * @param int $post_id The post ID.
     * @return array
     */
    private function prepare_custom_fields( $post_id ) {
        $meta_data    = get_post_meta( $post_id, 'spnwp_notification_meta', true );
        $custom_fields = maybe_unserialize( $meta_data );

        $data = array(
            'main' => array(
                'url'  => isset( $custom_fields['cta_one_value'] ) ? $custom_fields['cta_one_value'] : '',
                'text' => isset( $custom_fields['cta_one_label'] ) ? $custom_fields['cta_one_label'] : '',
            ),
            'alt' => array(
                'url'  => isset( $custom_fields['cta_two_value'] ) ? $custom_fields['cta_two_value'] : '',
                'text' => isset( $custom_fields['cta_two_label'] ) ? $custom_fields['cta_two_label'] : '',
            ),
            'start_date' => isset( $custom_fields['notification_start'] ) ? $custom_fields['notification_start'] : '',
            'end_date'   => isset( $custom_fields['notification_end'] ) ? $custom_fields['notification_end'] : '',
        );

        if ( empty( $data['main']['url'] ) && empty( $data['main']['text'] ) ) {
            unset( $data['main'] );
        }

        if ( empty( $data['alt']['url'] ) && empty( $data['alt']['text'] ) ) {
            unset( $data['alt'] );
        }

        return $data;
    }

    /**
     * Filters notifications to check if they are active.
     *
     * @param array $notification The notification data.
     * @return bool
     */
    private function filter_active_notification( $notification ) {
        $start_date = strtotime( $notification['start_date'] );
        $end_date   = strtotime( $notification['end_date'] );
        $now        = strtotime( current_time( 'mysql' ) );

        return ( $start_date <= $now ) && ( empty( $end_date ) || ( $now <= $end_date ) );
    }
}
