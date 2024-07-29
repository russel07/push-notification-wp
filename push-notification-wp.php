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
 * Text Domain:       smart-push-notification-wp
 */

 const SMART_NOTIFICATION_PUBLIC_PLUGIN_PATH = __FILE__;

 require 'autoloader.php';

 function spnwp_init() {
    return \Rus\Notification\PluginInit::get_instance();
 }

 spnwp_init();

 ?>
