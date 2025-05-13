<?php
/*
Plugin Name: CF Turnstile for WP
Version: v1.0.0
Plugin URI: https://www.tipsandtricks-hq.com/
Author: Tips and Tricks HQ
Author URI: https://www.tipsandtricks-hq.com/
Description: This plugin adds Cloudflare Turnstile to your WordPress site, providing an easy way to implement this CAPTCHA alternative for form validation and spam protection.
Text Domain: wp-cf-turnstile
License: GPL2
*/

//Prefix - wp_cft_

if(!defined('ABSPATH')){
    //Exit if accessed directly
    exit; 
}

//Defining the version constants here allows easy updating of them all from one file when releasing new versions.
define('WP_CFT_VERSION', '1.0.0'); //Plugin version
define('WP_CFT_DB_VERSION', '1.0'); //DB version

//Include the main plugin class
include_once('wp-cf-turnstile-core.php');

//Activation and deactivation hooks
register_activation_hook(__FILE__,array('WP_CFT_Main','activate_handler'));//activation hook
register_deactivation_hook(__FILE__,array('WP_CFT_Main','deactivate_handler'));//deactivation hook

//Add settings link in plugins listing page
function wp_cft_add_settings_link( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$settings_link = '<a href="admin.php?page=wp-cft">Settings</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}
add_filter( 'plugin_action_links', 'wp_cft_add_settings_link', 10, 2 );
