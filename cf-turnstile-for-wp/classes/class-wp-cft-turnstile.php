<?php

class WP_CFT_Turnstile {

	public static $instance;

	public WP_CFT_Config $settings;

    public $settings_overrdies = array();

	public static function get_instance() : self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->settings = WP_CFT_Config::get_instance();
	}

    public static function get_cft_cdn_url() {
	    return 'https://challenges.cloudflare.com/turnstile/v0/api.js';
    }

    public static function get_wp_cft_script_url() {
	    return WP_CFT_URL . '/js/wp-cft-script.js';
    }

    public static function get_wp_cft_style_url() {
        return WP_CFT_URL . '/css/wp-cf-turnstile-styles.css';
    }

	public static function register_scripts() {
		wp_register_script( 'cloudflare-turnstile-script', self::get_cft_cdn_url() );
		wp_register_script( 'wp-cft-script', self::get_wp_cft_script_url() , array( 'cloudflare-turnstile-script' ), WP_CFT_VERSION, array(
			'strategy'  => 'defer',
			'in_footer' => true,
		) );

        // Public style
		wp_register_style( 'wp-cf-turnstile-styles', self::get_wp_cft_style_url() , array(), WP_CFT_VERSION );
	}

    public function get_implicit_widget( $callback = '', $form_name = '', $unique_id = '', $class = '' ){
	    $site_key    = $this->settings->get_value( 'wp_cft_site_key' );

        $widget_settings = $this->widget_settings();
	    $theme       = isset($widget_settings['theme']) ? $widget_settings['theme'] : 'auto';
	    $language    = isset($widget_settings['language']) ? $widget_settings['language'] : 'auto';
	    $appearance  = isset($widget_settings['appearance']) ? $widget_settings['appearance'] : 'always';
	    $widget_size = isset($widget_settings['widget_size']) ? $widget_settings['widget_size'] : 'normal';

        ob_start();
	    ?>
        <div id="cf-turnstile-<?php echo esc_attr( $unique_id ); ?>"
            class="cf-turnstile wp-cf-turnstile-div <?php echo !empty($class) ? esc_attr( $class ) : '' ?>"
            data-sitekey="<?php echo esc_attr( $site_key ); ?>"
            data-theme="<?php echo esc_attr( $theme ); ?>"
            data-language="<?php echo esc_attr( $language ); ?>"
            data-size="<?php echo esc_attr( $widget_size ); ?>"
            data-retry="auto"
            data-retry-interval="1000"
            data-action="<?php echo esc_attr( $form_name ); ?>"
            data-appearance="<?php echo esc_attr( $appearance ); ?>"
            <?php if ( !empty($callback) ) { ?>
            data-callback="<?php echo esc_attr( $callback ); ?>"
            <?php } ?>
            data-error-callback="wp_cft_error_callback"
            data-expired-callback="wp_cft_expired_callback"
        ></div>
	    <?php

	    return ob_get_clean();
    }

	/**
	 * Create turnstile field template.
	 */
	public function render_implicit( $callback = '', $form_name = '', $unique_id = '', $class = '' , $widget_id = '') {
		wp_enqueue_script( 'wp-cft-script' );
		wp_enqueue_style( 'wp-cf-turnstile-styles' );

        do_action( "wp_cft_before_cft_widget", esc_attr( $unique_id ) );

        echo $this->get_implicit_widget( $callback, $form_name, $unique_id, $class );

		do_action( "wp_cft_after_cft_widget", esc_attr( $unique_id ), $widget_id );
	}

	public function force_re_render($unique_id = '')
	{
		$unique_id = sanitize_text_field($unique_id);
		$site_key = $this->settings->get_value( 'wp_cft_site_key' );

        $output = '';
        ob_start();
		if ($unique_id) {
			?>
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    setTimeout(function () {
                        const cft_element_id = "cf-turnstile<?php echo esc_html( $unique_id ); ?>";
                        const cft_element = document.getElementById(cft_element_id);

                        if (cft_element && !cft_element.innerHTML.trim()) {
                            turnstile.remove("#" + cft_element_id);

                            turnstile.render("#" + cft_element_id, {
                                sitekey: "<?php echo esc_html( $site_key ); ?>"
                            });
                        }
                    }, 0);
                });
            </script>
			<?php
		}
        $output .= ob_get_clean();

        return $output;
	}

	/**
	 * Checks Turnstile Captcha POST is Valid
	 */
	public function check_cft_token_response( $cft_response_token = "" ) {
		$results = array();

		// Check if POST data is empty
		if ( empty( $cft_response_token ) && isset( $_REQUEST['cf-turnstile-response'] ) ) {
			$cft_response_token = sanitize_text_field( $_REQUEST['cf-turnstile-response'] );
		}

		// Get Turnstile Keys from Settings
		$site_key    = $this->settings->get_value( 'wp_cft_site_key' );
		$secret_key = $this->settings->get_value( 'wp_cft_secret_key' );

		if ( $site_key && $secret_key ) {
			$headers  = array(
				'body' => [
					'secret'   => $secret_key,
					'response' => $cft_response_token
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

			foreach ( $response as $item_key => $item_value ) {
				if ( $item_key == 'error-codes' ) {
					foreach ( $item_value as $error_code ) {
						$results['error_code'] = $error_code;
						$results['error_message'] = WP_CFT_Utils::error_message_by_code($error_code);
					}
				}
			}

			do_action('wp_cft_after_cft_token_check', $response, $results);

			return $results;
		}

		return false;
	}

    public function widget_settings(){
        $settings = array(
            'theme'       => $this->settings->get_value( 'wp_cft_theme', 'auto' ),
            'language'    => 'auto',
            'appearance'  => 'always',
            'widget_size' => $this->settings->get_value( 'wp_cft_widget_size', 'normal' )
        );
        $overrides = $this->settings_overrdies;

        $out = array();
	    foreach ( $settings as $name => $default ) {
		    if ( array_key_exists( $name, $overrides ) ) {
			    $out[ $name ] = $overrides[ $name ];
		    } else {
			    $out[ $name ] = $default;
		    }
	    }

        return $out;
    }

    public function add_settings_override($name, $value){
        $this->settings_overrdies[$name] = $value;
    }

    public function clear_settings_overrides() {
	    $this->settings_overrdies = array();
    }
}