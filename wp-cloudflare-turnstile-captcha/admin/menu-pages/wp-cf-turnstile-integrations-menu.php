<?php

class WP_CFT_Integrations_Menu extends WP_CFT_Admin_Menu {
	public $menu_page_slug = WP_CFT_SETTINGS_MENU_SLUG;

	/* Specify all the tabs of this menu in the following array */
	public $menu_tabs = array( 'tab1' => 'Plugin 1 Settings' );

	public function __construct() {
		$this->render_settings_menu_page();
	}

	public function get_current_tab() {
		//Get the current tab (if any), otherwise default to the first tab.
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'tab1';

		return $tab;
	}

	/*
	 * Renders our tabs of this menu as nav items
	 */
	public function render_menu_tabs() {
		$current_tab = $this->get_current_tab();

		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $this->menu_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->menu_page_slug . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
		}
		echo '</h2>';
	}

	/*
	 * The menu rendering goes here
	 */
	public function render_settings_menu_page() {
		echo '<div class="wrap">';
		echo '<h1>' . __( 'Plugin Integrations settings', 'wp-cft-turnstile' ) . '</h1>';
		//Get the current tab
		$tab = $this->get_current_tab();

		//Render the menu tabe before poststuff (for the menu tabs to be correctly rendered withou CSS issue)
		$this->render_menu_tabs();

		//Post stuff and body
		echo '<div id="poststuff"><div id="post-body">';
		echo '<div class="wp-cft-grey-box">' . sprintf( __( 'See the documentation here: %s', 'wp-cft-turnstile' ), '<a href="#">' . __( 'Documentation', 'wp-cft-turnstile' ) . '</a>' ) . '</div>';

		//Switch based on the current tab
		$tab_keys = array_keys( $this->menu_tabs );
		switch ( $tab ) {
			case $tab_keys[0]:
			default :
				$this->postbox( "wp-cft-integration-settings-postbox", "Plugin 1", $this->integration_settings_postbox_content() );
				break;
		}

		echo '</div></div>'; //end poststuff and post-body
		echo '</div>'; //<!-- end or wrap -->
	}

	public function integration_settings_postbox_content() {
		$output = '';
		ob_start();
		?>
            <p>Integration settings renders here</p>
		<?php
		$output .= ob_get_clean();

		return $output;
	}
} //end class