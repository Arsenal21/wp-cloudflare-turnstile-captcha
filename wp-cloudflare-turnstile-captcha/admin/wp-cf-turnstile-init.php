<?php
/* 
 * Inits the admin dashboard side of things.
 * Main admin file which loads all settings panels and sets up admin menus. 
 */
class WP_CFT_Admin_Init
{
    var $main_menu_page;
    var $dashboard_menu;
    var $settings_menu;

    function __construct()
    {
        $this->admin_includes();
        add_action('admin_print_scripts', array(&$this, 'admin_menu_page_scripts'));
        add_action('admin_print_styles', array(&$this, 'admin_menu_page_styles'));
        add_action('admin_menu', array(&$this, 'create_admin_menus'));        
    }
    
    function admin_includes()
    {
        include_once('wp-cf-turnstile-admin-menu.php');
    }

    function admin_menu_page_scripts() 
    {
        //make sure we are on the appropriate menu page
        if (isset($_GET['page']) && strpos($_GET['page'], WP_CFT_MENU_SLUG_PREFIX ) !== false ) {
            wp_enqueue_script('postbox');
            wp_enqueue_script('dashboard');
            wp_enqueue_script('thickbox');
            //wp_enqueue_script('media-upload');
        }
    }
    
    function admin_menu_page_styles() 
    {
        //make sure we are on the appropriate menu page
        if (isset($_GET['page']) && strpos($_GET['page'], WP_CFT_MENU_SLUG_PREFIX ) !== false ) {
            wp_enqueue_style('dashboard');
            wp_enqueue_style('thickbox');
            wp_enqueue_style('global');
            wp_enqueue_style('wp-admin');
            wp_enqueue_style('aiowpsec-admin-css', WP_CFT_URL. '/css/wp-cf-turnstile-admin-styles.css');
        }
    } 
    
    function create_admin_menus()
    {
        $menu_icon_url = WP_CFT_URL.'/images/plugin-icon.png';
        $this->main_menu_page = add_menu_page(__('Cloudflare Turnstile', WP_CFT_TEXT_DOMAIN), __('Cloudflare Turnstile', WP_CFT_TEXT_DOMAIN), WP_CFT_MANAGEMENT_PERMISSION, WP_CFT_MAIN_MENU_SLUG , array(&$this, 'handle_dashboard_menu_rendering'), $menu_icon_url);
        add_submenu_page(WP_CFT_MAIN_MENU_SLUG, __('Dashboard', WP_CFT_TEXT_DOMAIN),  __('Dashboard', WP_CFT_TEXT_DOMAIN) , WP_CFT_MANAGEMENT_PERMISSION, WP_CFT_MAIN_MENU_SLUG, array(&$this, 'handle_dashboard_menu_rendering'));
        add_submenu_page(WP_CFT_MAIN_MENU_SLUG, __('Settings', WP_CFT_TEXT_DOMAIN),  __('Settings', WP_CFT_TEXT_DOMAIN) , WP_CFT_MANAGEMENT_PERMISSION, WP_CFT_SETTINGS_MENU_SLUG, array(&$this, 'handle_settings_menu_rendering'));
        
        do_action('wp_cft_admin_menu_created');
    }
    
    function handle_dashboard_menu_rendering()
    {
        include_once('wp-cf-turnstile-dashboard-menu.php');
        $this->dashboard_menu = new WP_CFT_Dashboard_Menu();
    }

    function handle_settings_menu_rendering()
    {
        include_once('wp-cf-turnstile-settings-menu.php');
        $this->settings_menu = new WP_CFT_Settings_Menu();
        
    }
}//End of class
