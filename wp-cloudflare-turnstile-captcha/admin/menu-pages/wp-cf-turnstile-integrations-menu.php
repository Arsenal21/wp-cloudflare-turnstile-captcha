<?php

class WP_CFT_Integrations_Menu extends WP_CFT_Admin_Menu {
	public $menu_page_slug = WP_CFT_INTEGRATIONS_MENU_SLUG;

	/* Specify all the tabs of this menu in the following array */
	public $menu_tabs = array( 'tab1' => 'Accept Stripe Payments', 'tab2' => 'Simple Download Monitor' );

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
			case $tab_keys[1]:
				$this->postbox( "wp-cft-integration-settings-postbox", "Simple Download Monitor", $this->sdm_integration_settings_postbox_content() );
				break;
			case $tab_keys[0]:
			default :
				$this->postbox( "wp-cft-integration-settings-postbox", "Accept Stripe Payments", $this->asp_integration_settings_postbox_content() );
				break;
		}

		echo '</div></div>'; //end poststuff and post-body
		echo '</div>'; //<!-- end or wrap -->
	}

	public function asp_integration_settings_postbox_content() {

		$settings = WP_CFT_Config::get_instance();
		if ( isset( $_POST['wp_cft_asp_settings_submit'] ) && check_admin_referer( 'wp_cft_asp_settings_nonce' ) ) {
			$settings->set_value( 'wp_cft_enable_on_asp_checkout', ( isset( $_POST['wp_cft_enable_on_asp_checkout'] ) ? 'checked="checked"' : '' ) );

			$settings->save_config();

			echo '<div class="notice notice-success"><p>' . __( 'Settings saved.', 'wp-cft-turnstile' ) . '</p></div>';
		}

		$wp_cft_enable_on_asp_checkout = $settings->get_value( 'wp_cft_enable_on_asp_checkout' );

		$output = '';
		ob_start();
		?>
        <form action="" method="post">
            <table class="form-table">
                <tr>
                    <th>
                        <label><?php _e( 'Checkout Form', 'wp-cf-turnstile' ); ?></label>
                    </th>
                    <td>
                        <input type="checkbox"
                               name="wp_cft_enable_on_asp_checkout" <?php echo esc_attr( $wp_cft_enable_on_asp_checkout ); ?>
                               value="1">
                        <p class="description"><?php _e( 'Enable turnstile captcha on the checkout form of stripe payments plugin.', 'wp-cf-turnstile' ); ?></p>
                    </td>
                </tr>
            </table>
			<?php wp_nonce_field( 'wp_cft_asp_settings_nonce' ) ?>
			<?php submit_button( __( 'Save Changes' ), 'primary', 'wp_cft_asp_settings_submit' ) ?>
        </form>
		<?php
		$output .= ob_get_clean();

		return $output;
	}


	public function sdm_integration_settings_postbox_content() {
		$settings = WP_CFT_Config::get_instance();
		if ( isset( $_POST['wp_cft_sdm_settings_submit'] ) && check_admin_referer( 'wp_cft_sdm_settings_nonce' ) ) {
			$settings->set_value( 'wp_cft_enable_on_sdm_download', ( isset( $_POST['wp_cft_enable_on_sdm_download'] ) ? 'checked="checked"' : '' ) );
			$settings->set_value( 'wp_cft_enable_on_sdm_sf', ( isset( $_POST['wp_cft_enable_on_sdm_sf'] ) ? 'checked="checked"' : '' ) );

			$settings->save_config();

			echo '<div class="notice notice-success"><p>' . __( 'Settings saved.', 'wp-cft-turnstile' ) . '</p></div>';
		}

		$wp_cft_enable_on_sdm_download = $settings->get_value( 'wp_cft_enable_on_sdm_download' );
		$wp_cft_enable_on_sdm_sf = $settings->get_value( 'wp_cft_enable_on_sdm_sf' );

		$output = '';
		ob_start();
		?>
        <form action="" method="post">
            <table class="form-table">
                <tr>
                    <th>
                        <label><?php _e( 'Download Form', 'wp-cf-turnstile' ); ?></label>
                    </th>
                    <td>
                        <input type="checkbox"
                               name="wp_cft_enable_on_sdm_download" <?php echo esc_attr( $wp_cft_enable_on_sdm_download ); ?>
                               value="1">
                        <p class="description"><?php _e( 'Enable turnstile captcha on the download forms of simple download monitor core plugin.', 'wp-cf-turnstile' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th>
                        <label><?php _e( 'Squeeze Form', 'wp-cf-turnstile' ); ?></label>
                    </th>
                    <td>
                        <input type="checkbox"
                               name="wp_cft_enable_on_sdm_sf" <?php echo esc_attr( $wp_cft_enable_on_sdm_sf ); ?>
                               value="1">
                        <p class="description"><?php _e( 'Enable turnstile captcha on the download forms of squeeze form addon.', 'wp-cf-turnstile' ); ?></p>
                    </td>
                </tr>
            </table>
			<?php wp_nonce_field( 'wp_cft_sdm_settings_nonce' ) ?>
			<?php submit_button( __( 'Save Changes' ), 'primary', 'wp_cft_sdm_settings_submit' ) ?>
        </form>
		<?php
		$output .= ob_get_clean();

		return $output;
	}
} //end class