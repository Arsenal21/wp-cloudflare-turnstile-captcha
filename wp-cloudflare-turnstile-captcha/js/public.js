/**
 * This callback invoked upon success of the challenge.
 */
function wp_cft_callback(){
    console.log('[WP CFT]: Cloudflare turnstile challenge successful.');
}

/**
 * This callback invoked when there is an error
 */
function wp_cft_error_callback() {
    console.log('[WP CFT]: Cloudflare turnstile has encountered and error!');
}

/**
 * This callback invoked when the token expires and does not reset the widget.
 */
function wp_cft_expired_callback() {
    console.log('[WP CFT]: Cloudflare turnstile token has expired.');
}