<?php

class WP_CFT_Utils {
	public static function is_login_page() {
		$login_url_path   = wp_parse_url( wp_login_url(), PHP_URL_PATH );
		$current_url_path = wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

		return $current_url_path == $login_url_path;
	}

	public static function is_registration_page() {
		// The registration page is in the same page of login, but with an action param 'register';
		if ( self::is_login_page() ) {
			return isset( $_GET['action'] ) && $_GET['action'] == 'register';
		}

		return false;
	}

	public static function is_reset_password_page() {
		// The registration page is in the same page of login, but with an action param 'lostpassword';
		if ( self::is_login_page() ) {
			return isset( $_GET['action'] ) && $_GET['action'] == 'lostpassword';
		}

		return false;
	}

	/**
	 * Gets the custom Turnstile failed message
	 */
	public static function failed_message( $default = "" ) {
		$error_msg = WP_CFT_Config::get_instance()->get_value( 'wp_cft_custom_error_msg', $default );

		if ( empty( $error_msg ) ) {
			return __( 'Please verify that you are human.', 'wp-cf-turnstile' );
		} else {
			return sanitize_text_field( $error_msg );
		}
	}

	public function error_message_by_code( $code ) {
		switch ( $code ) {
			case 'missing-input-secret':
				return esc_html__( 'The secret parameter was not passed.', 'simple-cloudflare-turnstile' );
			case 'invalid-input-secret':
				return esc_html__( 'The secret parameter was invalid or did not exist.', 'simple-cloudflare-turnstile' );
			case 'missing-input-response':
				return esc_html__( 'The response parameter was not passed.', 'simple-cloudflare-turnstile' );
			case 'invalid-input-response':
				return esc_html__( 'The response parameter is invalid or has expired.', 'simple-cloudflare-turnstile' );
			case 'bad-request':
				return esc_html__( 'The request was rejected because it was malformed.', 'simple-cloudflare-turnstile' );
			case 'timeout-or-duplicate':
				return esc_html__( 'The response parameter has already been validated before.', 'simple-cloudflare-turnstile' );
			case 'internal-error':
				return esc_html__( 'An internal error happened while validating the response. The request can be retried.', 'simple-cloudflare-turnstile' );
			default:
				return esc_html__( 'There was an error with Turnstile response. Please check your keys are correct.', 'simple-cloudflare-turnstile' );
		}
	}

}
