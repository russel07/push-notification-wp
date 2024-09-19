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
        const REST_ROUTE = 'notifications'; 

        public function register_routes() {
            register_rest_route( 
                static::REST_NAMESPACE, 
                '/' . static::REST_ROUTE, 
                array(
                    array(
                        'methods'  => WP_REST_Server::READABLE,
                        'callback' => [ $this, 'spnwp_get_notifications' ],
                        'permission_callback' =>  '__return_true',
                    ),
                ) 
            );
        }
   
        public function spnwp_get_notifications( WP_REST_Request $request ) {
            $notifications = [];
        
            // Query posts of type 'wp_push_notification'
            $data = get_posts(
                [
                    'posts_per_page' => -1,
                    'post_type'      => 'wp_push_notification',
                    'no_found_rows'  => true,
                    'order'          => 'DESC',
                    'post_status'    => 'publish',
                ]
            );
        
            // Check if posts are found
            if ( ! empty( $data ) && ! is_wp_error( $data ) ) {
                foreach ( $data as $item ) {
                    $notification = [
                        'id'      => absint( $item->ID ),
                        'title'   => $item->post_title,
                        'content' => $item->post_content,
                    ];
        
                    // Get custom fields for each notification
                    $custom_fields = $this->prepare_custom_fields( $item->ID );
        
                    // Merge notification data with custom fields
                    $notifications[] = array_merge( $notification, $custom_fields );
                }
            }

            $response['active_notifications'] = $notifications;
            $response['dismissed_notifications'][0] = $notifications[0];
        
            return new WP_REST_Response( $response, 200 ); // Return notifications with 200 status
        }

        private function prepare_custom_fields( $postID )
        {
            $meta_data = get_post_meta( $postID, 'spnwp_notification_meta', true );
            $custom_fields = maybe_unserialize( $meta_data );
            $data = [
                'main' => [
                    'url'  => isset( $custom_fields['cta_one_value'] ) ? $custom_fields['cta_one_value'] : '',
                    'text' => isset( $custom_fields['cta_one_label'] ) ? $custom_fields['cta_one_label'] : '',
                ],
                'alt' => [
                    'url'  => isset( $custom_fields['cta_two_value'] ) ? $custom_fields['cta_two_value'] : '',
                    'text' => isset( $custom_fields['cta_two_label'] ) ? $custom_fields['cta_two_label'] : '',
                ],
                'start_date' => isset( $custom_fields['notification_start'] ) ? $custom_fields['notification_start'] : '',
                'end_date' => isset( $custom_fields['notification_end'] ) ? $custom_fields['notification_end'] : '',
            ];


            if ( empty( $data['main']['url'] ) && empty( $data['main']['text'] ) ) {
                unset( $data['main'] );
            }

            if ( empty( $data['alt']['url'] ) && empty( $data['alt']['text'] ) ) {
                unset( $data['alt'] );
            }

            if ( empty( $data['start_date'] ) ) {
                unset( $data['start_date'] );
            }

            if ( empty( $data['end_date'] ) ) {
                unset( $data['end_date'] );
            }

            return $data;
        }
    }