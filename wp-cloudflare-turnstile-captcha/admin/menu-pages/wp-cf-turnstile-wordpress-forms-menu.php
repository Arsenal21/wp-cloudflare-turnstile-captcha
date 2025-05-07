<?php

class WP_CFT_WordPress_Forms_Menu extends WP_CFT_Admin_Menu {
	public $menu_page_slug = WP_CFT_WORDPRESS_FORMS_MENU_SLUG;

	/* Specify all the tabs of this menu in the following array */
	public $menu_tabs = array( 'tab1' => 'WordPress Forms' );

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
		echo '<h1>'.__('WordPress Forms Integration', 'wp-cft-turnstile').'</h1>';
		//Get the current tab
		$tab = $this->get_current_tab();

		//Render the menu table before poststuff (for the menu tabs to be correctly rendered without CSS issue)
		$this->render_menu_tabs();

		//Post stuff and body
		echo '<div id="poststuff"><div id="post-body">';
		echo '<div class="wp-cft-grey-box">'.sprintf(__('See the documentation here: %s', 'wp-cft-turnstile'), '<a href="#">'. __('Documentation', 'wp-cft-turnstile') .'</a>').'</div>';

		//Switch based on the current tab
		$tab_keys = array_keys( $this->menu_tabs );
		switch ( $tab ) {
			case $tab_keys[0]:
			default :
				$this->postbox( "wp-cft-wp-settings-postbox", "WordPress Settings", $this->wp_settings_postbox_content() );
				break;
		}

		echo '</div></div>'; //end poststuff and post-body
		echo '</div>'; //<!-- end or wrap -->
	}

	public function wp_settings_postbox_content() {

		$settings = WP_CFT_Config::get_instance();
		if ( isset( $_POST['wp_cft_wp_settings_submit'] ) && check_admin_referer( 'wp_cft_wp_settings_nonce' ) ) {
			$settings->set_value( 'wp_cft_enable_on_wp_login', ( isset( $_POST['wp_cft_enable_on_wp_login'] ) ? 'checked="checked"' : '' ) );
			$settings->set_value( 'wp_cft_enable_on_wp_register', ( isset( $_POST['wp_cft_enable_on_wp_register'] ) ? 'checked="checked"' : '' ) );
			$settings->set_value( 'wp_cft_enable_on_wp_reset_password', ( isset( $_POST['wp_cft_enable_on_wp_reset_password'] ) ? 'checked="checked"' : '' ) );
			$settings->set_value( 'wp_cft_enable_on_wp_comment', ( isset( $_POST['wp_cft_enable_on_wp_comment'] ) ? 'checked="checked"' : '' ) );

			$settings->save_config();

			echo '<div class="notice notice-success"><p>' . __( 'Settings saved.', 'wp-cft-turnstile' ) . '</p></div>';
		}

		$wp_cft_enable_on_wp_login          = $settings->get_value( 'wp_cft_enable_on_wp_login' );
		$wp_cft_enable_on_wp_register       = $settings->get_value( 'wp_cft_enable_on_wp_register' );
		$wp_cft_enable_on_wp_reset_password = $settings->get_value( 'wp_cft_enable_on_wp_reset_password' );
		$wp_cft_enable_on_wp_comment        = $settings->get_value( 'wp_cft_enable_on_wp_comment' );

		$output = '';
		ob_start();
		?>
        <form action="" method="post">
            <table class="form-table">
                <tr>
                    <th>
                        <label><?php _e( 'WordPress Login', 'wp-cf-turnstile' ); ?></label>
                    </th>
                    <td>
                        <input type="checkbox"
                               name="wp_cft_enable_on_wp_login" <?php echo esc_attr( $wp_cft_enable_on_wp_login ); ?>
                               value="1">
                        <p class="description"><?php _e( 'Enable turnstile captcha on the login form of wordpress.', 'wp-cf-turnstile' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label><?php _e( 'WordPress Register', 'wp-cf-turnstile' ); ?></label>
                    </th>
                    <td>
                        <input type="checkbox"
                               name="wp_cft_enable_on_wp_register" <?php echo esc_attr( $wp_cft_enable_on_wp_register ); ?>
                               value="1">
                        <p class="description"><?php _e( 'Enable turnstile captcha on the registration form of wordpress.', 'wp-cf-turnstile' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label><?php _e( 'WordPress Reset Password', 'wp-cf-turnstile' ); ?></label>
                    </th>
                    <td>
                        <input type="checkbox"
                               name="wp_cft_enable_on_wp_reset_password" <?php echo esc_attr( $wp_cft_enable_on_wp_reset_password ); ?>
                               value="1">
                        <p class="description"><?php _e( 'Enable turnstile captcha on the reset password form of wordpress.', 'wp-cf-turnstile' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label><?php _e( 'WordPress Comment', 'wp-cf-turnstile' ); ?></label>
                    </th>
                    <td>
                        <input type="checkbox"
                               name="wp_cft_enable_on_wp_comment" <?php echo esc_attr( $wp_cft_enable_on_wp_comment ); ?>
                               value="1">
                        <p class="description"><?php _e( 'Enable turnstile captcha on the comment form of wordpress.', 'wp-cf-turnstile' ); ?></p>
                    </td>
                </tr>
            </table>
			<?php wp_nonce_field( 'wp_cft_wp_settings_nonce' ) ?>
			<?php submit_button( __( 'Save Changes' ), 'primary', 'wp_cft_wp_settings_submit' ) ?>
        </form>
		<?php
		$output .= ob_get_clean();

		return $output;
	}
} //end class