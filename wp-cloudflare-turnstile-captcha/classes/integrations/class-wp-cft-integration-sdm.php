<?php

class WP_CFT_SDM_Integration {

	public WP_CFT_Turnstile $turnstile;

	public WP_CFT_Config $settings;

	public function __construct() {
		$this->turnstile = WP_CFT_Turnstile::get_instance();

		$this->settings  = WP_CFT_Config::get_instance();

		$wp_cft_enable_on_sdm_download = $this->settings->get_value( 'wp_cft_enable_on_sdm_download' );
		if ( $wp_cft_enable_on_sdm_download ) {
			add_filter( 'sdm_before_download_button', array( $this, 'render_sdm_download_form_cft' ), 30, 3 );
			add_action( 'sdm_process_download_request', array( $this, 'check_download_request' ) );

            // For hidden downloads.
            add_action( 'sdm_hd_process_download_request', array( $this, 'check_download_request' ) );
		}

		$wp_cft_enable_on_sdm_sf = $this->settings->get_value( 'wp_cft_enable_on_sdm_sf' );
		if ( $wp_cft_enable_on_sdm_sf ) {
			add_filter( 'sdm_sf_before_download_button', array( $this, 'render_sdm_sf_download_form_cft' ), 30, 3 );
			add_action( 'sdm_sf_download_form_submitted', array( $this, 'check_download_request' ) );
		}
	}

	/**
     * For core and hidden downloads form.
	 */
	public function render_sdm_download_form_cft($output, $id, $args ) {
		wp_enqueue_script( 'wp-cft-script-sdm', WP_CFT_URL . '/js/wp-cft-script-sdm.js' , array( 'wp-cft-script' ), WP_CFT_VERSION, array(
			'strategy'  => 'defer',
			'in_footer' => true,
		) );

		$cft_unique_id = 'sdm' . wp_rand();

        $dl_specific_cft_class_name = "wp-cft-sdm-dl-" . $id; // This unique class will be used in js to detect desired cft response field.

        $dl_specific_cft_callback_name = "wp_cft_sdm_callback" . $cft_unique_id;

        $class = '';
        $class .= $dl_specific_cft_class_name;

        $fancy = isset($args['fancy']) ? intval($args['fancy']) : '';
        $addon = isset($args['addon']) ? $args['addon'] : '';

        // Check if sdm squeeze form addon.
        if ($addon == 'sdm-sf') {
            if (in_array($fancy, array(0, 1, 4, 5))){
                $class .= ' wp-cft-place-widget-center';
                $class .= ' wp-cft-widget-mt-12';
            }

            if (in_array($fancy, array(0, 1))) {
                $class .= ' wp-cft-small-widget-size';
            }
        }

        // Check if core sdm plugin.
        if ( empty($addon) && $fancy == 2 ){
            // Force display compact widget size for core plugins fancy 2 display
            $this->turnstile->add_settings_override('widget_size', 'compact');

	        $class .= ' wp-cft-place-widget-center';
        }

		$output = '';
		ob_start();
		?>
		<script>
            // Create a callback function with unique function name, so it is specific to sdm download item widget.
            window['<?php echo esc_js($dl_specific_cft_callback_name)?>'] = function (token){
                console.log('[WP CFT] Cloudflare turnstile challenge successful.');

                const cft_cont_id = 'cf-turnstile-' + '<?php echo esc_js($cft_unique_id) ?>';
                const cft_cont = document.getElementById(cft_cont_id);

                // Get the download form (if there is any).
                const dl_form = wp_cft_get_dl_form(cft_cont);
                if(dl_form){
                    // Append cft token as am input field, so that it can be captured after form submission.

                    // First check if the token input field already exists or not.
                    const existing_cft_token_field = dl_form.querySelector('form[name="cf-turnstile-response"]');
                    if (existing_cft_token_field){
                        // Input field already exists, update token value.
                        existing_cft_token_field.value = token;
                    } else {
                        const cft_token_field = document.createElement('input');
                        cft_token_field.type = 'hidden';
                        cft_token_field.name = 'cf-turnstile-response';
                        cft_token_field.value = token;

                        dl_form.appendChild(cft_token_field);
                    }
                } else {
                    // Download form not found, It could be a download link button without a form wrap. Do nothing.
                }
            }
		</script>
		<?php
		$this->turnstile->render_implicit($dl_specific_cft_callback_name, 'sdm-download-'. $id, $cft_unique_id, $class );
		$output .= ob_get_clean();

        // Clear settings overrides to it doesn't affect next render.
        $this->turnstile->clear_settings_overrides();

		return $output;
	}

	/**
	 * For squeeze forms
	 */
	public function render_sdm_sf_download_form_cft($output, $id, $args ) {
        if (!is_array($args)) {
            $args = array();
        }
        $args['addon'] = 'sdm-sf';

        return $this->render_sdm_download_form_cft($output, $id, $args);
    }

	public function check_download_request( ) {
		// Check Turnstile
		$result = $this->turnstile->check_cft_token_response();

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