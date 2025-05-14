<?php

/*
 * Inits the admin dashboard side of things.
 * Main admin file which loads all settings panels and sets up admin menus.
 */

class WP_CFT_Admin_Init {
	public $main_menu_page;
	public $dashboard_menu;
	public $settings_menu;

	public function __construct() {
		$this->admin_includes();
		add_action( 'admin_print_scripts', array( $this, 'admin_menu_page_scripts' ) );
		add_action( 'admin_print_styles', array( $this, 'admin_menu_page_styles' ) );
		add_action( 'admin_menu', array( $this, 'create_admin_menus' ) );
	}

	public function admin_includes() {
		include_once WP_CFT_PATH . '/admin/menu-pages/wp-cf-turnstile-admin-menu.php';
	}

	public function admin_menu_page_scripts() {
		//make sure we are on the appropriate menu page
		if ( isset( $_GET['page'] ) && strpos( sanitize_text_field(wp_unslash($_GET['page'])), WP_CFT_MENU_SLUG_PREFIX ) !== false ) {
			//wp_enqueue_script('postbox');
			//wp_enqueue_script('dashboard');
			//wp_enqueue_script('thickbox');
			//wp_enqueue_script('media-upload');
		}
	}

	public function admin_menu_page_styles() {
		//make sure we are on the appropriate menu page
		if ( isset( $_GET['page'] ) && strpos( sanitize_text_field(wp_unslash($_GET['page'])), WP_CFT_MENU_SLUG_PREFIX ) !== false ) {
			wp_enqueue_style( 'wp-cft-admin-css', WP_CFT_URL . '/css/wp-cf-turnstile-admin-styles.css', array(), WP_CFT_VERSION );
		}
	}

	public function create_admin_menus() {
		$menu_icon_url = WP_CFT_URL . '/images/plugin-icon.png';

		$this->main_menu_page = add_menu_page( __( 'Cloudflare Turnstile', 'cf-turnstile-for-wp' ), __( 'Cloudflare Turnstile', 'cf-turnstile-for-wp' ), WP_CFT_MANAGEMENT_PERMISSION, WP_CFT_MAIN_MENU_SLUG, array( $this, 'handle_settings_menu_rendering' ), $menu_icon_url );
		add_submenu_page( WP_CFT_MAIN_MENU_SLUG, __( 'Settings', 'cf-turnstile-for-wp' ), __( 'Settings', 'cf-turnstile-for-wp' ), WP_CFT_MANAGEMENT_PERMISSION, WP_CFT_MAIN_MENU_SLUG, array( $this, 'handle_settings_menu_rendering') );
		add_submenu_page( WP_CFT_MAIN_MENU_SLUG, __( 'WordPress Forms', 'cf-turnstile-for-wp' ), __( 'WordPress Forms', 'cf-turnstile-for-wp' ), WP_CFT_MANAGEMENT_PERMISSION, WP_CFT_WORDPRESS_FORMS_MENU_SLUG, array( $this, 'handle_wordpress_menu_rendering' ) );
		add_submenu_page( WP_CFT_MAIN_MENU_SLUG, __( 'Integrations', 'cf-turnstile-for-wp' ), __( 'Integrations', 'cf-turnstile-for-wp' ), WP_CFT_MANAGEMENT_PERMISSION, WP_CFT_INTEGRATIONS_MENU_SLUG, array( $this, 'handle_integrations_menu_rendering') );

		do_action( 'wp_cft_admin_menu_created' );
	}

	public function handle_settings_menu_rendering() {
		include_once WP_CFT_PATH . '/admin/menu-pages/wp-cf-turnstile-settings-menu.php';
		$this->settings_menu = new WP_CFT_Settings_Menu();
	}

	public function handle_wordpress_menu_rendering() {
		include_once WP_CFT_PATH . '/admin/menu-pages/wp-cf-turnstile-wordpress-forms-menu.php';
		$this->dashboard_menu = new WP_CFT_WordPress_Forms_Menu();
	}

	public function handle_integrations_menu_rendering() {
		include_once WP_CFT_PATH . '/admin/menu-pages/wp-cf-turnstile-integrations-menu.php';
		$this->dashboard_menu = new WP_CFT_Integrations_Menu();
	}
}//End of class
