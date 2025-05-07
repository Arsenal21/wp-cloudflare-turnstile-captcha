// console.log('wp-cft script loaded!');

/**
 * This callback invoked upon success of the challenge.
 */
function wp_cft_callback(){
    console.log('[WP CFT] Cloudflare turnstile challenge successful.');
}

/**
 * This callback invoked when there is an error
 */
function wp_cft_error_callback(error_code) {
    wp_cft_handle_error_callback(error_code);

    return true; // Returning non-falsy value means the error was handled.
}

function wp_cft_handle_error_callback(error_code){
    document.querySelectorAll('.wp-cf-turnstile-div').forEach(el => {
        const existingErrorMsg = el.querySelector('.wp-cft-error-msg');
        if ( existingErrorMsg != null){
            existingErrorMsg.remove();
        }

        const error = wp_cft_error_msg_by_code(error_code);
        if (error.show){
            // Show the error to visitor.
            const errorEl = wp_cft_create_error_msg_html("Cloudflare turnstile error: " + error.message);
            el.appendChild(errorEl);
        } else {
            // Don't show the error to visitor, console log instead.
            console.log("[WP CFT] " + error.message)
        }
    })
}

function wp_cft_create_error_msg_html(message){
    const errorHtml = document.createElement('p');
    errorHtml.classList.add('wp-cft-error-msg');
    errorHtml.innerText = message;

    return errorHtml;
}

/**
 * This callback invoked when the token expires and does not reset the widget.
 */
function wp_cft_expired_callback() {
    console.log('[WP CFT] Cloudflare turnstile token has expired.');
}

function wp_cft_error_msg_by_code(code = 0){
    const errors = [
        {
            codeRegex: /100/,
            message: 'There was a problem initializing Turnstile before a challenge could be started.',
            show: true
        },
        {
            codeRegex: /105/,
            message: 'Turnstile was invoked in a deprecated or invalid way.',
            show: true
        },
        {
            codeRegex: /10[2-6]/,
            message: 'Invalid Parameters: The visitor sent an invalid parameter as part of the challenge towards Turnstile.',
            show: false
        },
        {
            codeRegex: /1101[0-1]0/,
            message: 'Turnstile was invoked with an invalid sitekey or a sitekey that is no longer active.',
            show: true
        },
        {
            codeRegex: /110200/,
            message: 'Unknown domain: Domain not allowed',
            show: false
        },
        {
            codeRegex: /1106/,
            message: 'Challenge timed out: The visitor took too long to solve the challenge and the challenge timed out.',
            show: false
        },
        {
            codeRegex: /300/,
            message: 'Generic client execution error: An unspecified error occurred in the visitor while they were solving a challenge.',
            show: false
        },
        {
            codeRegex: /400/,
            message: 'Incorrect turnstile configuration',
            show: true
        },
        {
            codeRegex: /600/,
            message: 'Challenge execution failure: A visitor failed to solve a Turnstile Challenge.',
            show: false
        },
        {
            // This is an unknown error.
            codeRegex: /.*/,
            message: 'Unknown error with code: ' + code,
            show: false
        }
    ];

    // Search for the matching error.
    for (const i in errors) {
        let result = errors[i].codeRegex.test(code.toString());
        if (result){
            return errors[i];
        }
    }

    // return the unknown error.
    return errors[errors.length - 1];
}

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