<?php

if (!class_exists('WP_CFT_Main')){

class WP_CFT_Main{
    var $version = '1.0';
    var $db_version = '1.0';
    var $plugin_url;
    var $plugin_path;
    var $plugin_configs;//TODO - Does it need to be static?
    var $admin_init;
    var $debug_logger;

    function __construct() {
        $this->load_configs();
        $this->define_constants();
        $this->includes();
        $this->loader_operations();

        add_action('init', array( &$this, 'wp_cft_plugin_init' ), 0 );
        do_action('wp_cft_loaded');
    }
    
    function plugin_url() { 
        if ( $this->plugin_url ) return $this->plugin_url;
        return $this->plugin_url = plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
    }

    function plugin_path() { 	
        if ( $this->plugin_path ) return $this->plugin_path;		
        return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
    }
    
    function load_configs(){
        include_once('classes/wp-cf-turnstile-config.php');
        $this->plugin_configs = WP_CFT_Config::get_instance();
    }
    
    function define_constants(){
        define('WP_CFT_VERSION', $this->version);
        define('WP_CFT_URL', $this->plugin_url());
        define('WP_CFT_PATH', $this->plugin_path());
        define('WP_CFT_DB_VERSION', $this->db_version);
        define('WP_CFT_TEXT_DOMAIN', 'wp-cf-turnstile');
        define('WP_CFT_MANAGEMENT_PERMISSION', 'manage_options');
        define('WP_CFT_MENU_SLUG_PREFIX', 'wp-cft');
        define('WP_CFT_MAIN_MENU_SLUG', 'wp-cft');
        define('WP_CFT_SETTINGS_MENU_SLUG', 'wp-cft-settings');
        //global $wpdb;
        //define('DB_NAME_TABLE_TBL', $wpdb->prefix . "define_name_here_tbl");

    }

    function includes() {
        //Load common files for everywhere
        include_once('classes/wp-cf-turnstile-debug-logger.php');
        if (is_admin()){ //Load admin side only files
            include_once('admin/wp-cf-turnstile-admin-init.php');
        }
        else{ //Load front end side only files
        }
    }

    function loader_operations(){
        add_action('plugins_loaded',array(&$this, 'plugins_loaded_handler'));//plugins loaded hook
        $this->debug_logger = new WP_CFT_Debug_Logger();
        if( is_admin() ){
            //Do admin side operations
            $this->admin_init = new WP_CFT_Admin_Init();
        }
    }
    
    public static function activate_handler(){//Only runs when the plugin activates - do installer tasks
        //include_once ('file-name-installer.php');
        //wp_cft_run_activation();
    }
    
    function do_db_upgrade_check(){
        if(is_admin()){//Check if DB needs to be updated
            if (get_option('wp_cft_db_version') != WP_CFT_DB_VERSION) {
                //include_once ('file-name-installer.php');
                //wp_cft_run_db_upgrade();
            }
        }
    }
    
    function plugins_loaded_handler(){//Runs when plugins_loaded action gets fired
        if(is_admin()){//Do admin side plugins_loaded operations
            $this->do_db_upgrade_check();
            //$this->settings_obj = new WP_CFT_Settings_Page();//Initialize settins menus
        }
    }
    
    function wp_cft_plugin_init(){//Lets run... Main plugin operation code goes here
        // Set up localisation
	// $this->load_plugin_textdomain();
        // 
        //Plugin into code goes here... actions, filters, shortcodes goes here
        //add_action(....);
        //$this->debug_logger->log_debug("WP Cloudflare Turnstile pluign init");
    }

}//End of class

}//End of class not exists check

$GLOBALS['wp_cft_main'] = new WP_CFT_Main();
