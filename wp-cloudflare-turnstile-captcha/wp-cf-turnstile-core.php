<?php

if ( ! class_exists( 'WP_CFT_Main' ) ) {

class WP_CFT_Main {
	public $plugin_url;
	public $plugin_path;
	public $plugin_configs;//TODO - Does it need to be static?
	public $admin_init;
	public $debug_logger;

	public function __construct() {
		$this->load_configs();
		$this->define_constants();
		$this->includes();
		$this->initialize_and_run_classes();

		// Register action hooks.
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded_handler' ) );
		// Note: Create a separate class to handle the other init time tasks.
		add_action( 'init', array( $this, 'load_language' ) );

		// Trigger the action hook for when the constructor has finished loading.
		do_action( 'wp_cft_loaded' );

		add_action( 'login_enqueue_scripts', array( $this, 'register_login_scripts' ) );
	}

	public function define_constants() {
		define( 'WP_CFT_URL', $this->plugin_url() );
		define( 'WP_CFT_PATH', $this->plugin_path() );
		define( 'WP_CFT_TEXT_DOMAIN', 'wp-cf-turnstile' );
		define( 'WP_CFT_MANAGEMENT_PERMISSION', 'manage_options' );
		define( 'WP_CFT_MENU_SLUG_PREFIX', 'wp-cft' );
		define( 'WP_CFT_MAIN_MENU_SLUG', 'wp-cft' );
		define( 'WP_CFT_SETTINGS_MENU_SLUG', 'wp-cft-settings' );
		define( 'WP_CFT_WORDPRESS_FORMS_MENU_SLUG', 'wp-cft-wp-forms' );
		define( 'WP_CFT_INTEGRATIONS_MENU_SLUG', 'wp-cft-integrations' );
		//global $wpdb;
		//define('DB_TABLE_TBL', $wpdb->prefix . "define_name_for_tbl");
	}

	public function plugin_url() {
		if ( $this->plugin_url ) {
			return $this->plugin_url;
		}

		return $this->plugin_url = plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) );
	}

	public function plugin_path() {
		if ( $this->plugin_path ) {
			return $this->plugin_path;
		}

		return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	public function load_configs() {
		include_once( 'classes/wp-cf-turnstile-config.php' );
		$this->plugin_configs = WP_CFT_Config::get_instance();
	}

	public function includes() {
		//Load common files for everywhere
		include_once( WP_CFT_PATH . '/classes/wp-cf-turnstile-utils.php' );
		include_once( WP_CFT_PATH . '/classes/class-wp-cft-turnstile.php' );
		include_once( WP_CFT_PATH . '/classes/integrations/class-wp-cft-integration-wp.php' );
		include_once( WP_CFT_PATH . '/classes/wp-cf-turnstile-debug-logger.php' );
		if ( is_admin() ) {
			//Load admin side only files
			include_once( 'admin/wp-cf-turnstile-admin-init.php' );
		} else {
			//Load front end side only files
		}
	}

	public function register_login_scripts(){
		WP_CFT_Turnstile::register_scripts();
	}

	public function initialize_and_run_classes() {
		//Initialize and run classes here
		$this->debug_logger = new WP_CFT_Debug_Logger();
		if ( is_admin() ) {
			//Do admin side operations
			$this->admin_init = new WP_CFT_Admin_Init();
		}

		new WP_CFT_WordPress_Integration();
	}

	public function load_language() {
		// Internationalization.
		// A better practice for text domain is to use dashes instead of underscores.
		//load_plugin_textdomain('language-text-domain', false, WP_CFT_PATH . '/languages/');
	}

	public static function activate_handler() {
		// Only runs when the plugin activates - do installer tasks
		//include_once ('file-name-installer.php');
		//wp_cft_run_activation();
	}

	public static function deactivate_handler() {}

	public function plugins_loaded_handler() {
		// Runs when plugins_loaded action gets fired.
		// Do any admin side plugins_loaded operations
		// if(is_admin()){
		//     $this->do_db_upgrade_check();
		// }
	}

	public function do_db_upgrade_check() {
		if ( is_admin() ) {
			//Check if DB needs to be updated
			if ( get_option( 'wp_cft_db_version' ) != WP_CFT_DB_VERSION ) {
				//include_once ('file-name-installer.php');
				//wp_cft_run_db_upgrade();
			}
		}
	}

}//End of class

}//End of class not exists check

$GLOBALS['wp_cft_main'] = new WP_CFT_Main();
