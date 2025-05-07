<?php

class WP_CFT_ASP_Integration {

	public WP_CFT_Turnstile $turnstile;

	public WP_CFT_Config $settings;

	public function __construct() {
		$this->turnstile = WP_CFT_Turnstile::get_instance();

		$this->settings  = WP_CFT_Config::get_instance();

		$wp_cft_enable_on_asp_checkout = $this->settings->get_value( 'wp_cft_enable_on_asp_checkout' );
		if ( $wp_cft_enable_on_asp_checkout ) {
			add_filter( 'asp_ng_pp_data_ready', array( $this, 'asp_ng_pp_data_ready' ), 10, 2 );
			add_action( 'asp_ng_pp_output_before_closing_head', array( $this, 'print_cft_scripts' ) );
			add_action( 'asp_ng_pp_output_before_closing_form', array( $this, 'render_asp_checkout_form_cft' ) );
			add_action( 'asp_ng_before_api_pre_submission_validation', array( $this, 'check_asp_checkout' ) ); // Here use the lower priority to get the WP_User object properly.
		}
	}

	public function asp_ng_pp_data_ready( $data, $atts ) {
		$addon            = array(
			'name'    => 'WP Cloudflare Turnstile',
			'handler' => 'WpCftHandlerNG',
		);
		$data['addons'][] = $addon;

		return $data;
	}

	public function print_cft_scripts(){
		WP_CFT_Turnstile::register_scripts();

		wp_print_scripts('wp-cft-script');
		wp_print_styles('wp-cf-turnstile-styles');
	}

	public function render_asp_checkout_form_cft() {
		echo '<div class="wp-cft-place-widget-center">';
		echo $this->turnstile->get_implicit_widget( 'wp_cft_asp_checkout_form_callback', 'asp-checkout', wp_rand() );
		echo '</div>';
	}

	public function check_asp_checkout( ) {
		$token = isset( $_POST['wp_cft_token_response'] ) ? $_POST['wp_cft_token_response'] : '';

		// Check Turnstile
		$result = $this->turnstile->check( $token );

		$success       = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		// Send error response if failed.
		if ( $success != true ) {
			$out            = array();
			$out['success'] = false;

			$error_msg = __( 'Cloudflare Turnstile error: ', 'wp-cf-turnstile' );
			$error_msg .= WP_CFT_Utils::failed_message( $error_message );

			$out['err'] = $error_msg;
			wp_send_json( $out );
		}
	}
}