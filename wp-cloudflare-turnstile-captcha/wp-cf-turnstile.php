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
    exit; //Exit if accessed directly
}

include_once('wp-cf-turnstile.php');

register_activation_hook(__FILE__,array('WP_CFT_Main','activate_handler'));//activation hook
//register_deactivation_hook(__FILE__,array('WP_CFT_Main','deactivate_handler'));//deactivation hook
