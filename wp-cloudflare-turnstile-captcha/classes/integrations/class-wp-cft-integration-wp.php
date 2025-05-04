<?php

class WP_CFT_WordPress_Integration {

	public WP_CFT_Turnstile $turnstile;

	public WP_CFT_Config $settings;

	public function __construct() {

		$this->turnstile = WP_CFT_Turnstile::get_instance();
		$this->settings  = WP_CFT_Config::get_instance();

		$wp_cft_enable_on_wp_login = $this->settings->get_value( 'wp_cft_enable_on_wp_login' );
		if ( $wp_cft_enable_on_wp_login ) {
			add_action( 'login_form', array( $this, 'render_wp_login_form_cft' ) );
			add_action( 'authenticate', array( $this, 'check_wp_login' ), 30, 1 ); // Here use the lower priority to get the WP_User object properly.
		}

		$wp_cft_enable_on_wp_register = $this->settings->get_value( 'wp_cft_enable_on_wp_register' );
		if ( $wp_cft_enable_on_wp_register ) {
			add_action('register_form', array($this, 'render_wp_register_form_cft'));
			add_action('registration_errors', array($this, 'check_wp_registration'), 10, 3);
		}

		$wp_cft_enable_on_wp_reset_password = $this->settings->get_value( 'wp_cft_enable_on_wp_reset_password' );
		if ( $wp_cft_enable_on_wp_reset_password ) {
			add_action('lostpassword_form', array($this, 'render_wp_pass_reset_form_cft'));
			add_action('lostpassword_post', array($this, 'check_wp_reset_password'), 10, 1);
		}

//		$wp_cft_enable_on_wp_comment = $this->settings->get_value( 'wp_cft_enable_on_wp_comment' );
//		if ( $wp_cft_enable_on_wp_comment ) {
//			add_action("comment_form_after", array($this, "render_comment_form_cft"));
//			add_action('comment_form_submit_button','check_wp_comment', 100, 2);
//		}
	}

	public function render_wp_login_form_cft() {
		$this->turnstile->render_implicit( '#wp-submit', 'wp_cft_callback', 'wordpress-login', '-' . wp_rand() );
	}

	public function render_wp_register_form_cft() {
		$this->turnstile->render_implicit('#wp-submit', 'wp_cft_callback', 'wordpress-register', '-' . wp_rand());
	}

	public function render_wp_pass_reset_form_cft() {
		$this->turnstile->render_implicit('#wp-submit', 'wp_cft_callback', 'wordpress-reset', '-' . wp_rand());
	}

	public function render_comment_form_cft() {
		if ( wp_doing_ajax() ) {
			wp_print_scripts('cfturnstile');
			wp_print_styles('cfturnstile-css');
		}
	}

	public function check_wp_login( $user ) {
		// Check skip
		if ( ! isset( $user->ID ) ) {
			return $user;
		}
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return $user;
		} // Skip XMLRPC
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $user;
		} // Skip REST API
		if ( isset( $_POST['edd_login_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['edd_login_nonce'] ), 'edd-login-nonce' ) ) {
			return $user;
		} // Skip EDD
		if ( is_wp_error( $user ) && isset( $user->errors['empty_username'] ) && isset( $user->errors['empty_password'] ) ) {
			return $user;
		} // Skip Errors

		// Skip if not on login page
		$login_page_only = true; // TODO: It might need to adjust this later.
		if ( $login_page_only && ! WP_CFT_Utils::is_login_page()) {
			return $user;
		}

		// Check Turnstile
		$result = $this->turnstile->check();

		// TODO: Remove this
		// wp_die(print_r($result, true));
		// return new WP_Error( 'cfturnstile_error', print_r( $result, true ) );

		$success = $result['success'];
		if ( $success != true ) {
			 $user = new WP_Error( 'wpf_cf_turnstile_error', WP_CFT_Utils::failed_message() );
		}

		return $user;
	}

	public function check_wp_registration($errors){
		// Check skip
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return $errors;
		} // Skip XMLRPC
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $errors;
		} // Skip REST API
		if ( isset( $_POST['woocommerce-register-nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['woocommerce-register-nonce'] ), 'woocommerce-register' ) ) {
			return $errors;
		} // Skip Woo
		if ( isset( $_POST['edd_register_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['edd_register_nonce'] ), 'edd-register-nonce' ) ) {
			return $errors;
		} // Skip EDD

		// Skip if not on login page
		$register_page_only = true; // TODO: It might need to adjust this later.
		if ( $register_page_only && ! WP_CFT_Utils::is_registration_page() ) {
			return $errors;
		}

		// Skip Logged In Admins
		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			return $errors;
		}

		$result = $this->turnstile->check();

		$success = isset( $result['success'] ) ? boolval( $result['success'] ) : false;

		if ( ! $success ) {
			$errors->add( 'wpf_cf_turnstile_error', WP_CFT_Utils::failed_message() );
		}

		return $errors;
	}

	public function check_wp_reset_password($validation_errors){
		// Skip Woo
		if ( isset( $_POST['woocommerce-lost-password-nonce'] ) ) {
			return;
		}

		// Check if password reset page.
		if ( WP_CFT_Utils::is_reset_password_page() ) {
			$check   = $this->turnstile->check();

			$success = isset( $check['success'] ) ? boolval( $check['success'] ) : false;

			if ( ! $success ) {
				$validation_errors->add( 'wpf_cf_turnstile_error', WP_CFT_Utils::failed_message() );
			}
		}
	}

	public function check_wp_comment(){

	}

}