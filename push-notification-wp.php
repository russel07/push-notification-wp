<?php
/**
 * Plugin Name:       Push Notification WP
 * Plugin URI:        https://wordpress.org/plugins/push-notification-wp
 * Description:       Manage push notification smartly
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Smarty Soft
 * Author URI:        https://smarty-soft.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/shovoalways
 * Text Domain:       spnwp
 */

 define( 'PUSH_NOTIFICATION_VERSION', '1.0.0' );
 define( 'PUSH_NOTIFICATION_PLUGIN_PATH', __FILE__ );
 define( 'PUSH_NOTIFICATION_PLUGIN_DIR', __DIR__ );
 define( 'PUSH_NOTIFICATION_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

 require 'autoloader.php';

 function spnwp_init() {

    return \Rus\Notification\PluginInit::get_instance();

 }

 spnwp_init();

add_action( 'plugins_loaded', 'my_plugin_load_textdomain' );

/**
 * Load the plugin textdomain for translation.
 */
function my_plugin_load_textdomain() {
 load_plugin_textdomain( 'spnwp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}


?>
