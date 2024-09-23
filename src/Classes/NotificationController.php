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

            register_rest_route(
                static::REST_NAMESPACE,
                '/' . static::REST_ROUTE,
                array(
                    array(
                        'methods'  => WP_REST_Server::CREATABLE,
                        'callback' => [ $this, 'spnwp_dismiss_notification' ],
                        'permission_callback' => '__return_true',
                        'args' => array(
                            'id' => array(
                                'required' => true,
                                'validate_callback' => function($param, $request, $key) {
                                    return is_numeric($param);
                                },
                                'sanitize_callback' => 'absint',
                                'description' => 'The ID of the notification to be dismissed.',
                            ),
                        ),
                    ),
                )
            );

        }
   
        public function spnwp_get_notifications() {
            $user_id                    = get_current_user_id();
            $dismissed_notifications    = get_option( 'spnwp_dismissed_notification_'.$user_id, [] );
            $notifications    = [
                'active_notifications' => [],
                'dismissed_notifications' => []
            ];

            if ( !is_array( $dismissed_notifications ) ) {
                $dismissed_notifications = [];
            }
        
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
                    $notification = array_merge( $notification, $custom_fields );

                    if ( in_array( $item->ID, $dismissed_notifications ) ) {
                        $notifications['dismissed_notifications'][] = $notification;
                    } else if( $this->filter_active_notification( $notification ) ) {
                        $notifications['active_notifications'][] = $notification;
                    }
                }
            }
        
            return new WP_REST_Response( $notifications, 200 ); // Return notifications with 200 status
        }

        public function spnwp_dismiss_notification( WP_REST_Request $request ) {
            // Get the post ID from the request
            $post_id = $request->get_param('id');

            // Get the current logged-in user ID
            $user_id = get_current_user_id();

            // Get all dismissed notifications stored in the options table
            // It will store data for all users in a single option
            $dismissed_notifications = get_option( 'spnwp_dismissed_notification_'.$user_id, [] );

            // If the option doesn't exist or it's not an array, initialize it
            if ( !is_array( $dismissed_notifications ) ) {
                $dismissed_notifications = [];
            }

            // Check if the post ID already exists in the user's dismissed array
            if ( !in_array( $post_id, $dismissed_notifications ) ) {
                // If the post ID doesn't exist, add it to the user's array
                $dismissed_notifications[] = $post_id;

                // Update the dismissed notifications in the options table
                update_option( 'spnwp_dismissed_notification_'.$user_id, $dismissed_notifications );
            }

            // Return true to indicate success
            return $this->spnwp_get_notifications();
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

        private function filter_active_notification( $notification )
        {
            $start_date = strtotime( $notification['start_date'] );
            $end_date   = strtotime( $notification['end_date'] );
            $now        = strtotime(current_time( 'mysql' ));

            if( $start_date <= $now && ( empty( $end_date ) || ($now <= $end_date)) ) {
                return true;
            }

            return false;
        }
    }