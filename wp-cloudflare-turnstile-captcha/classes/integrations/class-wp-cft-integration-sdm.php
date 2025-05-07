<?php

class WP_CFT_SDM_Integration {

	public WP_CFT_Turnstile $turnstile;

	public WP_CFT_Config $settings;

	public function __construct() {
		$this->turnstile = WP_CFT_Turnstile::get_instance();

		$this->settings  = WP_CFT_Config::get_instance();

		$wp_cft_enable_on_sdm_download = $this->settings->get_value( 'wp_cft_enable_on_sdm_download' );
		if ( $wp_cft_enable_on_sdm_download ) {
			add_filter( 'sdm_before_download_button', array( $this, 'render_sdm_download_form_cft' ), 10, 3 );
			add_action( 'sdm_process_download_request', array( $this, 'check_asp_checkout' ) );
		}
	}

	public function render_sdm_download_form_cft($output, $id, $args ) {
		ob_start();
		$this->turnstile->render_implicit('wp_cft_callback', 'sdm-download-'. $id, wp_rand(), '' );
		$output .= ob_get_clean();
		return $output;
	}

	public function check_asp_checkout( ) {
		// Check Turnstile
		$result = $this->turnstile->check( );

		$success       = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		// Send error response if failed.
		if ( empty($success) ) {
			$error_msg = __( 'Cloudflare Turnstile error: ', 'wp-cf-turnstile' );
			$error_msg .= WP_CFT_Utils::failed_message( $error_message );

			wp_die($error_msg);
		}
	}
}