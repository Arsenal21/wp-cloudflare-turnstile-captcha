<?php
/*
Plugin Name: WP Cloudflare Turnstile Captcha
Version: v1.0.0
Plugin URI: https://www.tipsandtricks-hq.com/
Author: Tips and Tricks HQ
Author URI: https://www.tipsandtricks-hq.com/
Description: Cloudflare turnstile captcha for WordPress
Text Domain: wp-cf-turnstile
License: GPL2
*/

//Prefix - wp_cft_

if(!defined('ABSPATH')){
    //Exit if accessed directly
    exit; 
}

//Include the main plugin class
include_once('wp-cf-turnstile-core.php');

//Activation and deactivation hooks
register_activation_hook(__FILE__,array('WP_CFT_Main','activate_handler'));//activation hook
//register_deactivation_hook(__FILE__,array('WP_CFT_Main','deactivate_handler'));//deactivation hook

//Add settings link in plugins listing page
function wp_cft_add_settings_link( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$settings_link = '<a href="admin.php?page=wp-cft-settings">Settings</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}
add_filter( 'plugin_action_links', 'wp_cft_add_settings_link', 10, 2 );
