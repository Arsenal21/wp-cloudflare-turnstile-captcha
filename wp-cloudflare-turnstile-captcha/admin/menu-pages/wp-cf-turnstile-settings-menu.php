<?php

class WP_CFT_Settings_Menu extends WP_CFT_Admin_Menu {
	public $menu_page_slug = WP_CFT_SETTINGS_MENU_SLUG;

	/* Specify all the tabs of this menu in the following array */
	public $menu_tabs = array( 'tab1' => 'General Settings' );

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
		echo '<h1>' . __( 'WP Cloudflare Turnstile Settings', 'wp-cft-turnstile' ) . '</h1>';
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
				//include_once('file-to-handle-this-tab-rendering.php');
				//call_function_to_render_tab1();
				$this->postbox( "wp-cft-turnstile-api-settings-postbox", "API Settings", $this->cft_api_settings_postbox_content() );
				$this->postbox( "wp-cft-turnstile-appearance-settings-postbox", "Display and Appearance", $this->cft_display_settings_postbox_content() );
				break;
		}

		echo '</div></div>'; //end poststuff and post-body
		echo '</div>'; //<!-- end or wrap -->
	}

	public function cft_api_settings_postbox_content() {

		$settings = WP_CFT_Config::get_instance();
		if ( isset( $_POST['wp_cft_api_settings_submit'] ) && check_admin_referer( 'wp_cft_api_settings_nonce' ) ) {
			$settings->set_value( 'wp_cft_site_key', sanitize_text_field( $_POST['wp_cft_site_key'] ) );
			$settings->set_value( 'wp_cft_secret_key', sanitize_text_field( $_POST['wp_cft_secret_key'] ) );

			$settings->save_config();

			echo '<div class="notice notice-success"><p>' . __( 'Settings saved.', 'wp-cft-turnstile' ) . '</p></div>';
		}

		$wp_cft_site_key   = $settings->get_value( 'wp_cft_site_key' );
		$wp_cft_secret_key = $settings->get_value( 'wp_cft_secret_key' );

		$output = '';
		ob_start();
		?>
        <form action="" method="post">
            <table class="form-table">
                <tr>
                    <th>
                        <label><?php _e( 'Site Key', 'wp-cf-turnstile' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="wp_cft_site_key" class="wp_cft-settings-text-field-cat-2"
                               value="<?php echo esc_attr( $wp_cft_site_key ); ?>" required>
                        <p class="description"><?php _e( 'The site key for the Cloudflare Turnstile API.', 'wp-cf-turnstile' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label><?php _e( 'Secret Key', 'wp-cf-turnstile' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="wp_cft_secret_key" class="wp_cft-settings-text-field-cat-2"
                               value="<?php echo esc_attr( $wp_cft_secret_key ); ?>" required>
                        <p class="description"><?php _e( 'The secret key for the Cloudflare Turnstile API.', 'wp-cf-turnstile' ); ?></p>
                    </td>
                </tr>
            </table>
			<?php wp_nonce_field( 'wp_cft_api_settings_nonce' ) ?>
			<?php submit_button( __( 'Save Changes' ), 'primary', 'wp_cft_api_settings_submit' ) ?>
        </form>
		<?php
		$output .= ob_get_clean();

		return $output;
	}

	public function cft_display_settings_postbox_content() {

		$settings = WP_CFT_Config::get_instance();
		if ( isset( $_POST['wp_cft_display_settings_submit'] ) && check_admin_referer( 'wp_cft_display_settings_nonce' ) ) {
			$settings->set_value( 'wp_cft_theme', sanitize_text_field( $_POST['wp_cft_theme'] ) );
			$settings->set_value( 'wp_cft_widget_size', sanitize_text_field( $_POST['wp_cft_widget_size'] ) );
			$settings->set_value( 'wp_cft_custom_error_msg', sanitize_text_field( $_POST['wp_cft_custom_error_msg'] ) );

			$settings->save_config();

			echo '<div class="notice notice-success"><p>' . __( 'Settings saved.', 'wp-cft-turnstile' ) . '</p></div>';
		}

		$wp_cft_theme            = $settings->get_value( 'wp_cft_theme' );
		$wp_cft_widget_size      = $settings->get_value( 'wp_cft_widget_size' );
		$wp_cft_custom_error_msg = $settings->get_value( 'wp_cft_custom_error_msg' );

		$output = '';
		ob_start();
		?>
        <form action="" method="post">
            <table class="form-table">
                <tr>
                    <th>
                        <label><?php _e( 'Theme', 'wp-cf-turnstile' ); ?></label>
                    </th>
                    <td>
                        <select name="wp_cft_theme">
                            <option value="light" <?php echo $wp_cft_theme == 'light' ? 'selected' : ''; ?>><?php _e( 'Light', 'wp-cf-turnstile' ) ?></option>
                            <option value="dark" <?php echo $wp_cft_theme == 'dark' ? 'selected' : ''; ?>><?php _e( 'Dark', 'wp-cf-turnstile' ) ?></option>
                            <option value="auto" <?php echo $wp_cft_theme == 'auto' ? 'selected' : ''; ?>><?php _e( 'Auto', 'wp-cf-turnstile' ) ?></option>
                        </select>
                        <p class="description"><?php _e( 'The site key for the Cloudflare Turnstile API.', 'wp-cf-turnstile' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label><?php _e( 'Widget Size', 'wp-cf-turnstile' ); ?></label>
                    </th>
                    <td>
                        <select name="wp_cft_widget_size">
                            <option value="normal" <?php echo $wp_cft_widget_size == 'normal' ? 'selected' : ''; ?>><?php _e( 'Normal (300px)', 'wp-cf-turnstile' ) ?></option>
                            <option value="flexible" <?php echo $wp_cft_widget_size == 'flexible' ? 'selected' : ''; ?>><?php _e( 'Flexible (100%)', 'wp-cf-turnstile' ) ?></option>
                            <option value="compact" <?php echo $wp_cft_widget_size == 'compact' ? 'selected' : ''; ?>><?php _e( 'Compact (150px)', 'wp-cf-turnstile' ) ?></option>
                        </select>
                        <p class="description"><?php _e( 'The widget display size of turnstile checkbox.', 'wp-cf-turnstile' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label><?php _e( 'Custom Error Message', 'wp-cf-turnstile' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="wp_cft_custom_error_msg" class="wp_cft-settings-text-field-cat-2"
                               value="<?php echo esc_attr( $wp_cft_custom_error_msg ); ?>">
                        <p class="description"><?php _e( 'Shown if the form is submitted without completing the Turnstile challenge. Leave blank to use the default.', 'wp-cf-turnstile' ); ?></p>
                    </td>
                </tr>
            </table>
			<?php wp_nonce_field( 'wp_cft_display_settings_nonce' ) ?>
			<?php submit_button( __( 'Save Changes' ), 'primary', 'wp_cft_display_settings_submit' ) ?>
        </form>
		<?php
		$output .= ob_get_clean();

		return $output;
	}
} //end class