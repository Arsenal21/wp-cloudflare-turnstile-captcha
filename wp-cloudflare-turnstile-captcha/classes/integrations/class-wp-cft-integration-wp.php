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
			add_action('registration_errors', array($this, 'check_wp_registration'), 30, 3);
		}

		$wp_cft_enable_on_wp_reset_password = $this->settings->get_value( 'wp_cft_enable_on_wp_reset_password' );
		if ( $wp_cft_enable_on_wp_reset_password ) {
			add_action('lostpassword_form', array($this, 'render_wp_pass_reset_form_cft'));
			add_action('lostpassword_post', array($this, 'check_wp_reset_password'), 30, 1);
		}

		$wp_cft_enable_on_wp_comment = $this->settings->get_value( 'wp_cft_enable_on_wp_comment' );
		if ( $wp_cft_enable_on_wp_comment ) {
			add_action('comment_form_submit_button', array($this, 'render_comment_form_cft'), 100, 2);
			add_action('pre_comment_on_post', array( $this, 'check_wp_comment'), 30, 1);
		}
	}

	public function render_wp_login_form_cft() {
		$this->turnstile->render_implicit( 'wp_cft_callback', 'wordpress-login', wp_rand(), 'wp-cft-widget-ml-n15 wp-cft-widget-mb-12' );
	}

	public function render_wp_register_form_cft() {
		$this->turnstile->render_implicit('wp_cft_callback', 'wordpress-register', wp_rand(), 'wp-cft-widget-ml-n15' );
	}

	public function render_wp_pass_reset_form_cft() {
		$this->turnstile->render_implicit('wp_cft_callback', 'wordpress-reset', wp_rand(), 'wp-cft-widget-ml-n15 wp-cft-widget-mb-12' );
	}

	public function render_comment_form_cft( $submit_button, $args ) {
		wp_enqueue_script( 'wp-cft-script' );

		$unique_id = wp_rand();

		$submit_before = '';
		$submit_after  = '';

		$submit_before .= $this->turnstile->get_implicit_widget( 'wp_cft_callback', 'wordpress-comment', 'c-' .$unique_id );
		$submit_before .= '<br>';

		// $submit_after .= $this->turnstile->force_re_render( "-c-" . $unique_id );// TODO: This needs to be checked it its necessary.

		// Script to render turnstile when clicking reply
		ob_start();
		?>
		<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function () {
                document.body.addEventListener("click", function (event) {
                    if (event.target.matches(".comment-reply-link, #cancel-comment-reply-link")) {
                        turnstile.reset(".comment-form .cf-turnstile");
                    }
                });
            });
		</script>
		<?php
		$script = ob_get_clean();

		return $submit_before . $submit_button . $submit_after . $script;
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
		if ( ! WP_CFT_Utils::is_login_page()) {
			return $user;
		}

		// Check Turnstile
		$result = $this->turnstile->check();

		$success = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		if ( $success != true ) {
			 $user = new WP_Error( 'wpf_cf_turnstile_error', WP_CFT_Utils::failed_message($error_message) );
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

		// Skip if not on registration page
		if ( ! WP_CFT_Utils::is_registration_page() ) {
			return $errors;
		}

		// Skip Logged In Admins
		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			return $errors;
		}

		$result = $this->turnstile->check();

		$success = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		if ( ! $success ) {
			$errors->add( 'wpf_cf_turnstile_error', WP_CFT_Utils::failed_message($error_message) );
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
			$result = $this->turnstile->check();

			$success = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
			$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

			if ( ! $success ) {
				$validation_errors->add( 'wpf_cf_turnstile_error', WP_CFT_Utils::failed_message($error_message) );
			}
		}
	}

	public function check_wp_comment( $comment_data ) {
		if ( is_admin() ) {
			return $comment_data;
		}

		if ( ! empty( $_POST ) ) {
			$result = WP_CFT_Turnstile::get_instance()->check();

			$success = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
			$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

			if ( ! $success ) {
				wp_die(
					'<p><strong>' . __( 'ERROR:', 'wp-cf-turnstile' ) . '</strong> ' . WP_CFT_Utils::failed_message($error_message) . '</p>',
					'wp-cf-turnstile',
					array( 'response'  => 403 )
				);
			}

		}
		return $comment_data;
	}

}