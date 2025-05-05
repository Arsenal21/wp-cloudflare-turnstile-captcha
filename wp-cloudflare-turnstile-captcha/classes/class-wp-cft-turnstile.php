<?php

class WP_CFT_Turnstile {

	public static $instance;

	public WP_CFT_Config $settings;

	public static function get_instance() : self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->settings = WP_CFT_Config::get_instance();
	}

	public static function register_scripts() {
		wp_register_script( 'cloudflare-turnstile-script', 'https://challenges.cloudflare.com/turnstile/v0/api.js' );
		wp_register_script( 'wp-cft-script', WP_CFT_URL . '/js/public.js', array( 'cloudflare-turnstile-script' ), wp_rand(), array(
			'strategy'  => 'defer',
			'in_footer' => true,
		) );
	}

	/**
	 * Create turnstile field template.
	 */
	public function render_implicit( $button_id = '', $callback = '', $form_name = '', $unique_id = '', $class = '' ) {
		wp_enqueue_script( 'wp-cft-script' );

        do_action( "wp_cft_before_cft_widget", esc_attr( $unique_id ) );

		$site_key    = $this->settings->get_value( 'wp_cft_site_key' );
		$theme       = $this->settings->get_value( 'wp_cft_theme', 'light' );
		$language    = 'auto';
		$appearance  = 'always';
		$widget_size = $this->settings->get_value( 'wp_cft_widget_size', 'normal' );

        $cft_disable_button = false; // TODO: Need to adjust this later.
        $cft_failure_message_enable = false;
		?>
        <div id="cf-turnstile<?php echo esc_attr( $unique_id ); ?>"
            class="cf-turnstile <?php echo !empty($class) ? esc_attr( $class ) : '' ?>"
            <?php if ( $cft_disable_button ) { ?>
            data-callback="<?php echo esc_attr( $callback ); ?>"
            <?php } ?>
            data-sitekey="<?php echo esc_attr( $site_key ); ?>"
            data-theme="<?php echo esc_attr( $theme ); ?>"
            data-language="<?php echo esc_attr( $language ); ?>"
            data-size="<?php echo esc_attr( $widget_size ); ?>"
            data-retry="auto"
            data-retry-interval="1000"
            data-action="<?php echo esc_attr( $form_name ); ?>"
            <?php if ( $cft_failure_message_enable ) { ?>
            data-callback="wp_cft_callback"
            data-error-callback="wp_cft_error_callback"
            <?php } ?>
            data-appearance="<?php echo esc_attr( $appearance ); ?>"
        ></div>
		<?php
		do_action( "wp_cft_after_cft_widget", esc_attr( $unique_id ), $button_id );
	}

	/**
	 * Checks Turnstile Captcha POST is Valid
	 */
	public function check( $post_data = "" ) {

		$results = array();

		// Check if whitelisted
        //		if(cfturnstile_whitelisted()) {
        //			$results['success'] = true;
        //			return $results;
        //		}

                // Hook to allow custom skip
        //		$skip = apply_filters('cfturnstile_widget_disable', false);
        //		if($skip) {
        //			$results['success'] = true;
        //			return $results;
        //		}

		// Check if POST data is empty
		if ( empty( $post_data ) && isset( $_POST['cf-turnstile-response'] ) ) {
			$post_data = sanitize_text_field( $_POST['cf-turnstile-response'] );
		}

		// Get Turnstile Keys from Settings
		$site_key    = $this->settings->get_value( 'wp_cft_site_key' );
		$secret_key = $this->settings->get_value( 'wp_cft_secret_key' );

		if ( $site_key && $secret_key ) {
			$headers  = array(
				'body' => [
					'secret'   => $secret_key,
					'response' => $post_data
				]
			);
			$verify   = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', $headers );
			$verify   = wp_remote_retrieve_body( $verify );
			$response = json_decode( $verify );

			if ( $response->success ) {
				$results['success'] = $response->success;
			} else {
				$results['success'] = false;
			}

			foreach ( $response as $site_key => $val ) {
				if ( $site_key == 'error-codes' ) {
					foreach ( $val as $site_key => $error_val ) {
						$results['error_code'] = $error_val;
                        //if ( $error_val == 'invalid-input-secret' ) {
                        //    update_option( 'cfturnstile_tested', 'no' ); // Disable if invalid secret
                        //}
					}
				}
			}

			do_action('wp_cft_after_cft_token_check', $response, $results);

			return $results;

		}

		return false;
	}
}