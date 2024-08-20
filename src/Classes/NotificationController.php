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
            return new WP_REST_Response( $notifications );
        }
    }