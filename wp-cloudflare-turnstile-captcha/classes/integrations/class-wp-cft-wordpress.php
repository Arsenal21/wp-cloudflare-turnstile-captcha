<?php

class WP_CFT_WordPress_Integration {
	public function __construct() {
		add_action( 'login_form', array( $this, 'render_cft' ) );
		add_action( 'authenticate', array( $this, 'check_wp_login' ) );
	}

	public function render_cft() {

		$wp_cft_enable_on_wp_login = WP_CFT_Config::get_instance()->get_value( 'wp_cft_enable_on_wp_login' );

		if ( $wp_cft_enable_on_wp_login ) {
			WP_CFT_Turnstile::get_instance()->render_explicit( '#wp-submit', 'turnstileWPCallback', 'wordpress-login', '-' . wp_rand() );
		}
	}

	public function check_wp_login( $user ) {
//		wp_die(print_r($user, true));
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
		$login_page_only = true;
		if ( $login_page_only ) {
			$login_url_path   = wp_parse_url( wp_login_url(), PHP_URL_PATH );
			$current_url_path = wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
			if ( $current_url_path !== $login_url_path ) {
				return $user;
			}
		}

		// Custom skip filter
//		if ( apply_filters( 'cfturnstile_wp_login_checks', false ) === true ) {
//			return $user;
//		}

		// Start session
		if ( ! session_id() ) {
			session_start();
		}

		// Check if already validated
		if ( isset( $_SESSION['cfturnstile_login_checked'] ) && wp_verify_nonce( sanitize_text_field( $_SESSION['cfturnstile_login_checked'] ), 'cfturnstile_login_check' ) ) {
			return $user;
		}

		// Check Turnstile
		$check   = WP_CFT_Turnstile::get_instance()->check();

//		wp_die(print_r($check, true));

		$success = $check['success'];
		if ( $success != true ) {
//			$user = new WP_Error( 'cfturnstile_error', cfturnstile_failed_message() );
			$user = new WP_Error( 'cfturnstile_error', "Some type of error happened while logging in" ); // TODO: Need to update this
			do_action( 'cfturnstile_wp_login_failed' );
		} else {
			$nonce                                 = wp_create_nonce( 'cfturnstile_login_check' );
			$_SESSION['cfturnstile_login_checked'] = $nonce;
		}

		return $user;
	}
}