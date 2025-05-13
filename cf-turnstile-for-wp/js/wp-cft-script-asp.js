
/**
 * This callback invoked upon success of the challenge. This is specific to ASP plugin.
 */
function wp_cft_asp_checkout_form_callback(token){
    console.log('[WP CFT] Cloudflare turnstile challenge successful for ASP checkout.');

    // Here, the 'vars' object is available by the ASP plugin.
    if (vars.data){
        // Add the response token to the vars.data object so that later it can be passed to asp asp_pp_create_pi ajax payload.
        vars.data.wp_cft_token_response = token;
    }
}

/**
 * For ASP plugin integration.
 */
var WpCftHandlerNG = function (data) {
    // This callback triggers before the 'asp_pp_create_pi' ajax request executes.
    this.csBeforeRegenParams = function () {
        // console.log('[WP CFT]: Adding response token to csBeforeRegenParams');
        // Adding the response token to asp_pp_create_pi ajax payload.
        if (vars.data){
            const token = vars.data.wp_cft_token_response || '';
            vars.data.csRegenParams += '&wp_cft_token_response=' + token;
        }
    }
}